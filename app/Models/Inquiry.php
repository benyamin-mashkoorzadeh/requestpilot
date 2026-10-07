<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'message',
    'status',
    'assigned_team',
    'requires_review',
    'intent',
    'intent_confidence',
    'priority',
    'priority_confidence',
    'reviewed_intent',
    'reviewed_priority',
    'reviewed_at',
])]
class Inquiry extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_review' => 'boolean',
            'intent_confidence' => 'float',
            'priority_confidence' => 'float',
            'reviewed_at' => 'datetime',
        ];
    }
}
