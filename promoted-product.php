<?php
namespace PromotedProduct;
/**
 * Plugin Name: Promoted Product
 * Plugin URI: #!
 * Description: This plugin adds the "Promoted Product" functionality to WooCommerce stores. It allows to select a product as promoted and display it on every page of your store.
 * Version: 1.0.0
 * Author: Daniel Okoń
 * Author URI: #!
 * Developer: Daniel Okoń
 * Text Domain: promoted-product
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('PROMOTED_PRODUCT_VERSION', "1.0.0");

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main plugin class.
 *
 * @package PromotedProduct
 */
class Init {
    /**
     * Constructor
     *
     * Checks if WooCommerce is active.
     * If it is, loads the plugin code.
     * If it's not, displays an error notice in admin panel.
     * Always shows activation message when plugin is activated.
     */
    public function __construct() {
        /**
         * Only run plugin if WooCommerce is active.
         * Otherwise, display an error message.
         **/
        if (
            in_array(
                'woocommerce/woocommerce.php',
                apply_filters('active_plugins', get_option('active_plugins'))
            )
        ) {
            add_action('woocommerce_init', array($this, 'loadPlugin'));
        } else {
            \PromotedProduct\Admin\Notices::call_display_no_woocommerce_notice();
        }

        $this->setup_activation_notice();
        add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(get_called_class(), 'setup_settings_button'));
    }

    /**
     * Loads the plugin.
     *
     * @return void
     */
    public function loadPlugin() {
        $plugin = new \PromotedProduct\Core;
        $admin = new \PromotedProduct\Admin\Core;
    }

    /**
     * Adds 'Settings' link to plugin page entry.
     *
     * Settings link leads to WooCommerce settings page with proper tab and section open.
     *
     * @param array $links List of links present in a plugin entry.
     * @return void
     */
    public static function setup_settings_button($links) {
        $settings_link = '<a href="' . get_bloginfo('url') . '/wp-admin/admin.php?page=wc-settings&tab=products&section=promoted_product_settings">'. __('Settings', 'promoted-product') .'</a>';
        $links[] = $settings_link;
        return $links;
    }

    /**
     * Sets up activation notice in admin panel.
     *
     * Set up transient, then checks the transient to display a message once.
     *
     * @return void
     */
    public function setup_activation_notice() {
        register_activation_hook(__FILE__, array($this, 'activate_transient'));
        add_action('admin_notices', array($this, 'call_plugin_activated_notice'));
    }

    /**
     * Sets up transient on plugin activation.
     *
     * @return void
     */
    public function activate_transient() {
        set_transient( 'promoted_product_activation_notice', true, 5 );
    }

    /**
     * Prepares the notice content and calls the display method if transient is present.
     *
     * @return void
     */
    function call_plugin_activated_notice() {
        if (get_transient('promoted_product_activation_notice')) {
            ob_start();
            ?>
            <p>
                <?php _e('Thank you for using Promoted Product plugin!', 'promoted-product'); ?> <strong><?php _e('You are awesome!', 'promoted-product'); ?></strong>
            </p>
            <p>
                <?php _e('Please keep in mind, to use Promoted Product plugin, WooCommerce needs to be installed and active.', 'promoted-product'); ?>
            </p>
            <p>
                <?php _e('You can see the general settings here:', 'promoted-product'); ?> 
                <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/admin.php?page=wc-settings&tab=products&section=promoted_product_settings">
                    <?php _e('View the settings.', 'promoted-product'); ?>
                </a>
            </p>
            <p>
                <?php _e('To see the promoted product on your website, you need to go to the product you want to promote and check the option there.', 'promoted-product'); ?> 
                <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/edit.php?post_type=product">
                    <?php _e('Here\'s a shortcut.', 'promoted-product'); ?>
                </a>
            </p>
            <?php
            $message = ob_get_clean();
            \PromotedProduct\Admin\Notices::display_notice('success', $message);
            delete_transient('promoted_product_activation_notice');
        }
    }
}

$init = new Init();