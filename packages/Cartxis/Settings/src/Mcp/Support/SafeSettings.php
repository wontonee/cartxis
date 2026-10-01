<?php

declare(strict_types=1);

namespace Cartxis\Settings\Mcp\Support;

final class SafeSettings
{
    /**
     * Keys that MCP may read or write (general / store only — never secrets).
     *
     * @var list<string>
     */
    public const WHITELIST = [
        // General
        'site_name',
        'site_tagline',
        'admin_email',
        'contact_phone',
        'contact_address',
        'store_country',
        'meta_title',
        'meta_description',
        'meta_keywords',
        // Store
        'store_name',
        'store_description',
        'business_registration',
        'vat_number',
        'store_license',
        'store_email',
        'support_email',
        'store_phone',
        'store_phone_alt',
        'store_whatsapp',
        'store_address_1',
        'store_address_2',
        'store_city',
        'store_state',
        'store_postal_code',
        'store_timezone',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_linkedin',
        'social_youtube',
        'social_tiktok',
        'social_pinterest',
        'checkout_allow_guest',
        'checkout_require_account',
    ];

    /**
     * @var array<string, list<string>>
     */
    public const GROUPS = [
        'general' => [
            'site_name',
            'site_tagline',
            'admin_email',
            'contact_phone',
            'contact_address',
            'store_country',
            'meta_title',
            'meta_description',
            'meta_keywords',
        ],
        'store' => [
            'store_name',
            'store_description',
            'business_registration',
            'vat_number',
            'store_license',
            'store_email',
            'support_email',
            'store_phone',
            'store_phone_alt',
            'store_whatsapp',
            'store_address_1',
            'store_address_2',
            'store_city',
            'store_state',
            'store_postal_code',
            'store_timezone',
            'social_facebook',
            'social_instagram',
            'social_twitter',
            'social_linkedin',
            'social_youtube',
            'social_tiktok',
            'social_pinterest',
            'checkout_allow_guest',
            'checkout_require_account',
        ],
    ];

    public static function isAllowed(string $key): bool
    {
        return in_array($key, self::WHITELIST, true);
    }

    /**
     * @return list<string>
     */
    public static function keysForGroup(?string $group): array
    {
        if ($group === null || $group === '') {
            return self::WHITELIST;
        }

        return self::GROUPS[$group] ?? [];
    }

    public static function groupForKey(string $key): string
    {
        foreach (self::GROUPS as $group => $keys) {
            if (in_array($key, $keys, true)) {
                return $group;
            }
        }

        return 'general';
    }
}
