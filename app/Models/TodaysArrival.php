<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Model\TodaysArrivalBranch;
use App\Model\Product;

class TodaysArrival extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'poster_images',
        'main_poster',
        'arrival_date',
        'arrival_branch_id',
        'product_ids',
        'whatsapp_message_template',
        'whatsapp_enabled',
        'is_active',
        'show_in_app',
        'sort_order',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'poster_images' => 'array',
        'product_ids' => 'array',
        'whatsapp_enabled' => 'boolean',
        'is_active' => 'boolean',
        'show_in_app' => 'boolean',
    ];

    /**
     * Get the branch associated with this arrival
     */
    public function arrivalBranch(): BelongsTo
    {
        return $this->belongsTo(TodaysArrivalBranch::class, 'arrival_branch_id');
    }

    /**
     * Get products associated with this arrival
     */
    public function products()
    {
        if (!$this->product_ids) {
            return collect();
        }
        
        return Product::whereIn('id', $this->product_ids)->get();
    }

    /**
     * Get main poster URL
     */
    public function getMainPosterUrlAttribute()
    {
        if ($this->main_poster) {
            return asset('storage/' . $this->main_poster);
        }
        return null;
    }

    /**
     * Get all poster URLs
     */
    public function getPosterUrlsAttribute()
    {
        if (!$this->poster_images) {
            return [];
        }
        
        return array_map(function($image) {
            return asset('storage/' . $image);
        }, $this->poster_images);
    }

    /**
     * Get WhatsApp message for this arrival
     */
    public function getFormattedWhatsappMessageAttribute()
    {
        $template = $this->whatsapp_message_template ?: 
            "Hello! I'm interested in today's arrival: {title}\n\nDate: {date}\nBranch: {branch}\n\nPlease provide more details and pricing information.";
        
        return str_replace([
            '{title}',
            '{date}',
            '{branch}'
        ], [
            $this->title,
            $this->arrival_date->format('d/m/Y'),
            $this->arrivalBranch->name ?? ''
        ], $template);
    }

    /**
     * Scope for active arrivals
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for app visible arrivals
     */
    public function scopeAppVisible($query)
    {
        return $query->where('show_in_app', true);
    }

    /**
     * Scope for today's arrivals
     */
    public function scopeToday($query)
    {
        return $query->whereDate('arrival_date', today());
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('arrival_date', $date);
    }

    /**
     * Scope for specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('arrival_branch_id', $branchId);
    }

    /**
     * Scope for branches with WhatsApp enabled
     */
    public function scopeWhatsappEnabled($query)
    {
        return $query->where('whatsapp_enabled', true)
                    ->whereHas('arrivalBranch', function($q) {
                        $q->whereNotNull('whatsapp_number');
                    });
    }
}