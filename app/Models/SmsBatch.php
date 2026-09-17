<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsBatch extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'body',
        'audience',
        'class_id',
        'total_recipients',
        'sent_count',
        'failed_count',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SmsTemplate::class, 'template_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'batch_id');
    }
}
