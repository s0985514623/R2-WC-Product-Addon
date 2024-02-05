<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

// use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

class ProductAddon
{
    public function __construct()
    {
        \add_filter('woocommerce_product_data_tabs', [ $this, 'product_settings_tabs' ]);
        \add_action('woocommerce_product_data_panels', [ $this, 'render_app' ]);
        \add_action('woocommerce_before_add_to_cart_button', [ $this, 'render_product' ]);
        //在購物車計算前，設定商品價格
        \add_action('woocommerce_before_calculate_totals', [ $this, 'set_custom_cart_item_price' ]);
        //ELEMENTOR 計算價格前用到的Filter
        \add_filter('woocommerce_cart_item_price', [ $this, 'ele_custom_cart_item_price' ], 20, 3);
    }
    function ele_custom_cart_item_price($price, $cart_item, $cart_item_key)
    {
        if (array_key_exists('product_addon_price', $cart_item)) {
            $price = 'NT$' . number_format(floatval($cart_item[ 'product_addon_price' ]));
        }
        return $price;
    }
    function set_custom_cart_item_price($cart_object)
    {
        // wp_send_json($cart_object);
        foreach ($cart_object->get_cart() as $item) {
            if (array_key_exists('product_addon_price', $item)) {
                $item[ 'data' ]->set_price($item[ 'product_addon_price' ]);
            }
        }
    }
    public function product_settings_tabs($tabs)
    {
        $tabs[ 'r2_wcpa' ] = array(
            'label'    => '加價購商品',
            'target'   => 'r2_wcpa',
            'class'    => array('show_if_simple', 'show_if_variable', 'hide_if_bundle'),
            'priority' => 60,
        );
        return $tabs;
    }
    //後台頁面渲染
    public function render_app()
    {
        echo '<div id="' . Bootstrap::RENDER_ID_1 . '" class="panel woocommerce_options_panel hidden">';
        echo '</div>';
    }
    //前台商品頁渲染
    function render_product()
    {
        //載入js
        \wp_enqueue_script('add_to_cart', Bootstrap::get_plugin_url() . '/inc/custom/js/add_to_cart.js', array('jquery'), false, true);

        global $product;
        $product_type        = $product->get_type();
        $product_meta_string = \get_post_meta($product->get_id(), Bootstrap::SNAKE . '_meta', true);
        $product_meta        = Functions::json_parse($product_meta_string, [  ], true);
        $handled_shop_meta   = $this->handleShopMeta($product_meta);
        //post_meta 不為空時
        if (!empty($handled_shop_meta) && ($product_type == 'simple' || $product_type == 'variable')) {
            foreach ($handled_shop_meta as $meta) {
                //get product
                $product_addon_id = $meta[ 'productId' ];
                /**
                 * @var \WC_Product_Variable $product_addon =>改善vscode會提示 defined錯誤
                 */
                $product_addon      = \wc_get_product($product_addon_id);
                $product_addon_type = $product_addon->get_type();
                switch ($product_addon_type) {
                    case 'variable':
                        \load_template(Bootstrap::get_plugin_dir() . '/inc/templates/single-product/variable.php', false, [
                            'product'             => $product_addon,
                            'meta'                => $meta,
                            'variationAttributes' => $product_addon->get_variation_attributes(false),
                         ]);
                        break;
                    case 'simple':
                        \load_template(Bootstrap::get_plugin_dir() . '/inc/templates/single-product/simple.php', false, [
                            'product' => $product_addon,
                            'meta'    => $meta,
                         ]);
                        break;
                    default:
                        \load_template(Bootstrap::get_plugin_dir() . '/inc/templates/single-product/simple.php', false, [
                            'product' => $product_addon,
                            'meta'    => $meta,
                         ]);
                        break;
                }
            }
        }
    }
    /**
     * 檢查 shop_meta 裡面的商品與 woocommerce 裡面的商品是否 type 一致
     * 如果不一致，就更新 shop_meta 裡面的 data
     *
     * @param array $shop_meta
     * @return array
     */
    private function handleShopMeta(array $shop_meta): array
    {
        $need_update = false;
        // 檢查當前的 shop_meta 裡面的商品與 woocommerce 裡面的商品是否 type 一致
        foreach ($shop_meta as $key => $meta) {
            $meta_product_type = $meta[ 'productType' ] ?? '';
            if (empty($meta_product_type)) {
                // 如果舊版本用戶沒有存到 productType，就判斷給個預設值
                $is_variable_product = !empty($meta[ 'variations' ]);
                $meta_product_type   = $is_variable_product ? 'variable' : 'simple';
            }

            $product_id = $meta[ 'productId' ];
            /**
             * @var \WC_Product_Variable $product =>改善vscode會提示 defined錯誤
             */
            $product      = \wc_get_product($product_id);
            $product_type = $product->get_type();

            if ($meta_product_type !== $product_type) {
                $need_update = true;
                // 如果不一致，就更新 shop_meta 裡面的 productType
                $shop_meta[ $key ][ 'productType' ] = $product_type;

                if ($product_type === 'simple') {
                    $shop_meta[ $key ] = [
                        "productId"    => $product_id,
                        "productType"  => $product_type,
                        "regularPrice" => $product->get_regular_price(),
                        "salesPrice"   => $product->get_sale_price(),
                     ];
                }

                if ($product_type === 'variable') {
                    $variations          = $product->get_available_variations();
                    $formattedVariations = [  ];
                    foreach ($variations as $key => $variation) {
                        $formattedVariations[  ] = [
                            "variationId"  => $variation[ 'variation_id' ],
                            "regularPrice" => $variation[ 'display_regular_price' ],
                            "salesPrice"   => $variation[ 'display_price' ],

                         ];
                    }

                    $shop_meta[ $key ] = [
                        "productId"   => $product_id,
                        "productType" => $product_type,
                        "variations"  => $formattedVariations,
                     ];
                }
            }
        }

        if ($need_update) {
            // 更新 post_meta
            global $post;
            \update_post_meta($post->ID, Bootstrap::SNAKE . '_meta', \wp_json_encode($shop_meta));
        }

        return $shop_meta;
    }
}
