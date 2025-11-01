<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'base_price'];

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class, 'category_id');
    }
}
