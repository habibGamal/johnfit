<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteSetting extends Model
{
    protected $fillable = [
        'next_quote_id',
        'last_sent_quote_id',
    ];

    public static function instance(): self
    {
        return static::firstOrCreate([]);
    }

    public function nextQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'next_quote_id');
    }

    public function lastSentQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'last_sent_quote_id');
    }

    public function resolveNextQuote(): ?Quote
    {
        if ($this->next_quote_id) {
            return Quote::find($this->next_quote_id);
        }

        $lastId = $this->last_sent_quote_id;

        $next = Quote::query()
            ->when($lastId, fn ($q) => $q->where('id', '>', $lastId))
            ->orderBy('id')
            ->first();

        // Wrap around to the first quote when we reach the end
        return $next ?? Quote::orderBy('id')->first();
    }
}
