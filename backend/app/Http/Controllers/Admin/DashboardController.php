<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'stats' => [
                'total_donations' => Donation::where('status', 'paid')->sum('amount'),
                'total_donors' => Donation::where('status', 'paid')->distinct('user_id')->count('user_id'),
                'active_campaigns' => Campaign::where('status', 'active')->count(),
                'pending_campaigns' => Campaign::where('status', 'pending')->count(),
                'total_users' => User::count(),
                'new_users_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            ],
            'monthly_donations' => Donation::selectRaw('MONTH(paid_at) as month, SUM(amount) as total, COUNT(*) as count')
                ->where('status', 'paid')
                ->whereYear('paid_at', now()->year)
                ->groupBy('month')
                ->orderBy('month')
                ->get(),
            'recent_donations' => Donation::with(['campaign', 'user'])
                ->where('status', 'paid')
                ->latest('paid_at')
                ->limit(10)
                ->get(),
            'top_campaigns' => Campaign::withCount(['donations as paid_donations_count' => function($q) {
                    $q->where('status', 'paid');
                }])
                ->withSum(['donations as total_raised' => function($q) {
                    $q->where('status', 'paid');
                }], 'amount')
                ->where('status', 'active')
                ->orderByDesc('total_raised')
                ->limit(5)
                ->get(),
        ]);
    }
}