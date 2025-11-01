<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','email','phone','category_id','from_date','to_date','nights',
        'base_total','weekend_surcharge','discount','final_total'
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(RoomCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }
}
