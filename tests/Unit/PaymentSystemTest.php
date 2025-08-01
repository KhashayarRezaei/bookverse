<?php

namespace Tests\Unit;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Services\PaymentFactory;
use App\Services\PayPalPaymentService;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_payment_service_implements_interface()
    {
        $stripeService = new StripePaymentService;

        $this->assertInstanceOf(PaymentGatewayInterface::class, $stripeService);
    }

    public function test_paypal_payment_service_implements_interface()
    {
        $paypalService = new PayPalPaymentService;

        $this->assertInstanceOf(PaymentGatewayInterface::class, $paypalService);
    }

    public function test_stripe_payment_service_returns_success_response()
    {
        $stripeService = new StripePaymentService;
        $user = User::factory()->create();
        $amount = 100.00;

        $response = $stripeService->charge($user, $amount);

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('transaction_id', $response);
        $this->assertArrayHasKey('amount', $response);
        $this->assertEquals($amount, $response['amount']);
    }

    public function test_paypal_payment_service_returns_success_response()
    {
        $paypalService = new PayPalPaymentService;
        $user = User::factory()->create();
        $amount = 150.00;

        $response = $paypalService->charge($user, $amount);

        $this->assertIsArray($response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('transaction_id', $response);
        $this->assertArrayHasKey('amount', $response);
        $this->assertEquals($amount, $response['amount']);
    }

    public function test_payment_factory_returns_stripe_service()
    {
        $factory = new PaymentFactory;

        $service = $factory->create('stripe');

        $this->assertInstanceOf(StripePaymentService::class, $service);
        $this->assertInstanceOf(PaymentGatewayInterface::class, $service);
    }

    public function test_payment_factory_returns_paypal_service()
    {
        $factory = new PaymentFactory;

        $service = $factory->create('paypal');

        $this->assertInstanceOf(PayPalPaymentService::class, $service);
        $this->assertInstanceOf(PaymentGatewayInterface::class, $service);
    }

    public function test_payment_factory_throws_exception_for_unsupported_method()
    {
        $factory = new PaymentFactory;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported payment method: credit_card');

        $factory->create('credit_card');
    }

    public function test_payment_factory_throws_exception_for_empty_method()
    {
        $factory = new PaymentFactory;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment method cannot be empty');

        $factory->create('');
    }

    public function test_payment_factory_throws_exception_for_null_method()
    {
        $factory = new PaymentFactory;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment method cannot be empty');

        $factory->create(null);
    }

    public function test_payment_factory_case_insensitive_stripe()
    {
        $factory = new PaymentFactory;

        $service = $factory->create('STRIPE');

        $this->assertInstanceOf(StripePaymentService::class, $service);
    }

    public function test_payment_factory_case_insensitive_paypal()
    {
        $factory = new PaymentFactory;

        $service = $factory->create('PAYPAL');

        $this->assertInstanceOf(PayPalPaymentService::class, $service);
    }

    public function test_stripe_service_generates_unique_transaction_ids()
    {
        $stripeService = new StripePaymentService;
        $user = User::factory()->create();
        $amount = 100.00;

        $response1 = $stripeService->charge($user, $amount);
        $response2 = $stripeService->charge($user, $amount);

        $this->assertNotEquals($response1['transaction_id'], $response2['transaction_id']);
        $this->assertStringStartsWith('stripe_', $response1['transaction_id']);
        $this->assertStringStartsWith('stripe_', $response2['transaction_id']);
    }

    public function test_paypal_service_generates_unique_transaction_ids()
    {
        $paypalService = new PayPalPaymentService;
        $user = User::factory()->create();
        $amount = 100.00;

        $response1 = $paypalService->charge($user, $amount);
        $response2 = $paypalService->charge($user, $amount);

        $this->assertNotEquals($response1['transaction_id'], $response2['transaction_id']);
        $this->assertStringStartsWith('paypal_', $response1['transaction_id']);
        $this->assertStringStartsWith('paypal_', $response2['transaction_id']);
    }
}
