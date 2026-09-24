<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkItem extends Model
{
    use HasFactory;

    protected $table = 'atk_items';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Item belongs to a Category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    /**
     * Relationship: Item has many Variants.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(AtkVariant::class, 'item_id');
    }
}
