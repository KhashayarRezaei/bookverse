<?php

namespace App\Contracts;

use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * Charge a user for a specific amount
     *
     * @param  User  $user  The user to charge
     * @param  float  $amount  The amount to charge
     * @return array Response containing status, transaction_id, and amount
     */
    public function charge(User $user, float $amount): array;
}
