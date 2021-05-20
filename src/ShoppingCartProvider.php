<?php

namespace Ndq\Shoppingcart;

use Illuminate\Support\ServiceProvider;
use Ndq\Shoppingcart\ShoppingCartImpl;

class ShoppingCartProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // publish the model
        $this->publishes([
            __DIR__.'/../app/' => app_path(),
            ]);

        $timestamp = date('Y_m_d_His', time());

        $this->publishes([
            __DIR__.'/../database/migrations/0000_00_00_000000_create_shopping_cart_table.php' => database_path('migrations/'.$timestamp.'_create_shopping_cart_table.php'),
            ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('cart', function ($app) {
            return new ShoppingCartImpl();
        });
    }
}
