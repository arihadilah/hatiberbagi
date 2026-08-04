<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'target_amount',
        'deadline',
        'status',
        'is_featured'
    ];

    protected $casts = [
        'deadline' => 'date',
        'verified_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Accessor: persentase progress
    public function getProgressPercentAttribute()
    {
        if ($this->target_amount == 0) return 0;
        return min(100, round(($this->raised_amount / $this->target_amount) * 100, 1));
    }

    // Accessor: sisa hari
    public function getDaysLeftAttribute()
    {
        return max(0, now()->diffInDays($this->deadline, false));
    }

    // Scope: campaign aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope: campaign featured
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}