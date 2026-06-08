<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['request_id', 'customer_id', 'message'])]
class ClaimRequestMessage extends Model
{
    protected $table = 'claim_request_message';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'request_id' => 'integer',
            'customer_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function claimRequest(): BelongsTo
    {
        return $this->belongsTo(ClaimRequest::class, 'request_id', 'request_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
