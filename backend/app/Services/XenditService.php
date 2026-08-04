<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class XenditService
{
    private string $secretKey;
    private string $baseUrl;
    private string $webhookToken;

    public function __construct()
    {
        $this->secretKey    = config('xendit.secret_key');
        $this->webhookToken = config('xendit.webhook_token');
        $this->baseUrl      = 'https://api.xendit.co';
    }

    public function createInvoice(array $params): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . '/v2/invoices', [
                'external_id'      => $params['order_id'],
                'amount'           => (int) $params['amount'],
                'description'      => 'Donasi: ' . substr($params['campaign_title'], 0, 100),
                'payer_email'      => $params['email'] ?? 'donor@hatiberbagi.id',
                'customer'         => [
                    'given_names'  => $params['donor_name'] ?? 'Hamba Allah',
                    'email'        => $params['email'] ?? 'donor@hatiberbagi.id',
                ],
                'success_redirect_url' => config('app.frontend_url') . '/donation/success?order_id=' . $params['order_id'],
                'failure_redirect_url' => config('app.frontend_url') . '/donation/failed',
                'currency'         => 'IDR',
                'items'            => [[
                    'name'     => 'Donasi Campaign',
                    'quantity' => 1,
                    'price'    => (int) $params['amount'],
                ]],
                'fees' => [],
            ]);

        if (!$response->successful()) {
            throw new \Exception('Xendit error: ' . $response->json('message'));
        }

        return $response->json();
    }

    public function handleCallback(array $payload): array
    {
        // Verifikasi webhook token dari Xendit
        $webhookToken = $payload['webhook_token'] ?? '';

        if ($webhookToken !== $this->webhookToken) {
            throw new \Exception('Invalid webhook token');
        }

        return [
            'order_id'       => $payload['external_id'],
            'transaction_id' => $payload['id'],
            'status'         => $payload['status'] === 'PAID' ? 'paid' : 'pending',
            'payment_method' => $payload['payment_method'] ?? null,
            'amount'         => $payload['amount'],
        ];
    }
}