<?php
namespace PromotedProduct;

/**
 * The front-end specific functionality of the plugin.
 *
 * Contains all neccessary calls to make front-end of the plugin work.
 *
 * @package    PromotedProduct
 */
class Core {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
    protected $version;

	/**
	 * Plugin name
	 *
	 * @var string
	 */
    protected $plugin_name;

	/**
	 * Constructor
	 *
	 * Constructor sets up basic information about plugin,
	 * then calls add_hooks() method to instantiate front-end work.
	 *
	 * @return void
	 */
    public function __construct() {
		if ( defined( 'PROMOTED_PRODUCT_VERSION' ) ) {
			$this->version = PROMOTED_PRODUCT_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'promoted-product';
        $this->add_hooks();
	}

	/**
     * Add hooks to WordPress schedule.
     *
     * @return void
     */
	public function add_hooks() {
		add_action( 'init', array($this, 'load_plugin_textdomain') );
        add_filter('wp_footer', array( get_called_class(), 'generate_frontend'), 1); // Render the element in footer. Explanation in /includes/js/scripts.js.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
	}

	/**
	 * Loads plugin text domain
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'promoted-product',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
     * Enqueues the scripts and styles used by plugin on front-end.
     *
     * @return void
     */
	public function enqueue_scripts_and_styles() {
        wp_enqueue_script(
            'promoted-product-scripts-front',
            get_bloginfo('url') . '/wp-content/plugins/promoted-product/includes/js/scripts.js',
            false,
            PROMOTED_PRODUCT_VERSION
        );

		wp_enqueue_style(
            'promoted-product-styles-front',
            get_bloginfo('url') . '/wp-content/plugins/promoted-product/includes/css/style.css',
            false,
            PROMOTED_PRODUCT_VERSION
        );
    }

	/**
	 * Generates front-end code of the plugin
	 *
	 * If a product is selected to be promoted,
	 * checks if promotion is timed and valid,
	 * then gets the data and calls Renderer.
	 *
	 * @return void
	 */
	public static function generate_frontend() {
		$data_getter = \PromotedProduct\DataGetter::getInstance();
		$currently_promoted_product_id = $data_getter->get_currently_promoted_product_id();
		if (!$currently_promoted_product_id) {
			return;
		}

		$valid = DataGetter::check_time_validity($currently_promoted_product_id);
		if ($valid) {
			$data = DataGetter::get_data_for_frontend($currently_promoted_product_id);
			Renderer::render_frontend($data);
		}
	}
}