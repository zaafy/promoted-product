<?php
namespace PromotedProduct\Admin;

/**
 * The admin specific functionality of the plugin.
 *
 * Contains all neccessary calls to make admin side of the plugin work.
 *
 * @package    PromotedProduct
 */
class Core {
	public function __construct() {
        $this->init_metaboxes();
        $this->add_hooks();
	}

    /**
     * Add hooks to WordPress schedule.
     *
     * @return void
     */
    public function add_hooks() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts_and_styles'));
        add_filter( 'woocommerce_get_sections_products' , array($this, 'add_new_section_to_settings') );
        add_filter( 'woocommerce_get_settings_products' , array($this, 'add_fields_to_settings') , 10, 2 );
    }

    /**
     * Add 'Promoted Product' section to WooCommerce settings page.
     *
     * @param array $settings_tabs WooCommerce action ('woocommerce_get_sections_products) default param.
     * @return array $settings_tabs
     */
    public function add_new_section_to_settings($settings_tabs) {
        $settings_tabs['promoted_product_settings'] = __( 'Promoted Product' );
        return $settings_tabs;
    }

    /**
     * Undocumented function
     *
     * @param array $settings WooCommerce action ('woocommerce_get_sections_products) default param.
     * @param string $current_section Current section of WooCommerce settings tab.
     * @return array $settings An array of WooCommerce settings fields.
     */
    public function add_fields_to_settings($settings, $current_section) {
        if ($current_section == 'promoted_product_settings') {
            $data_getter = \PromotedProduct\DataGetter::getInstance();
            $currently_promoted_product_id = $data_getter->get_currently_promoted_product_id();
            if ($currently_promoted_product_id) {
                $name = get_the_title($currently_promoted_product_id);
                $link = get_edit_post_link($currently_promoted_product_id);
                $currently_promoted_text = __('Currently promoted product', 'promoted-product');
                $link_text = __('Edit product', 'promoted-product');
                $display = <<<HTML
                    <p>$currently_promoted_text: <strong>$name</strong> - <a href="$link">$link_text</a></p>
HTML;
            } else {
                $no_promoted_product_text = __('You are currently not promoting any product. You can do it by going to product edit page and checking the "Promote this product" checkbox.', 'promoted-product');
                $display = <<<HTML
                <p>$no_promoted_product_text</p>
HTML;
            }

            $settings = array(
                array(
                    'title'    => __( 'Promoted Product', 'promoted-product' ),
                    'type'     => 'title',
                    'desc'     => $display,
                    'id'       => 'promoted_product_settings',
                ),
                array(
                    'title'    => __('Title', 'promoted-product'),
                    'desc'     => __('Enter the title shown before the promoted product.', 'promoted-product'),
                    'id'       => 'promoted_product_promotion_title',
                    'default'  => '',
                    'type'     => 'text',
                    'desc_tip' => true,
                ),
                array(
                    'title'    => __('Background Color', 'promoted-product'),
                    'desc'     => __('Select the background color of presented promoted product.', 'promoted-product'),
                    'id'       => 'promoted_product_promotion_background_color',
                    'default'  => '',
                    'type'     => 'color',
                    'desc_tip' => true,
                ),
                array(
                    'title'    => __('Text Color', 'promoted-product'),
                    'desc'     => __('Select the text color of presented promoted product.', 'promoted-product'),
                    'id'       => 'promoted_product_promotion_text_color',
                    'default'  => '',
                    'type'     => 'color',
                    'desc_tip' => true,
                ),
                array(
                    'type'     => 'sectionend',
                    'id'       => 'promoted_product_settings',
                ),
            );

            return $settings;
        } else {
            return $settings;
        }
    }

    /**
     * Initializes Metaboxes instance and inits the creation of the fields.
     *
     * @return void
     */
    public function init_metaboxes() {
        $metaboxes = new Metaboxes();
        $metaboxes->init();
    }

    /**
     * Enqueues the scripts and styles used by plugin in admin panel.
     *
     * @return void
     */
    public function enqueue_admin_scripts_and_styles() {
        wp_enqueue_style(
            'promoted-product-styles-admin',
            get_bloginfo('url') . '/wp-content/plugins/promoted-product/admin/css/admin-style.css',
            false,
            PROMOTED_PRODUCT_VERSION
        );

        wp_enqueue_script(
            'promoted-product-scripts-admin',
            get_bloginfo('url') . '/wp-content/plugins/promoted-product/admin/js/admin-scripts.js',
            false,
            PROMOTED_PRODUCT_VERSION
        );
    }
}
