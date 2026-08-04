<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = config('midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(array $params): object
    {
        $snapParams = [
            "transaction_details" => [
                "order_id" => $params["order_id"],
                "gross_amount" => $params["amount"],
            ],
            "customer_details" => [
                "first_name" => $params["donor_name"] ?? "Hamba Allah",
                "email" => $params["email"] ?? "donor@hatiberbagi.id",
            ],
            "item_details" => [[
                "id" => "DONATION-" . $params["campaign_id"],
                "price" => $params["amount"],
                "quantity" => 1,
                "name" => "Donasi: " . substr($params["campaign_title"], 0, 50),
            ]],
            "callbacks" => [
                "finish" => config('app.frontend_url') . "/donation/success",
            ],
        ];

        return Snap::createTransaction($snapParams);
    }

    public function handleNotification(): array
    {
        $notif = new Notification();

        return [
            "order_id" => $notif->order_id,
            "transaction_id" => $notif->transaction_id,
            "status" => $this->mapStatus($notif->transaction_status, $notif->fraud_status ?? null),
            "payment_type" => $notif->payment_type,
        ];
    }

    private function mapStatus(string $txStatus, ?string $fraudStatus): string
    {
        if ($txStatus == "capture" && $fraudStatus == "accept") return "paid";
        if ($txStatus == "settlement") return "paid";
        if (in_array($txStatus, ["cancel", "deny", "expire"])) return "failed";
        if ($txStatus == "pending") return "pending";
        return "failed";
    }
}