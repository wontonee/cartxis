<?php

namespace Vortex\Product\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'attribute_id',
        'attribute_option_id',
        'text_value',
        'boolean_value',
        'date_value',
    ];

    protected $casts = [
        'boolean_value' => 'boolean',
        'date_value' => 'date',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the attribute
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the attribute option
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(AttributeOption::class, 'attribute_option_id');
    }

    /**
     * Alias for option() - for backward compatibility
     */
    public function attributeOption(): BelongsTo
    {
        return $this->option();
    }

    /**
     * Get the value based on attribute type
     */
    public function getValue()
    {
        $attribute = $this->attribute;

        return match ($attribute->type) {
            'select', 'multiselect' => $this->option?->label,
            'text', 'textarea' => $this->text_value,
            'boolean' => $this->boolean_value,
            'date' => $this->date_value,
            default => $this->text_value,
        };
    }
}
