<?php defined('ABSPATH') || exit;

/**
 * Plugin Name: Payeer for WooCommerce
 * Version:     1.0.1
 * Plugin URI:  https://beycanpress.com/
 * Description: Payeer payment gateway for WooCommerce
 * Author: BeycanPress
 * Author URI:  https://beycanpress.com
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: payeer_gateway
 * Domain Path: /languages
 * Tags: Payeer, Cryptocurrency, WooCommerce, WordPress, Ethereum, Bitcoin, Payment, Plugin, Gateway
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
*/

require __DIR__ . '/vendor/autoload.php';

new \BeycanPress\Payeer\OtherPlugins(__FILE__);

add_action('plugins_loaded', function () {
    add_filter('woocommerce_payment_gateways', function($gateways) {
        $gateways[] = \BeycanPress\Payeer\Gateway::class;
        return $gateways;
    });
});