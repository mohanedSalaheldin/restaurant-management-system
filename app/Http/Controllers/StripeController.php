<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Services\StripePaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeController extends Controller
{
    public function __construct(
        protected StripePaymentService $stripeService
    ) {}

    public function checkout(Bill $bill): RedirectResponse
    {
        try {
            $checkoutUrl = $this->stripeService->createCheckoutSession($bill);

            return redirect()->away($checkoutUrl);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (! $sessionId) {
            return redirect()->route('billing.index')->with('error', 'Session ID missing.');
        }

        try {
            $bill = $this->stripeService->verifyAndRecordPayment($sessionId);

            return redirect()->route('billing.receipt', $bill)->with('success', 'Payment via Stripe was successful!');
        } catch (Exception $e) {
            return redirect()->route('billing.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Other failures are left to surface as a 500 so that Stripe retries the delivery.
     */
    public function webhook(Request $request): Response
    {
        try {
            $this->stripeService->handleWebhook($request->getContent(), (string) $request->header('Stripe-Signature'));
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response('Invalid webhook request.', 400);
        }

        return response('OK');
    }
}
