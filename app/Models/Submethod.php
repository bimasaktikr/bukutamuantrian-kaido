<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submethod extends Model
{
    //

    // add fillable
    protected $fillable = ['name','method_id'];
    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function Method() : BelongsTo
    {
        return $this->belongsTo(Method::class);
    }

    public function Transaction() : BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
