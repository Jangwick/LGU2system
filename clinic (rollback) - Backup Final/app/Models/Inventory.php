<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'quantity',
        'unit',
        'status',
        'supplier',
        'purchase_date',
        'expiration_date',
        'notes',
        'archive',
    ];

    public function consultations(): BelongsToMany
    {
        return $this->belongsToMany(Consultation::class, 'consultation_inventory')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
    }

    public function isLowStock()
    {
        return $this->quantity < $this->reorder_level;
    }
}
