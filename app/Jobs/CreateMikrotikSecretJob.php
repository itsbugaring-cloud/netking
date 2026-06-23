<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\MikroTikService;
use App\Services\ProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateMikrotikSecretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = 60;

    /**
     * The customer instance.
     */
    protected Customer $customer;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Execute the job.
     */
    public function handle(ProvisioningService $provisioningService): void
    {
        try {
            Log::info("Starting MikroTik provisioning for customer: {$this->customer->id}");

            // Load area with router credentials
            $this->customer->loadMissing('area', 'package');

            // Use MikroTikService with per-area credentials (lazy connection)
            $mikrotik = MikroTikService::forArea($this->customer->area);

            if (!$mikrotik->isConnected()) {
                Log::warning("Router unreachable for area {$this->customer->area->name}, releasing to retry");
                $this->release($this->backoff);
                return;
            }

            $exists = $mikrotik->secretExists($this->customer->pppoe_user);
            if (($exists['success'] ?? false) !== true) {
                throw new \RuntimeException("MikroTik duplicate check failed: " . ($exists['error'] ?? 'Unknown error'));
            }
            if (($exists['exists'] ?? false) === true) {
                throw new \RuntimeException("PPPoE username {$this->customer->pppoe_user} sudah ada di MikroTik area {$this->customer->area->name}.");
            }

            // Create PPPoE secret on the area's router
            $result = $mikrotik->createSecret(
                username: $this->customer->pppoe_user,
                password: $this->customer->pppoe_pass,
                service: 'pppoe',
                profile: $this->customer->package?->mikrotik_profile ?? 'default',
                remoteAddress: $this->customer->remote_ip,
                localAddress: $this->customer->local_address,
                comment: $this->customer->name
            );

            if (!$result['success']) {
                throw new \RuntimeException("MikroTik createSecret failed: " . ($result['error'] ?? 'Unknown error'));
            }

            Log::info("MikroTik secret created successfully for customer: {$this->customer->id}");

            // Mark customer as active
            $provisioningService->markAsActive($this->customer);
        } catch (\RuntimeException $e) {
            // Business-logic errors — mark customer as failed, don't rethrow
            Log::error("MikroTik provisioning error for customer {$this->customer->id}: " . $e->getMessage());

            // Connection-related errors → retry (only works with database/redis queue)
            if (
                str_contains($e->getMessage(), 'Connection') ||
                str_contains($e->getMessage(), 'timeout') ||
                str_contains($e->getMessage(), 'refused') ||
                str_contains($e->getMessage(), 'Not connected')
            ) {
                Log::warning("Router unreachable, releasing job back to queue for retry");
                if ($this->job && !$this->job->isReleased()) {
                    $this->release($this->backoff);
                    return;
                }
            }

            $provisioningService->markAsFailed($this->customer, $e->getMessage());
            // Don't rethrow — customer marked failed, request completes cleanly
        } catch (\Exception $e) {
            Log::error("Unexpected error provisioning customer {$this->customer->id}: " . $e->getMessage());
            $provisioningService->markAsFailed($this->customer, $e->getMessage());
            // Don't rethrow — handle gracefully
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed permanently for customer {$this->customer->id}: " . $exception->getMessage());

        $provisioningService = app(ProvisioningService::class);
        $provisioningService->markAsFailed(
            $this->customer,
            "Provisioning failed after {$this->tries} attempts: " . $exception->getMessage()
        );
    }
}
