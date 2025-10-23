<?php

namespace Vortex\Cart\Http\Controllers;

use Illuminate\Routing\Controller;
use Vortex\Core\Services\ThemeViewResolver;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * @var ThemeViewResolver
     */
    protected $themeResolver;

    /**
     * Create a new controller instance.
     *
     * @param ThemeViewResolver $themeResolver
     */
    public function __construct(ThemeViewResolver $themeResolver)
    {
        $this->themeResolver = $themeResolver;
    }

    /**
     * Display the shopping cart page
     */
    public function index(): Response
    {
        return Inertia::render($this->themeResolver->resolve('Cart/Index'), [
            'pageTitle' => 'Shopping Cart',
        ]);
    }
}
