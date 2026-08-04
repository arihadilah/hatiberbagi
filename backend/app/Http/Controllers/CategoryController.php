<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('is_active', true)
            ->withCount(['campaigns' => fn($q) => $q->where('status', 'active')])
            ->get();

        return response()->json($categories);
    }
}