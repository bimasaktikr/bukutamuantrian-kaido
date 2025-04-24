<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Queue extends Model
{
    //

    // add fillable
    protected $fillable = [
        'number',
        'transaction_id',
        'operator_id',
        'status',
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function  transaction() : BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function  operator() : BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }
}
