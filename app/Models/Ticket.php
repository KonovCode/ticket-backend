<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['title', 'description', 'city', 'date', 'time', 'price', 'address', 'user_id'];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
       return $this->belongsTo(User::class);
    }
}
