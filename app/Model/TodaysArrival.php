<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TodaysArrival extends Model
{
    use HasFactory;

    protected $table = 'todays_arrivals';

    protected $fillable = [
        'title',
        'description',
        'arrival_date',
        'branch_id',
        'main_poster',
        'poster_images',
        'product_ids',
        'is_active',
        'show_in_app',
        'sort_order'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'poster_images' => 'array',
        'product_ids' => 'array',
        'branch_id' => 'array',
        'is_active' => 'boolean',
        'show_in_app' => 'boolean',
        'sort_order' => 'integer'
    ];

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
        return $query->where(function($q) use ($branchId) {
            $q->whereJsonContains('branch_id', $branchId)
              ->orWhereJsonContains('branch_id', (string)$branchId);
        });
    }

    /**
     * Get the branches for this arrival
     */
    public function branches()
    {
        if (!$this->branch_id) {
            return collect();
        }
        
        return Branch::whereIn('id', $this->branch_id)->get();
    }

    /**
     * Get the products for this arrival
     */
    public function products()
    {
        if (!$this->product_ids) {
            return collect();
        }
        
        return Product::whereIn('id', $this->product_ids)->get();
    }

    /**
     * Get formatted poster images with full URLs
     */
    public function getFormattedPosterImagesAttribute()
    {
        if (!$this->poster_images) {
            return [];
        }

        $baseUrl = config('app.url');
        return array_map(function($image) use ($baseUrl) {
            return $baseUrl . '/storage/' . $image;
        }, $this->poster_images);
    }

    /**
     * Get main poster image
     */
    public function getMainPosterAttribute()
    {
        if (!$this->poster_images || count($this->poster_images) === 0) {
            return null;
        }

        return $this->poster_images[0];
    }

    /**
     * Get WhatsApp message for this arrival
     */
    public function getFormattedWhatsappMessageAttribute()
    {
        $message = "🛒 *New Arrival Available!*\n\n";
        $message .= "📦 *{$this->title}*\n";
        
        if ($this->description) {
            $message .= "📝 {$this->description}\n";
        }
        
        $message .= "📅 *Arrival Date:* " . $this->arrival_date->format('d/m/Y') . "\n\n";
        
        if ($this->product_ids) {
            $products = $this->products();
            if ($products->count() > 0) {
                $message .= "🛍️ *Available Products:*\n";
                foreach ($products->take(5) as $product) {
                    $message .= "• {$product->name}\n";
                }
                if ($products->count() > 5) {
                    $message .= "• ...and " . ($products->count() - 5) . " more items\n";
                }
            }
        }
        
        $message .= "\n✅ Please let us know if you're interested!";
        
        return $message;
    }

    /**
     * Get arrival branches relationship
     */
    public function arrivalBranches()
    {
        return $this->hasMany(TodaysArrivalBranch::class, 'arrival_id');
    }
}