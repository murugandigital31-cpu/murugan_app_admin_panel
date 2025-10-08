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
        'arrival_branch_id',
        'main_poster',
        'poster_images',
        'product_ids',
        'is_active',
        'show_in_app',
        'sort_order',
        'whatsapp_message_template',
        'whatsapp_enabled'
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'poster_images' => 'array',
        'product_ids' => 'array',
        'arrival_branch_id' => 'integer',
        'is_active' => 'boolean',
        'show_in_app' => 'boolean',
        'sort_order' => 'integer',
        'whatsapp_enabled' => 'boolean'
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
        return $query->where('arrival_branch_id', (int)$branchId);
    }

    /**
     * Get the branch for this arrival
     */
    public function branch()
    {
        if (!$this->arrival_branch_id) {
            return null;
        }

        return TodaysArrivalBranch::find($this->arrival_branch_id);
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