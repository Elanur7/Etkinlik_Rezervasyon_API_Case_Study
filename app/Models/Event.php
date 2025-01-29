<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    // Hangi alanların kütüphanede kullanılabilir olduğunu belirtiriz.
    protected $fillable = [
        'name',
        'description',
        'venue_id',
        'start_date',
        'end_date'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function seats()
    {
        return $this->hasManyThrough(Seat::class, Venue::class, 'id', 'venue_id', 'venue_id', 'id');
    }
}
