<?php

namespace App\Models;

use App\Enums\RecuStatus;
use Database\Factories\RecuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recu extends Model
{
    /** @use HasFactory<RecuFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'texte_source',
        'statut',
        'payload_ia',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'statut' => RecuStatus::class,
            'payload_ia' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}

