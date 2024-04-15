<?php
namespace PromotedProduct;

/**
 * Renderer class to handle front-end HTML rendering
 *
 * @package PromotedProduct
 */
class Renderer {
    /**
     * Disallow instatiating.
     */
    private function __construct() {}

    /**
     * Renders the front-end code.
     *
     * Gets the data of product and settings, then displays our front-end.
     *
     * @param array{promoted_title_settings:string,product_title:string,product_link:string,background_color:string,text_color:string} $data Data of product and settings.
     * @return void
     */
    public static function render_frontend($data) {
        $link_text = __('Check it out!', 'promoted-product');
        $html = <<<HTML
            <div class="promoted-product-render" style="background-color: {$data['background_color']}">
                <p class="promoted-product-text" style="color: {$data['text_color']}">
                    {$data['promoted_title_settings']}: {$data['product_title']}
                </p>
                <a class="promoted-product-link" href="{$data['product_link']}" style="color: {$data['text_color']}">
                    $link_text
                </a>
            </div>
HTML;
        echo $html;
    }
}