<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Feedback extends Model
{
    //

    // add fillable
    protected $fillable = [
        'uuid',
        'transaction_id',
        'rate',
        'comment',
        'submited'
    ];
    // add guaded
    protected $guarded = ['uuid'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'submited' => 'boolean',
        'rate' => 'integer',
    ];


    protected static function booted()
    {
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
