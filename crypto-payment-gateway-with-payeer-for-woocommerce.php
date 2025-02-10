<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

// @phpcs:disable PSR1.Files.SideEffects
// @phpcs:disable PSR12.Files.FileHeader
// @phpcs:disable Generic.Files.LineLength

/**
 * Plugin Name: Crypto Payment Gateway with Payeer for WooCommerce
 * Version:     1.0.2
 * Requires Plugins: woocommerce
 * Plugin URI:  https://beycanpress.com/
 * Description: Payeer payment gateway for WooCommerce
 * Author: BeycanPress
 * Author URI:  https://beycanpress.com
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: crypto-payment-gateway-with-payeer-for-woocommerce
 * Domain Path: /languages
 * Tags: Payeer, Cryptocurrency, WooCommerce, Payment, Gateway
 * Requires at least: 5.0
 * Tested up to: 6.7.1
 * Requires PHP: 8.1
*/

require __DIR__ . '/vendor/autoload.php';

define('BP_PAYEER_GATEWAY_VERSION', '1.0.1');
define('BP_PAYEER_GATEWAY_URL', plugin_dir_url(__FILE__));
define('BP_PAYEER_GATEWAY_PATH', plugin_dir_path(__FILE__));

new BeycanPress\Payeer\OtherPlugins(__FILE__);

add_action('before_woocommerce_init', function (): void {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
});

add_action('woocommerce_blocks_loaded', function (): void {
    if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        add_action('woocommerce_blocks_payment_method_type_registration', function ($registry): void {
            $registry->register(new BeycanPress\Payeer\BlocksGateway());
        });
    }
});

add_action('plugins_loaded', function (): void {
    add_filter('woocommerce_payment_gateways', function ($gateways) {
        $gateways[] = \BeycanPress\Payeer\Gateway::class;
        return $gateways;
    });
});
