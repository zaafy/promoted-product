<?php
namespace PromotedProduct\Admin;

/**
 * Metaboxes class handles functionalities connected to metaboxes in WordPress.
 *
 * This class handles creating and updating the custom fields in single product edit page.
 *
 * @package    PromotedProduct
 */
class Metaboxes {
    private $fields = array(
        'promoted_product_is_promoted',
        'promoted_product_replacement_title',
        'promoted_product_is_timed',
        'promoted_product_ttl'
    );

    public function __construct() {}

    /**
     * Initializes the metabox creation process.
     *
     * It adds actions to WordPress scheduling to create the fields and save the data.
     *
     * @return void
     */
    public function init() {
        add_action( 'add_meta_boxes', array($this, 'create_metabox_for_product'));
        add_action( 'save_post', array($this, 'save_metaboxes_data'));
    }

    /**
     * Creates metabox for product single.
     *
     * This is a callback function for 'add_meta_boxes' action.
     *
     * @return void
     */
    public function create_metabox_for_product() {
        add_meta_box(
            'promote_product_fields', // Unique ID
            'Promoted Product', // Box title
            array($this, 'build_metabox_content'), // Content callback, must be of type callable
            'product' // Post type
        );
    }

    /**
     * Builds HTML structure of the metaboxes.
     *
     * Creates a wrapper for the data, then builds the structure and fills the content using data from post_meta table.
     *
     * @param object $post WP_Post instance given to callback function by add_meta_box() call.
     * @return void
     */
    public function build_metabox_content(object $post) {
        $postmeta = get_post_meta($post->ID);
        $time = date('Y-m-d\TH:m'); // YYYY-MM-DDThh:mm
        $show_time = false;
        if (isset($postmeta['_'.$this->fields[2]])) {
            $show_time = $postmeta['_'.$this->fields[2]][0] == 'checked' ? true : false;
        }
        $labels = array(
            'promote' => __('Promote this product?', 'promoted-product'),
            'title_replace' => __('Replace Product Title with this text', 'promoted-product'),
            'is_timed' => __('Is promoting timed?', 'promoted-product'),
            'time_to_live' => __('Promoting Deadline', 'promoted-product')
        );
        ?>
        <div class="promoted-product-metabox-wrapper">
            <div>
                <label for="<?php echo $this->fields[0] ?>">
                    <?php echo $labels['promote'] ?>
                </label>
                <input
                    type="checkbox"
                    name="<?php echo $this->fields[0] ?>"
                    id="<?php echo $this->fields[0] ?>"
                    value="checked"
                    <?php if (isset($postmeta['_'.$this->fields[0]]) && $postmeta['_'.$this->fields[0]][0] == 'checked') echo 'checked';?>
                >
            </div>
            <div>
                <label for="<?php echo $this->fields[1] ?>">
                    <?php echo $labels['title_replace'] ?>
                </label>
                <input
                    type="text"
                    name="<?php echo $this->fields[1] ?>"
                    id="<?php echo $this->fields[1] ?>"
                    value="<?php echo isset($postmeta['_'.$this->fields[0]]) ? $postmeta['_'.$this->fields[1]][0] : ''; ?>"
                >
            </div>
            <div>
                <label for="<?php echo $this->fields[2] ?>">
                    <?php echo $labels['is_timed'] ?>
                </label>
                <input
                    type="checkbox"
                    name="<?php echo $this->fields[2] ?>"
                    id="<?php echo $this->fields[2] ?>"
                    value="checked" <?php if ($show_time) echo 'checked';?>
                    onchange="manipulateTimePicker('<?php echo $this->fields[2] ?>', '<?php echo $this->fields[3] ?>');"
                >
            </div>
            <div class="<?php echo $show_time ? 'visible' : 'hidden'; ?> <?php echo $this->fields[3] ?>">
                <label for="<?php echo $this->fields[3] ?>">
                    <?php echo $labels['time_to_live'] ?>
                </label>
                <input
                    id="<?php echo $this->fields[3] ?>"
                    type="datetime-local"
                    name="<?php echo $this->fields[3] ?>"
                    value="<?php echo isset($postmeta['_'.$this->fields[0]]) && $postmeta['_'.$this->fields[3]][0] ? $postmeta['_'.$this->fields[3]][0] : $time ?>"
                />
            </div>
            <?php wp_nonce_field('save_metadata_promoted-' . $post->ID, 'save_metadata_promoted'); // set up nonce?>
        </div>
        <?php
    }

    /**
     * Saves data from metaboxes
     *
     * If user has permission to edit metaboxes data and nonce is valid,
     * save the data using update_post_meta().
     *
     * @param integer $post_id ID of WordPress post to which the data should be saved.
     * @return void
     */
    public function save_metaboxes_data(int $post_id) {
        // check if user has privileges and verify nonce
        if (current_user_can('edit_post', $post_id) === false || check_admin_referer( 'save_metadata_promoted-' . $post_id, 'save_metadata_promoted') === false) {
            return;
        }

        $this->disable_current_promotion($post_id);

        foreach($this->fields as $field) {
            if (array_key_exists($field, $_POST)) {
                update_post_meta(
                    $post_id,
                    "_$field",
                    sanitize_text_field($_POST[$field])
                );
            } else {
                update_post_meta(
                    $post_id,
                    "_$field",
                    0
                );
            }
        }
    }

    /**
     * Disable current promotion.
     *
     * Checks if the given ID is currently promoted.
     * If not, then disables the current promotion.
     *
     * @param integer $compare_id
     * @return void
     */
    public static function disable_current_promotion(int $compare_id) {
        $data_getter = \PromotedProduct\DataGetter::getInstance();
        $currently_promoted = $data_getter->get_currently_promoted_product_id();
        if ($currently_promoted && $currently_promoted != $compare_id) {
            update_post_meta($currently_promoted, '_promoted_product_is_promoted', array());
        }
    }
}