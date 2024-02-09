<?php

use Otomaties\WooCommerceExtraCheckoutSteps\Plugin;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\View;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Steps;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Assets;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Config;
use Otomaties\WooCommerceExtraCheckoutSteps\Helpers\Loader;

/**
 * Plugin Name:       Otomaties WooCommerce Extra Checkout Steps
 * Description:       Add extra steps to the checkout process.
 * Version:           1.0.0
 * Author:            Tom Broucke
 * Author URI:        https://tombroucke.be/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-extra-checkout-steps
 * Domain Path:       /resources/languages
 */

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Get main plugin class instance
 *
 * @return Plugin
 */
function WooCommerceExtraCheckoutSteps()
{
    static $plugin;

    if (!$plugin) {
        $plugin = new Plugin(
            new Loader(),
            new Config()
        );
        do_action('woocommerce_extra_checkout_steps', $plugin);
    }

    return $plugin;
}

// Bind the class to the service container
add_action('woocommerce_extra_checkout_steps', function ($plugin) {
    $plugin->bind(Loader::class, function ($plugin) {
        return $plugin->getLoader();
    });
    $plugin->bind(View::class, function ($plugin) {
        return new View($plugin->config('paths.views'));
    });
    $plugin->bind(Assets::class, function ($plugin) {
        return new Assets($plugin->config('paths.public'));
    });
    $plugin->singleton(Steps::class, function ($plugin) {
        return new Steps();
    });
    $plugin->bind('form-fields', function ($plugin, $args) {
        return $plugin->make(View::class)->render('form-fields', [
            'id' => get_the_ID(),
            'name' => $args['name'],
        ]);
    });
}, 10);

// Initialize the plugin and run the loader
add_action('woocommerce_extra_checkout_steps', function ($plugin) {
    $plugin
        ->initialize()
        ->runLoader();
}, 9999);

WooCommerceExtraCheckoutSteps();
