<?php

namespace App\Services;

use App\Models\Customer;
use Carbon\Carbon;

class BillingCalculator
{
    private function prorationBaseDays(): int
    {
        return max(1, (int) config('billing.proration_base_days', 30));
    }

    private function resolveCycleWindow(int $year, int $month, int $dueDay): array
    {
        $dueDate = $this->resolveDueDateForPeriod($year, $month, $dueDay);
        $windowStart = $dueDate->copy()->subMonthNoOverflow();
        $windowEnd = $dueDate->copy();

        return [$windowStart, $windowEnd, $dueDate];
    }

    public function resolveBaseMonthlyAmount(Customer $customer): float
    {
        $directPrice = (float) ($customer->package_price ?? 0);
        if ($directPrice > 0) {
            return round($directPrice, 2);
        }

        $packagePrice = (float) ($customer->package?->price ?? 0);
        if ($packagePrice > 0) {
            return round($packagePrice, 2);
        }

        $speedDown = (int) ($customer->package?->speed_down ?? 0);
        $speedMap = (array) config('billing.default_speed_prices', []);
        if ($speedDown > 0 && isset($speedMap[$speedDown])) {
            return round((float) $speedMap[$speedDown], 2);
        }

        return round((float) config('billing.default_package_price', 100000), 2);
    }

    public function resolveDueDateForPeriod(int $year, int $month, ?int $overrideDay = null): Carbon
    {
        $reference = Carbon::createFromDate($year, $month, 1);
        $dueDay = (int) ($overrideDay ?: config('billing.invoice_due_day', 20));
        $safeDay = min(max($dueDay, 1), $reference->daysInMonth);

        return $reference->copy()->setDay($safeDay)->startOfDay();
    }

    /**
     * Calculate final invoice amount including first-month prorata.
     *
     * @return array{
     *   skip: bool,
     *   amount: float,
     *   base_amount: float,
     *   billed_days: int,
     *   period_days: int,
     *   is_prorated: bool,
     *   period_month: int,
     *   period_year: int
     * }
     */
    public function calculateForPeriod(Customer $customer, int $year, int $month): array
    {
        $dueDay = (int) config('billing.invoice_due_day', 20);
        [$windowStart, $windowEnd] = $this->resolveCycleWindow($year, $month, $dueDay);
        $periodDays = $this->prorationBaseDays();

        $baseAmount = $this->resolveBaseMonthlyAmount($customer);

        $billingStart = $customer->billing_start_date
            ? Carbon::parse($customer->billing_start_date)->startOfDay()
            : optional($customer->created_at)?->copy()->startOfDay();

        if (!$billingStart) {
            $billingStart = $windowStart->copy();
        }

        // If customer starts on/after cycle due date, this period should not be billed yet.
        if (!$billingStart->lt($windowEnd)) {
            return [
                'skip' => true,
                'amount' => 0.0,
                'base_amount' => $baseAmount,
                'billed_days' => 0,
                'period_days' => $periodDays,
                'is_prorated' => false,
                'period_month' => $month,
                'period_year' => $year,
            ];
        }

        $billableStart = $billingStart->greaterThan($windowStart)
            ? $billingStart->copy()
            : $windowStart->copy();

        // Business rule: billed days count excludes due date (e.g. 05->20 = 15 days).
        $billedDays = (int) $billableStart->diffInDays($windowEnd);
        $billedDays = max(0, min($billedDays, $periodDays));
        $isProrated = $billedDays < $periodDays;

        $amount = $periodDays > 0
            ? round(($baseAmount / $periodDays) * $billedDays, 2)
            : 0.0;

        return [
            'skip' => $amount <= 0 || $billedDays <= 0,
            'amount' => $amount,
            'base_amount' => $baseAmount,
            'billed_days' => $billedDays,
            'period_days' => $periodDays,
            'is_prorated' => $isProrated,
            'period_month' => $month,
            'period_year' => $year,
        ];
    }
}
