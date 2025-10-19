<?php

namespace Vortex\Cart\Http\Controllers;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Display the shopping cart page
     */
    public function index(): Response
    {
        return Inertia::render('Frontend/Cart/Index', [
            'pageTitle' => 'Shopping Cart',
        ]);
    }
}
