<?php

namespace Vortex\Core\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'is_active',
        'is_default',
        'sort_order',
        'instructions',
        'configuration',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'configuration' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the configuration value for a specific key.
     */
    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->configuration, $key, $default);
    }

    /**
     * Set a configuration value.
     */
    public function setConfigValue(string $key, mixed $value): void
    {
        $config = $this->configuration ?? [];
        data_set($config, $key, $value);
        $this->configuration = $config;
    }

    /**
     * Calculate fee for a given amount.
     */
    public function calculateFee(float $amount): float
    {
        $fee = $this->getConfigValue('handling_fee', 0);
        $feeType = $this->getConfigValue('handling_fee_type', 'flat');

        if ($feeType === 'percentage') {
            return ($amount * $fee) / 100;
        }

        return $fee;
    }

    /**
     * Check if method is available for the given amount.
     */
    public function isAvailableForAmount(float $amount): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $minAmount = $this->getConfigValue('min_order_amount', 0);
        $maxAmount = $this->getConfigValue('max_order_amount');

        if ($amount < $minAmount) {
            return false;
        }

        if ($maxAmount !== null && $amount > $maxAmount) {
            return false;
        }

        return true;
    }

    /**
     * Check if payment method is available for a specific country.
     */
    public function isAvailableForCountry(string $countryCode): bool
    {
        $allowedCountries = $this->getConfigValue('allowed_countries', ['*']);

        if (in_array('*', $allowedCountries)) {
            return true;
        }

        return in_array($countryCode, $allowedCountries);
    }

    /**
     * Get all active payment methods.
     */
    public static function active()
    {
        return static::where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get the default payment method.
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    /**
     * Set this method as default.
     */
    public function setAsDefault(): bool
    {
        // Remove default from all other methods
        static::where('is_default', true)->update(['is_default' => false]);

        // Set this as default
        return $this->update(['is_default' => true]);
    }
}
