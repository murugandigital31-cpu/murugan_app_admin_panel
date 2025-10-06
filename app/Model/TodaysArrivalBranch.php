<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TodaysArrivalBranch extends Model
{
    use HasFactory;

    protected $table = 'todays_arrival_branches';

    protected $fillable = [
        'name',
        'location',
        'whatsapp_number',
        'contact_person',
        'address',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function todaysArrivals()
    {
        // Since branch_id is stored as JSON array, we need a custom relationship
        return $this->hasManyThrough(
            TodaysArrival::class,
            static::class,
            'id',
            'id',
            'id',
            'id'
        )->whereRaw("JSON_CONTAINS(branch_id, '\"$this->id\"')");
    }

    // Accessors
    public function getFormattedWhatsappAttribute()
    {
        $number = preg_replace('/[^0-9]/', '', $this->whatsapp_number);
        if (substr($number, 0, 1) !== '+') {
            $number = '+' . $number;
        }
        return $number;
    }

    public function getWhatsappLinkAttribute()
    {
        return 'https://wa.me/' . preg_replace('/[^0-9]/', '', $this->whatsapp_number);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithArrivals($query)
    {
        return $query->with(['todaysArrivals' => function($q) {
            $q->where('is_active', true)->where('arrival_date', '>=', now()->startOfDay());
        }]);
    }
}