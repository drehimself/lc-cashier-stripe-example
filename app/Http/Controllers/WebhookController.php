<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Subscription;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class WebhookController extends CashierController
{
    /**
     * Handle deleted customer.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerDeleted(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['id'])) {
            $user->subscriptions->each(function (Subscription $subscription) {
                $subscription->skipTrial()->markAsCancelled();
            });

            $user->forceFill([
                'stripe_id' => null,
                'trial_ends_at' => null,
                'card_brand' => null,
                'card_last_four' => null,
            ])->save();
        }

        Log::info('Customer was deleted: '.$user->email);

        return $this->successMethod();
    }
}
