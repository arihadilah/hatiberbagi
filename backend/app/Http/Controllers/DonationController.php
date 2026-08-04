<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Donation;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DonationController extends Controller
{
    public function __construct(private XenditService $xendit) {}

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id'  => 'required|exists:campaigns,id',
            'amount'       => 'required|integer|min:10000',
            'donor_name'   => 'nullable|string|max:100',
            'is_anonymous' => 'boolean',
            'message'      => 'nullable|string|max:255',
        ]);

        $campaign    = Campaign::findOrFail($request->campaign_id);
        $orderId     = 'HB-' . strtoupper(uniqid());
        $isAnonymous = (bool) ($request->is_anonymous ?? false);

        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        $donorName = $isAnonymous
            ? 'Hamba Allah'
            : ($request->donor_name ?? $user?->name ?? 'Anonim');

        // Buat invoice ke Xendit
        $invoice = $this->xendit->createInvoice([
            'order_id'       => $orderId,
            'amount'         => $request->amount,
            'campaign_id'    => $campaign->id,
            'campaign_title' => $campaign->title,
            'donor_name'     => $donorName,
            'email'          => $user?->email,
        ]);

        // Simpan donasi
        Donation::create([
            'campaign_id'       => $campaign->id,
            'user_id'           => $user?->id,
            'amount'            => $request->amount,
            'donor_name'        => $donorName,
            'is_anonymous'      => $isAnonymous,
            'message'           => $request->message,
            'midtrans_order_id' => $orderId,
            'payment_url'       => $invoice['invoice_url'],
            'status'            => 'pending',
        ]);

        return response()->json([
            'invoice_url' => $invoice['invoice_url'],
            'order_id'    => $orderId,
        ]);
    }

    // Webhook dari Xendit
    public function notification(Request $request)
    {
        try {
            // Ambil token dari header X-CALLBACK-TOKEN
            $payload = array_merge(
                $request->all(),
                ['webhook_token' => $request->header('x-callback-token')]
            );

            $data     = $this->xendit->handleCallback($payload);
            $donation = Donation::where('midtrans_order_id', $data['order_id'])
                ->firstOrFail();

            if ($data['status'] === 'paid' && $donation->status !== 'paid') {
                DB::transaction(function () use ($donation, $data) {
                    $donation->update([
                        'status'                  => 'paid',
                        'midtrans_transaction_id' => $data['transaction_id'],
                        'payment_type'            => $data['payment_method'],
                        'paid_at'                 => now(),
                    ]);
                    $donation->campaign->increment('raised_amount', $donation->amount);
                    $donation->campaign->increment('donor_count');
                });
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Xendit callback error: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    public function show(string $orderId)
    {
        $donation = Donation::with('campaign:id,title,slug,thumbnail')
            ->where('midtrans_order_id', $orderId)
            ->firstOrFail();

        return response()->json($donation);
    }
}