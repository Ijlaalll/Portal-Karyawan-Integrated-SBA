<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AtkCategory extends Model
{
    use HasFactory;

    protected $table = 'atk_categories';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Category has many Items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(AtkItem::class, 'category_id');
    }
}
