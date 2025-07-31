<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentFactory
{
    /**
     * Create a payment service based on the payment method
     *
     * @param string|null $paymentMethod The payment method (stripe, paypal)
     * @return PaymentGatewayInterface
     * @throws InvalidArgumentException
     */
    public function create(?string $paymentMethod): PaymentGatewayInterface
    {
        if (empty($paymentMethod)) {
            throw new InvalidArgumentException('Payment method cannot be empty');
        }

        $method = strtolower(trim($paymentMethod));

        return match ($method) {
            'stripe' => new StripePaymentService(),
            'paypal' => new PayPalPaymentService(),
            default => throw new InvalidArgumentException("Unsupported payment method: {$paymentMethod}")
        };
    }
}
