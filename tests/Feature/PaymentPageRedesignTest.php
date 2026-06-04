<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentPageRedesignTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the payment page loads successfully and includes the Tabler CSS CDN link.
     *
     * Validates: Requirements 1.1
     */
    public function test_page_loads_with_tabler_css_cdn(): void
    {
        $response = $this->get(route('payment.public.root'));

        $response->assertStatus(200);
        $response->assertSee('cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css', false);
    }

    /**
     * Test that the payment page uses the Tabler container-xl layout class.
     *
     * Validates: Requirements 1.1
     */
    public function test_page_contains_container_xl_class(): void
    {
        $response = $this->get(route('payment.public.root'));

        $response->assertStatus(200);
        $response->assertSee('container-xl', false);
    }

    /**
     * Test that the payment page does NOT contain Boxicons CDN link or bx bx- class references.
     *
     * Validates: Requirements 1.8
     */
    public function test_page_does_not_contain_boxicons(): void
    {
        $response = $this->get(route('payment.public.root'));

        $response->assertStatus(200);
        $response->assertDontSee('boxicons', false);
        $response->assertDontSee('bx bx-', false);
    }

    /**
     * Test that the payment page does NOT contain custom CSS variables from the old design.
     *
     * Validates: Requirements 1.3
     */
    public function test_page_does_not_contain_custom_css_variables(): void
    {
        $response = $this->get(route('payment.public.root'));

        $response->assertStatus(200);
        $response->assertDontSee('--bg-a', false);
        $response->assertDontSee('--surface', false);
        $response->assertDontSee('--primary-color', false);
    }

    /**
     * Test that the payment page uses Tabler Icons (ti ti-* classes).
     *
     * Validates: Requirements 1.8
     */
    public function test_page_contains_tabler_icons(): void
    {
        $response = $this->get(route('payment.public.root'));

        $response->assertStatus(200);
        $response->assertSee('ti ti-', false);
    }
}
