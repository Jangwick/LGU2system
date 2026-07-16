<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Inventory;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'notes',
        'consulted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inventories(): BelongsToMany
    {
        return $this->belongsToMany(Inventory::class, 'consultation_inventory')
                    ->withPivot('quantity_given')
                    ->withTimestamps();
    }


}
