<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['user', 'category']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $campaigns = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json($campaigns);
    }

    public function verify($id)
    {
        $campaign = Campaign::findOrFail($id);

        $campaign->update([
            'status' => 'active',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Campaign verified successfully', 'campaign' => $campaign]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);

        $campaign = Campaign::findOrFail($id);

        $campaign->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return response()->json(['message' => 'Campaign rejected', 'campaign' => $campaign]);
    }

    public function toggleFeatured($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->is_featured = !$campaign->is_featured;
        $campaign->save();

        return response()->json(['message' => 'Featured status updated', 'is_featured' => $campaign->is_featured]);
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted']);
    }
}