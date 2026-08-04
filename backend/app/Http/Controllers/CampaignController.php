<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['user', 'category'])
            ->where('status', 'active');

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        $campaigns = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 12);

        return response()->json($campaigns);
    }

    public function show(string $slug)
    {
        $campaign = Campaign::with(['user', 'category', 'donations' => function($q) {
            $q->where('status', 'paid')->latest()->limit(10);
        }, 'comments.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($campaign);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'target_amount' => 'required|integer|min:100000',
            'deadline' => 'required|date|after:today',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($request->title);
        $data['status'] = 'pending';
        $data['raised_amount'] = 0;
        $data['donor_count'] = 0;

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('campaigns', 'public');
            $data['thumbnail'] = $path;
        }

        $campaign = Campaign::create($data);

        return response()->json($campaign, 201);
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::where('user_id', auth()->id())->findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'sometimes|string',
            'thumbnail' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('campaigns', 'public');
            $campaign->thumbnail = $path;
        }

        $campaign->update($request->except('thumbnail'));

        return response()->json($campaign);
    }
}