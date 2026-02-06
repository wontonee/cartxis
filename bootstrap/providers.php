<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\ThemeServiceProvider::class,
    Cartxis\Core\Providers\CoreServiceProvider::class,
    Cartxis\Core\Providers\MailConfigServiceProvider::class,
    Cartxis\Admin\Providers\AdminServiceProvider::class,
    Cartxis\Product\Providers\ProductServiceProvider::class,
    Cartxis\Cart\Providers\CartServiceProvider::class,
    Cartxis\Shop\Providers\ShopServiceProvider::class,
    Cartxis\Settings\Providers\SettingsServiceProvider::class,
    Cartxis\Stripe\Providers\StripeServiceProvider::class,
    Cartxis\Razorpay\Providers\RazorpayServiceProvider::class,
    Cartxis\PhonePe\Providers\PhonePeServiceProvider::class,
    Cartxis\Sales\Providers\SalesServiceProvider::class,
    Cartxis\Customer\Providers\CustomerServiceProvider::class,
    Cartxis\Setup\Providers\SetupServiceProvider::class,  // Must be before CMS to prevent catch-all route conflict
    Cartxis\CMS\Providers\CMSServiceProvider::class,
    Cartxis\System\Providers\SystemServiceProvider::class,
    Cartxis\Reports\ReportsServiceProvider::class,
    Cartxis\Marketing\MarketingServiceProvider::class,
    Cartxis\API\Providers\APIServiceProvider::class,
];
