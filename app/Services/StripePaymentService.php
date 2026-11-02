<?php

namespace App\Services;

use App\Models\Bill;
use Exception;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripePaymentService
{
    public function __construct(
        protected BillingService $billingService
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(Bill $bill): string
    {
        if ($bill->status === 'paid') {
            throw new Exception("This bill has already been paid.");
        }

        $amountInCents = (int) round($bill->remaining_amount * 100);

        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'usd',
                    'unit_amount' => $amountInCents,
                    'product_data' => [
                        'name'        => "Restaurant Bill: #{$bill->invoice_number}",
                        'description' => "Order #{$bill->order->order_number} " . ($bill->table ? "- Table {$bill->table->table_number}" : "- Takeaway"),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            // Stripe fills in {CHECKOUT_SESSION_ID} itself, so it must not be URL-encoded by route().
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('billing.show', $bill),
            'metadata'    => [
                'bill_id' => $bill->id,
            ],
        ]);

        return $session->url;
    }

    public function verifyAndRecordPayment(string $sessionId): Bill
    {
        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            throw new Exception("The payment has not been completed on Stripe.");
        }

        $billId = $session->metadata->bill_id ?? null;
        if (! $billId) {
            throw new Exception("Invalid session metadata.");
        }

        return $this->recordSessionPayment($session, Bill::findOrFail($billId));
    }

    /**
     * Covers customers who close the browser before Stripe redirects them back.
     *
     * @throws UnexpectedValueException when the payload or its signature is invalid
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        $secret = config('services.stripe.webhook_secret');

        // An empty secret would let anyone sign a forged event.
        if (empty($secret)) {
            throw new UnexpectedValueException('Stripe webhook secret is not configured.');
        }

        $event = Webhook::constructEvent($payload, $signature, $secret);

        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'])) {
            return;
        }

        $session = $event->data->object;
        $billId = $session->metadata->bill_id ?? null;

        // Other checkouts on the same Stripe account are not ours to handle.
        if ($session->payment_status !== 'paid' || ! $billId) {
            return;
        }

        $bill = Bill::find($billId);

        if ($bill) {
            $this->recordSessionPayment($session, $bill);
        }
    }

    /**
     * Safe to call twice: the redirect and the webhook usually both arrive for one payment.
     */
    protected function recordSessionPayment(object $session, Bill $bill): Bill
    {
        if ($bill->status === 'paid') {
            return $bill;
        }

        try {
            $this->billingService->recordPayment(
                $bill,
                [
                    'payment_method'   => 'stripe',
                    'amount_tendered'  => $session->amount_total / 100,
                    'reference_number' => $session->payment_intent ?? $session->id,
                ],
                $bill->user_id
            );
        } catch (Exception $e) {
            // Losing the race to the other delivery is fine; any other failure is not.
            if ($bill->fresh()->status !== 'paid') {
                throw $e;
            }
        }

        return $bill->fresh();
    }
}
