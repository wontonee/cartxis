<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\ThemeServiceProvider::class,
    Vortex\Core\Providers\CoreServiceProvider::class,
    Vortex\Core\Providers\MailConfigServiceProvider::class,
    Vortex\Admin\Providers\AdminServiceProvider::class,
    Vortex\Product\Providers\ProductServiceProvider::class,
    Vortex\Cart\Providers\CartServiceProvider::class,
    Vortex\Shop\Providers\ShopServiceProvider::class,
    Vortex\Settings\Providers\SettingsServiceProvider::class,
    Vortex\Stripe\Providers\StripeServiceProvider::class,
    Vortex\Razorpay\Providers\RazorpayServiceProvider::class,
    Vortex\Sales\Providers\SalesServiceProvider::class,
    Vortex\Customer\Providers\CustomerServiceProvider::class,
    Vortex\Setup\Providers\SetupServiceProvider::class,  // Must be before CMS to prevent catch-all route conflict
    Vortex\CMS\Providers\CMSServiceProvider::class,
    Vortex\System\Providers\SystemServiceProvider::class,
    Vortex\Reports\ReportsServiceProvider::class,
    Vortex\Marketing\MarketingServiceProvider::class,
    Vortex\API\Providers\APIServiceProvider::class,
];
