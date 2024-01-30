<?php

declare(strict_types=1);

namespace BeycanPress\Payeer;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class BlocksGateway extends AbstractPaymentMethodType
{
    /**
     * @var Gateway
     */
    // @phpcs:ignore
    private $gateway;

    /**
     * @var string
     */
    // @phpcs:ignore
    protected $name;

    /**
     * @var array<string,mixed>
     */
    // @phpcs:ignore
    protected $settings = [];

    /**
     * @var string
     */
    private string $scriptId = 'payeer-blocks';

    /**
     * @return void
     */
    public function __construct()
    {
        $this->name = Gateway::ID;
        add_action('woocommerce_blocks_enqueue_checkout_block_scripts_after', [$this, 'anotherAssets']);
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        $this->settings = get_option("woocommerce_{$this->name}_settings", []);
        $this->gateway = WC()->payment_gateways->payment_gateways()[$this->name];
    }

    /**
     * @return bool
     */
    // @phpcs:ignore
    public function is_active(): bool
    {
        return $this->gateway->is_available();
    }

    /**
     * @return array<string,mixed>
     */
    // @phpcs:ignore
    public function get_payment_method_data(): array
    {
        return [
            'name'     => $this->name,
            'label'    => $this->get_setting('title'),
            'icons'    => $this->get_payment_method_icons(),
            'content'  => $this->gateway->getPaymentFields(),
            'button'   => $this->get_setting('order_button_text'),
            'supports' => array_filter($this->gateway->supports, [$this->gateway, 'supports'])
        ];
    }

    /**
     * @return array<array<string,string>>
     */
    // @phpcs:ignore
    public function get_payment_method_icons(): array
    {
        return [
            [
                'id'  => $this->name,
                'alt' => $this->get_setting('title'),
                'src' => PAYEER_GATEWAY_URL . 'assets/images/payeer.png'
            ]
        ];
    }

    /**
     * @return array<string>
     */
    // @phpcs:ignore
    public function get_payment_method_script_handles(): array
    {
        wp_register_script(
            $this->scriptId,
            PAYEER_GATEWAY_URL . 'assets/js/blocks.js',
            [],
            PAYEER_GATEWAY_VERSION,
            true
        );

        return [$this->scriptId];
    }

    /**
     * @return void
     */
    public function anotherAssets(): void
    {
        if (wp_script_is($this->scriptId, 'registered')) {
            wp_enqueue_style(
                $this->scriptId,
                PAYEER_GATEWAY_URL . 'assets/css/blocks.css',
                [],
                PAYEER_GATEWAY_VERSION
            );
        }
    }
}
