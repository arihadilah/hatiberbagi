<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $campaignId)
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'is_anonymous' => 'boolean',
        ]);

        $campaign = Campaign::findOrFail($campaignId);

        $comment = Comment::create([
            'campaign_id' => $campaign->id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'is_anonymous' => $request->is_anonymous ?? false,
            'is_approved' => true,
        ]);

        return response()->json($comment, 201);
    }
}