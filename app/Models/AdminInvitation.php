<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'created_by',
        'expires_at',
        'registered_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}