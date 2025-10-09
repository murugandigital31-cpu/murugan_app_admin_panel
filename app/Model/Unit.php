<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'units';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_name',
        'unit_short_name',
        'unit_type',
        'is_active',
        'is_default',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active units.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope a query to order units by sort order and name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('unit_name', 'asc');
    }

    /**
     * Scope a query to only include default units.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', 1);
    }

    /**
     * Get all products using this unit.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'unit', 'unit_short_name');
    }

    /**
     * Check if this unit can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        // Default units cannot be deleted
        if ($this->is_default) {
            return false;
        }

        // Units with associated products cannot be deleted
        if ($this->products()->count() > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get the display name with short name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->unit_name} ({$this->unit_short_name})";
    }
}

