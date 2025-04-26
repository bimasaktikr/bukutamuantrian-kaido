<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Whatsapp extends Model
{
    // add fillable
    protected $fillable = [
        'key',
        'status',
        'session_name',
        'server_host_url',
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
