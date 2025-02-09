<?php

declare(strict_types=1);

// @phpcs:disable Generic.Files.LineLength
// @phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage

namespace BeycanPress\Payeer;

class Gateway extends \WC_Payment_Gateway
{
    /**
     * @var string
     */
    public const ID = 'bp_payeer_gateway';

    /**
     * @var string
     */
    private string $payeerUrl = 'https://payeer.com/merchant/';

    /**
     * @var string
     */
    private string $payeer_merchant;

    /**
     * @var string
     */
    private string $payeer_secret_key;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $id;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $method_title;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $method_description;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $title;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $description;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $enabled;

    /**
     * @var string
     */
    // @phpcs:ignore
    public $order_button_text;

    /**
     * @var array<string>
     */
    // @phpcs:ignore
    public $supports;

    /**
     * @var array<mixed>
     */
    // @phpcs:ignore
    public $form_fields;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->id = self::ID;
        $this->method_title = esc_html__('Payeer', 'crypto-payment-gateway-with-payeer-for-woocommerce');
        $this->method_description = esc_html__('Payeer payment gateway', 'crypto-payment-gateway-with-payeer-for-woocommerce');

        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = ['products'];

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        /** @disregard */
        $this->init_settings();
        /** @disregard */
        $this->title = $this->get_option('title');
        /** @disregard */
        $this->enabled = $this->get_option('enabled');
        /** @disregard */
        $this->description = $this->get_option('description');
        /** @disregard */
        $this->order_button_text = $this->get_option('order_button_text');
        /** @disregard */
        $this->payeer_merchant = $this->get_option('payeer_merchant');
        /** @disregard */
        $this->payeer_secret_key = $this->get_option('payeer_secret_key');

        add_action('woocommerce_api_bp-payeer-gateway-callback', [(new Callback()), 'init']);
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    /**
     * @return void
     */
    // @phpcs:ignore
    public function init_form_fields(): void
    {
        $this->form_fields = [
            'enabled' => [
                'title'       => esc_html__('Enable/Disable', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'label'       => esc_html__('Enable', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type'        => 'checkbox',
                'default'     => 'no'
            ],
            'title' => [
                'title'       => esc_html__('Title', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type'        => 'text',
                'description' => esc_html__('This controls the title which the user sees during checkout.', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'default'     => esc_html__('Pay with Payeer', 'crypto-payment-gateway-with-payeer-for-woocommerce')
            ],
            'description' => [
                'title'       => esc_html__('Description', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type'        => 'textarea',
                'description' => esc_html__('This controls the description which the user sees during checkout.', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'default'     => esc_html__('Pay with Payeer', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
            ],
            'order_button_text' => [
                'title'       => esc_html__('Order button text', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type'        => 'text',
                'description' => esc_html__('Pay button on the checkout page', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'default'     => esc_html__('Pay to Payeer', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
            ],
            'payment_complete_order_status' => [
                'title'   => esc_html__('Payment complete order status', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type'    => 'select',
                'help'    => esc_html__('The status to apply for order after payment is complete.', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'options' => [
                    'wc-completed' => esc_html__('Completed', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                    'wc-processing' => esc_html__('Processing', 'crypto-payment-gateway-with-payeer-for-woocommerce')
                ],
                'default' => 'wc-completed',
            ],
            'payeer_merchant' => [
                'title' => esc_html__('ID of the store', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type' => 'text',
                'description' => esc_html__('Identifier of store registered in the system "PAYEER" Get it in "Account -> Merchant -> Settings".', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'default' => ''
            ],
            'payeer_secret_key' => [
                'title' => esc_html__('Secret key', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'type' => 'password',
                'description' => esc_html__('The secret key notification that payment has been made,which is used to check the integrity of received information and unequivocal identification of the sender. Must match the secret key specified in : the "Account -> Merchant -> Settings".', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
                'default' => ''
            ],
            'payeer_success_url' => [
                'title'       => home_url('wc-api/bp-payeer-gateway-callback?action=success'),
                'type'        => 'title',
                'description' => esc_html__('You need to enter this URL in the "Success URL" field in your merchant settings. ', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
            ],
            'payeer_fail_url' => [
                'title'       => home_url('wc-api/bp-payeer-gateway-callback?action=fail'),
                'type'        => 'title',
                'description' => esc_html__('You need to enter this URL in the "Fail URL" field in your merchant settings.', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
            ],
            'payeer_status_url' => [
                'title'       => home_url('wc-api/bp-payeer-gateway-callback?action=status'),
                'type'        => 'title',
                'description' => esc_html__('You need to enter this URL in the "Status URL" field in your merchant settings.', 'crypto-payment-gateway-with-payeer-for-woocommerce'),
            ],
        ];
    }

    /**
     * @param string $key
     * @return string|null
     */
    // @phpcs:ignore
    public static function get_option_custom(string $key): ?string
    {
        $options = get_option('woocommerce_' . self::ID . '_settings');
        return isset($options[$key]) ? $options[$key] : null;
    }

    /**
     * @return mixed
     */
    // @phpcs:ignore
    public function get_icon() : string
    {
        return '<img src="' . plugins_url('assets/images/payeer.png', dirname(__FILE__)) . '" alt="Payeer" />';
    }

    /**
     * @return string
     */
    public function getPaymentFields(): string
    {
        ob_start();
        $this->payment_fields();
        return ob_get_clean();
    }

    /**
     * @return void
     */
    // @phpcs:ignore
    public function payment_fields(): void
    {
        // @phpcs:disable
        echo esc_html($this->description);
        ?>
        - <span class="py-footer">
            <span class="powered-by">
                Powered by
            </span>
            <a href="https://beycanpress.com/cryptopay/?utm_source=payeer_plugin&amp;utm_medium=powered_by" target="_blank">CryptoPay</a>
        </span>
        <?php
        // @phpcs:enable
    }

    /**
     * @param int $orderId
     * @return array<string,string>
     */
    // @phpcs:ignore
    public function process_payment($orderId): array
    {
        $order = new \WC_Order($orderId);

        $order = wc_get_order($orderId);
        $desc = base64_encode($order->get_customer_note());
        $amount = number_format((float) $order->get_total(), 2, '.', '');
        $currency = $order->get_currency();
        $storeId = $this->payeer_merchant;
        $secret = $this->payeer_secret_key;

        $sign = strtoupper(hash('sha256', implode(":", [
            $storeId,
            $orderId,
            $amount,
            $currency,
            $desc,
            $secret,
        ])));

        $url = $this->payeerUrl . '?' . http_build_query([
            'm_shop' => $storeId,
            'm_orderid' => $orderId,
            'm_amount' => $amount,
            'm_curr' => $currency,
            'm_desc' => $desc,
            'm_sign' => $sign
        ]);

        $order->update_status(
            'wc-pending',
            esc_html__('Payment is awaited.', 'crypto-payment-gateway-with-payeer-for-woocommerce')
        );

        $order->add_order_note(
            esc_html__(
                'Customer has chosen Payeer payment method, payment is pending.',
                'crypto-payment-gateway-with-payeer-for-woocommerce'
            )
        );

        // Return thankyou redirect
        return [
            'result' => 'success',
            'redirect' => $url
        ];
    }
}
