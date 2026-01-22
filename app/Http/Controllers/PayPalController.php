<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessPaymentJob;
use App\Models\Transaction;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayPalController extends Controller
{
    protected $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    /**
     * Create a new order
     */
    public function create(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'nullable|string|size:3',
        ]);

        $currency = $request->input('currency', 'USD');
        $transactionCode = 'TRX-' . strtoupper(Str::random(10));

        try {
            // Calculate Total and Prepare Items
            $itemsData = $request->input('items');
            $productIds = collect($itemsData)->pluck('product_id');
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            $totalAmount = 0;
            $transactionItems = [];

            foreach ($itemsData as $item) {
                $product = $products->get($item['product_id']);

                if (!$product) {
                    continue; // Should be caught by validation but safety check
                }

                $price = $product->price;
                $quantity = $item['quantity'];
                $subtotal = $price * $quantity;

                $totalAmount += $subtotal;

                $transactionItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price, // Store historical price
                    'subtotal' => $subtotal,
                ];
            }

            // Call PayPal Service
            $order = $this->payPalService->createOrder($totalAmount, $currency);

            // Save Transaction
            $transaction = Transaction::create([
                'transaction_code' => $transactionCode,
                'paypal_order_id' => $order['id'],
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'status' => 'PENDING',
                'payload' => $order,
            ]);

            // Save Transaction Items
            foreach ($transactionItems as $item) {
                $transaction->items()->create($item);
            }

            // Extract approval link
            $approvalLink = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;

            return response()->json([
                'success' => true,
                'transaction_code' => $transactionCode,
                'paypal_order_id' => $order['id'],
                'approval_link' => $approvalLink,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Capture payment
     */
    public function capture(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
        ]);

        $orderId = $request->input('paypal_order_id');

        try {
            $transaction = Transaction::where('paypal_order_id', $orderId)->firstOrFail();

            if ($transaction->status === 'COMPLETED') {
                return response()->json(['success' => true, 'message' => 'Transaction already completed.']);
            }

            // Capture Order
            $captureData = $this->payPalService->captureOrder($orderId);

            // Update Transaction
            $transaction->update([
                'status' => 'COMPLETED',
                'payload' => array_merge($transaction->payload ?? [], ['capture' => $captureData]),
            ]);

            // Dispatch Job
            ProcessPaymentJob::dispatch($transaction);

            return response()->json([
                'success' => true,
                'message' => 'Payment captured successfully.',
                'data' => $captureData
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle PayPal Success Callback
     */
    public function success(Request $request)
    {
        $orderId = $request->query('token');

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'Token not found'], 400);
        }

        // Reuse capture logic
        // Ideally, you would redirect the user to a frontend page here
        // But for API testing, we can trigger capture here or tell the user to do it.

        // Let's forward the request to the capture method for convenience
        $newRequest = new Request();
        $newRequest->merge(['paypal_order_id' => $orderId]);

        return $this->capture($newRequest);
    }

    /**
     * Cancel payment
     */
    public function cancel(Request $request)
    {
        // Implementation for cancel logic (update status)
        return response()->json(['success' => true, 'message' => 'Payment cancelled']);
    }
}
