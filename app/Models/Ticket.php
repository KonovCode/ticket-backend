<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'description',
        'city',
        'date',
        'time',
        'price',
        'address',
        'user_id',
        'total_tickets',
        'available_tickets'
    ];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
       return $this->belongsTo(User::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketOrder::class);
    }
}
