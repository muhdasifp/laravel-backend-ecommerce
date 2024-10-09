<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'image',
        'stock',
        'category_id',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    // public function carts()
    // {
    //     return $this->hasMany(Cart::class);
    // }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
