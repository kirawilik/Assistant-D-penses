<?php

namespace App\Models;

use App\Enums\DepenseCategorie;
use Database\Factories\DepenseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    /** @use HasFactory<DepenseFactory> */
    use HasFactory;

    protected $fillable = [
        'recu_id',
        'libelle',
        'quantite',
        'prix_unitaire',
        'categorie',
    ];

    protected function casts(): array
    {
        return [
            'categorie' => DepenseCategorie::class,
            'prix_unitaire' => 'decimal:2',
        ];
    }

    public function recu(): BelongsTo
    {
        return $this->belongsTo(Recu::class);
    }
}
