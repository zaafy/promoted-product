<?php
namespace PromotedProduct\Admin;

/**
 * Static class to handle admin notices display.
 *
 * @package    PromotedProduct
 */
class Notices {
    /**
     * Disallow instatiating.
     */
    private function __construct() {}

    /**
     * Adds action to admin_notices to call No WooCommerce notice
     *
     * @return void
     */
    public static function call_display_no_woocommerce_notice() {
        add_action('admin_notices', array(get_called_class(), 'display_no_woocommerce_notice'));
    }

    /**
     * Calls the display function with message that WooCommerce needs to be active.
     *
     * @return void
     */
    public static function display_no_woocommerce_notice() {
        self::display_notice('error', '<p>' . __('To use Promoted Product plugin, WooCommerce needs to be installed and active.', 'promoted-product') . '</p>');
    }

    /**
     * Display an admin notice
     *
     * Display a notice of given 'type' with given 'message' in admin panel.
     * The notice is dismissible.
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public static function display_notice($type ='', $message) {
        echo <<<HTML
        <div class="notice notice-$type is-dismissible">
            $message
        </div>
HTML;
    }
}