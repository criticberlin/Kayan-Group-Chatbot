<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasDepartment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, Auditable, HasDepartment;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'department_id',
        'import_id',
        'sku',
        'internal_code',
        'serial_number',
        'name',
        'name_arabic',
        'description',
        'description_arabic',
        'category',
        'category_arabic',
        'type_arabic',
        'type_english',
        'launch_date',
        'market_segment',
        'target_audience',
        'price',
        'currency',
        'stock_quantity',
        'manufacturer',
        'weight',
        'packaging_details',
        'shelf_life',
        'unit_barcode',
        'box_barcode',
        'carton_barcode',
        'selling_channel',
        'specifications',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the department this product belongs to.
     * Temporarily disabled due to null values causing Voyager errors
     */
    // public function department(): BelongsTo
    //{
    //    return $this->belongsTo(Department::class);
    //}

    /**
     * Get the import that created this product.
     */
    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    /**
     * Get the user who created this product.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this product.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to products in stock.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope to products by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for full-text search.
     */
    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['name', 'description'], $term);
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Get formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return sprintf('%s %s', $this->currency, number_format($this->price, 2));
    }
}
