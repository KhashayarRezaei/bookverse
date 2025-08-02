<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;

class StripePaymentService implements PaymentGatewayInterface
{
    public function charge(User $user, float $amount): array
    {
        $transactionId = 'stripe_' . uniqid() . '_' . time();

        return [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'gateway' => 'stripe',
            'user_id' => $user->id,
            'timestamp' => now()->toISOString(),
        ];
    }
}
