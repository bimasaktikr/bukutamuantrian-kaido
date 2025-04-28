<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    //

    // add fillable
    protected $fillable = [
        'customer_id',
        'submethod_id',
        'service_id',
        'purpose_id',
        'status',
        'date',
    ];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function purpose(): BelongsTo
    {
        return $this->belongsTo(Purpose::class);
    }

    public function submethod(): BelongsTo
    {
        return $this->belongsTo(Submethod::class);
    }

    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class);
    }
    // public function queue(): BelongsTo
    // {
    //     return $this->belongsTo(Queue::class);
    // }

}
