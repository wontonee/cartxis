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
];
