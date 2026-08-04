<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function donations(Request $request)
    {
        $query = Donation::with(['campaign', 'user'])
            ->where('status', 'paid');

        if ($request->start_date) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        if ($request->category_id) {
            $query->whereHas('campaign', function($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $donations = $query->orderBy('paid_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json($donations);
    }

    public function summary(Request $request)
    {
        $query = Donation::where('status', 'paid');

        if ($request->start_date) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $totalAmount = $query->sum('amount');
        $totalCount = $query->count();

        $byCategory = Category::withSum(['campaigns.donations as total' => function($q) use ($request) {
            $q->where('status', 'paid');
            if ($request->start_date) $q->whereDate('paid_at', '>=', $request->start_date);
            if ($request->end_date) $q->whereDate('paid_at', '<=', $request->end_date);
        }], 'amount')
            ->having('total', '>', 0)
            ->get(['id', 'name', 'color', 'icon']);

        return response()->json([
            'total_amount' => $totalAmount,
            'total_donations' => $totalCount,
            'by_category' => $byCategory,
        ]);
    }

    public function export(Request $request)
    {
        $donations = Donation::with(['campaign.category', 'user'])
            ->where('status', 'paid');

        if ($request->start_date) {
            $donations->whereDate('paid_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $donations->whereDate('paid_at', '<=', $request->end_date);
        }

        $donations = $donations->get();

        $csv = "Order ID,Tanggal,Donatur,Campaign,Kategori,Jumlah,Metode\n";

        foreach ($donations as $d) {
            $csv .= implode(",", [
                $d->midtrans_order_id,
                $d->paid_at ? $d->paid_at->format('d/m/Y H:i') : '-',
                $d->is_anonymous ? 'Hamba Allah' : ($d->donor_name ?? '-'),
                '"' . str_replace('"', '""', $d->campaign->title) . '"',
                $d->campaign->category->name ?? '-',
                $d->amount,
                $d->payment_type ?? '-',
            ]) . "\n";
        }

        return Response::make($csv, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=donasi_" . now()->format('Ymd') . ".csv",
        ]);
    }
}