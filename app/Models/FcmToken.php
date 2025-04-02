<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FcmToken extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'usuario_id',
        'token',
        'device_type',
        'active'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
