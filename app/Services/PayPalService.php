<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.secret');
        $mode = config('services.paypal.mode', 'sandbox');

        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    /**
     * Get Client Credentials Access Token
     */
    protected function getAccessToken(): string
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            Log::error('PayPal Auth Failed', ['error' => $response->body()]);
            throw new RuntimeException('Could not authenticate with PayPal');
        }

        return $response->json('access_token');
    }

    /**
     * Create an Order
     * @param float $amount
     * @param string $currency
     * @return array
     */
    public function createOrder(float $amount, string $currency = 'USD'): array
    {
        $token = $this->getAccessToken();

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => $currency,
                            'value' => number_format($amount, 2, '.', ''),
                        ],
                    ]
                ],
                // Add Application Context if needed (return_url, cancel_url)
                'application_context' => [
                    'return_url' => url('/api/paypal/success'), // Or frontend URL
                    'cancel_url' => url('/api/paypal/cancel'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayPal Create Order Failed', ['error' => $response->body()]);
            throw new RuntimeException('Could not create PayPal order');
        }

        return $response->json();
    }

    /**
     * Capture Payment for Order
     * @param string $orderId
     * @return array
     */
    public function captureOrder(string $orderId): array
    {
        $token = $this->getAccessToken();

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            Log::error('PayPal Capture Failed', ['order_id' => $orderId, 'error' => $response->body()]);
            throw new RuntimeException('Could not capture PayPal order');
        }

        return $response->json();
    }
}
