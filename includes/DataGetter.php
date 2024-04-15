<?php
namespace PromotedProduct;

/**
 * A singleton to handle getting data needs.
 *
 * It can get product data, put together data for front-end use,
 * get the currently promoted product ID and check the validity of current promotion.
 *
 * @package    PromotedProduct
 */
class DataGetter {
    private static $instance;
    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}

    /**
     * ID of currently promoted product for caching purpose.
     *
     * @var boolean|integer False if no product is promoted, ID of a post if it is.
     */
    public $currently_promoted_id = false;

    /**
     * Singleton getInstance
     *
     * @return object DataGetter instance
     */
    public static function getInstance() {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Gets title and link to post.
     *
     * Gets title from post_meta if filled, or a regular title if not.
     * Link is obtrained from get_the_permalink() function
     *
     * @param integer $id ID of the WordPress post to get the data from.
     * @return array{title:string,link:string}
     */
    public static function get_post_data(int $id) {
        $title = get_post_meta($id, '_promoted_product_replacement_title');

        if (is_array($title) && !empty($title[0])) {
            $title = $title[0];
        } else {
            $title = get_the_title($id);
        }

        return array(
            'title' => $title,
            'link' => get_the_permalink($id)
        );
    }

    /**
     * Gets data required for front-end.
     *
     * Gets data from post_meta and wp_options, then puts it together in one neat bundle for usage later.
     *
     * @param integer $id ID of the WordPress post which is currently promoted to get the data from.
     * @return array{promoted_title_settings:string,product_title:string,product_link:string,background_color:string,text_color:string}
     */
    public static function get_data_for_frontend(int $id) {
        $product_data = self::get_post_data($id);
		$settings_title = get_option('promoted_product_promotion_title');
		$bgc = get_option('promoted_product_promotion_background_color');
		$text_color = get_option('promoted_product_promotion_text_color');

		return array(
			'promoted_title_settings' => $settings_title,
			'product_title' => $product_data['title'],
			'product_link' => $product_data['link'],
			'background_color' => $bgc,
			'text_color' => $text_color
        );
    }

    /**
     * Checks if post with given ID is timed and the expiry date.
     *
     * If the expiry date is in the past, disables the promotion.
     *
     * @param integer $id ID of the WordPress post to check validity of.
     * @return boolean
     */
    public static function check_time_validity(int $id) {
        $is_timed = get_post_meta($id, '_promoted_product_is_timed');

		if (is_array($is_timed) && $is_timed[0] == 'checked') {
			$ttl = get_post_meta($id, '_promoted_product_ttl');
			if (is_array($ttl) && !empty($ttl[0])) {
				$ttl = new \DateTime($ttl[0]);
				$ttl = $ttl->getTimestamp();
				$now = new \DateTime('now');
				$now = $now->getTimestamp();

				if ($ttl <= $now) {
					// if ttl is in the past, disable product promoting
					update_post_meta($id, '_promoted_product_is_promoted', array());
					// ttl is past, don't render front
					return false;
				}
			}
		}

        return true;
    }

    /**
     * Gets currently promoted product ID
     *
     * If cached, returns ID.
     * If not, queries the post_meta table to get currently promoted product ID
     *
     * @return boolean|integer False if not found or error, ID of a post if found.
     */
    public function get_currently_promoted_product_id() {
        // if cached
        if ($this->currently_promoted_id) {
            // return cached
            return $this->currently_promoted_id;
        } else {
            global $wpdb;
            $result = $wpdb->get_results( "select post_id from $wpdb->postmeta where meta_key = '_promoted_product_is_promoted' and meta_value = 'checked'", ARRAY_A );
            if ( $wpdb->last_error ) {
                error_log('wpdb error: ' . $wpdb->last_error);
                return false;
            } else {
                $result = !empty($result) ? $result[0]['post_id'] : false;
                // cache the ID
                $this->currently_promoted_id = $result;
                return $result;
            }
        }
    }
}

DataGetter::getInstance();