<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecretKey extends Model
{
    //

    // add fillable
    protected $fillable = [
        'key',
        'session_name',
        'token',
        'server_host_url',
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
}
