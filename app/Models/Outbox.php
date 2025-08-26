<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outbox extends Model
{
    //

    // add fillable
    protected $fillable = [
        'to', 'message', 'related_type', 'related_id',
        'status', 'response_code', 'response_body', 'error', 'sent_at',
    ];

    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];


    public function related()
    {
        return $this->morphTo();
    }
}
