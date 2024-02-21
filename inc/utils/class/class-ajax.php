<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

class Ajax
{

    const GET_POST_META_ACTION      = 'addon_handle_get_post_meta';
    const UPDATE_POST_META_ACTION   = 'addon_handle_update_post_meta';
    const addon_handle_add_to_cart  = 'addon_handle_add_to_cart';
    const addon_handle_delete_cart  = 'addon_handle_delete_cart';
    const custom_handle_add_to_cart = 'custom_handle_add_to_cart';
    const handle_update_cart_data   = 'handle_update_cart_data';
    function __construct()
    {
        foreach ([ self::GET_POST_META_ACTION, self::UPDATE_POST_META_ACTION, self::addon_handle_add_to_cart, self::addon_handle_delete_cart, self::custom_handle_add_to_cart, self::handle_update_cart_data ] as $action) {
            \add_action('wp_ajax_' . $action, [ $this, $action . '_callback' ]);
            \add_action('wp_ajax_nopriv_' . $action, [ $this, $action . '_callback' ]);
        }
    }

    public function addon_handle_get_post_meta_callback()
    {
        // Security check
        \check_ajax_referer(Bootstrap::KEBAB, 'nonce');
        $post_id  = \sanitize_text_field($_POST[ 'post_id' ] ?? '');
        $meta_key = \sanitize_text_field($_POST[ 'meta_key' ] ?? '');

        if (empty($post_id)) {
            return;
        }

        $post_meta = empty($meta_key)?\get_post_meta($post_id) : \get_post_meta($post_id, $meta_key, true);

        $return = array(
            'message' => 'success',
            'data'    => [
                'post_meta' => $post_meta,
             ],
        );

        \wp_send_json($return);
        \wp_die();
    }

    public function addon_handle_update_post_meta_callback()
    {
        // Security check
        \check_ajax_referer(Bootstrap::KEBAB, 'nonce');
        $post_id    = \sanitize_text_field($_POST[ 'post_id' ] ?? '');
        $meta_key   = \sanitize_text_field($_POST[ 'meta_key' ] ?? '');
        $meta_value = \sanitize_text_field($_POST[ 'meta_value' ] ?? '');

        if (empty($post_id) || empty($meta_key)) {
            return;
        }
        //更新post_meta
        $update_result = \update_post_meta($post_id, $meta_key, $meta_value);

        $return = array(
            'message' => 'success',
            'data'    => [
                'update_result' => $update_result,
                'meta_value'    => $meta_value,
             ],
        );

        \wp_send_json($return);

        \wp_die();
    }
    public function addon_handle_add_to_cart_callback()
    {
        // Security check
        \check_ajax_referer(Bootstrap::KEBAB, 'nonce', false);
        $parent_product_id = filter_var($_POST[ 'parent_product_id' ], FILTER_SANITIZE_NUMBER_INT);
        $parent_product_id = filter_var($parent_product_id, FILTER_VALIDATE_INT) ? $parent_product_id : 0;
        $product_id        = filter_var($_POST[ 'product_id' ], FILTER_SANITIZE_NUMBER_INT);
        $product_id        = filter_var($product_id, FILTER_VALIDATE_INT) ? $product_id : 0;
        $quantity          = filter_var($_POST[ 'quantity' ], FILTER_SANITIZE_NUMBER_INT);
        $quantity          = filter_var($quantity, FILTER_VALIDATE_INT) ? $quantity : 1;
        $variation_id      = filter_var($_POST[ 'variable_id' ], FILTER_SANITIZE_NUMBER_INT);
        $variation_id      = filter_var($variation_id, FILTER_VALIDATE_INT) ? $variation_id : 0;

        //後端取得金額
        $product_meta_string = \get_post_meta($parent_product_id, Bootstrap::SNAKE . '_meta', true);
        $product_meta        = Functions::json_parse($product_meta_string, [  ], true);
        $productArray        = array_filter($product_meta, function ($v) use ($product_id) {
            return $v[ 'productId' ] == $product_id;
        });
        $productArray  = reset($productArray);
        $productType   = $productArray[ 'productType' ] ?? '';
        $product_price = null;
        if ($productType == 'variable') {
            $product_price = array_filter($productArray[ 'variations' ], function ($v) use ($variation_id) {
                return $v[ 'variationId' ] == $variation_id;
            }) ?? null;
            $product_price = reset($product_price)[ 'salesPrice' ];
        } else if ($productType == 'simple') {
            $product_price = $productArray[ 'salesPrice' ] ?? null;
        }

        if (empty($product_id) || !isset($product_price)) {
            $return = array(
                'message' => 'error',
                'data'    => [
                    'productType'   => $productArray,
                    'product_id'    => $product_id,
                    'quantity'      => $quantity,
                    'variation_id'  => $variation_id,
                    'product_price' => $product_price,
                    'variable'      => $_POST,
                    'empty'         => empty($variation_id),
                 ],
            );
            \wp_send_json($return);
        }

        // WC_Cart::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
        $cart_item_key = \WC()->cart->add_to_cart($product_id, $quantity, $variation_id, array(), array('product_price' => $product_price, 'parent_product_id' => $parent_product_id));
        if ($cart_item_key) {
            // WooCommerce的函數，用於獲取更新後的fragments和cart_hash
            \WC_AJAX::get_refreshed_fragments();
            //debug
            //     $return = array(
            //         'message' => 'success',
            //         'data'    => [
            //                 'product_id'          => $product_id,
            //                 'quantity'            => $quantity,
            //                 'variation_id'        => $variation_id,
            //                 'product_price' => $product_price,
            //                 'variable'            => $_POST,
            //                 'empty'               => empty($variation_id),
            //          ],
            // );
            // \wp_send_json($return);
        } else {
            $return = array(
                'message' => 'error',
                'data'    => [
                    'product_id'    => $product_id,
                    'quantity'      => $quantity,
                    'variation_id'  => $variation_id,
                    'product_price' => $product_price,
                    'variable'      => $_POST,
                    'empty'         => empty($variation_id),
                 ],
            );
            \wp_send_json($return);
        }
        die();
    }
    public function addon_handle_delete_cart_callback()
    {
        // Security check
        \check_ajax_referer(Bootstrap::KEBAB, 'nonce', false);
        // The $_REQUEST contains all the data sent via ajax
        if (isset($_POST)) {
            $parentsId = $_POST[ 'parentsId' ];
            // 移除現有對應的綑綁商品
            if ($parentsId) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                    if ($cart_item[ 'product_id' ] == $parentsId) {
                        WC()->cart->remove_cart_item($cart_item_key);
                    }
                }
                $return = array(
                    'message' => 'success',
                    'data'    => [
                        'parentsId' => $parentsId,
                        'variable'  => $_POST,
                     ],
                );
                wp_send_json($return);
            }
        } else {
            $return = array(
                'message' => 'error',
                'data'    => [
                    '$_POST' => $_POST,
                 ],
            );
            wp_send_json($return);
        }

        // Always die in functions echoing ajax content
        die();
    }
    public function custom_handle_add_to_cart_callback()
    {
        // Security check
        \check_ajax_referer(Bootstrap::KEBAB, 'nonce', false);
        //如果未登入就加入購物車則檔下來強迫用Google登入
        if (!\is_user_logged_in()) {
            ob_start();
            include_once Bootstrap::get_plugin_dir() . 'inc/templates/googleLogin.php';
            $loginPup = ob_get_clean();
            status_header(400);
            wp_send_json($loginPup);
            wp_die();
        }
        $_POST_items = $_POST[ 'items' ];
        if (!empty($_POST_items)) {
            foreach ($_POST_items as $value) {
                $parent_product_id = filter_var($value[ 'parent_product_id' ] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $parent_product_id = filter_var($parent_product_id, FILTER_VALIDATE_INT) ? $parent_product_id : 0;
                $product_id        = filter_var($value[ 'product_id' ], FILTER_SANITIZE_NUMBER_INT);
                $product_id        = filter_var($product_id, FILTER_VALIDATE_INT) ? $product_id : 0;
                $quantity          = filter_var($value[ 'quantity' ], FILTER_SANITIZE_NUMBER_INT);
                $quantity          = filter_var($quantity, FILTER_VALIDATE_INT) ? $quantity : 1;
                $variation_id      = filter_var($value[ 'variable_id' ] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $variation_id      = filter_var($variation_id, FILTER_VALIDATE_INT) ? $variation_id : 0;

                //後端取得金額
                $product_price = null;
                //如果是加購商品
                if ($parent_product_id !== 0) {
                    $product_meta_string = \get_post_meta($parent_product_id, Bootstrap::SNAKE . '_meta', true);
                    $product_meta        = Functions::json_parse($product_meta_string, [  ], true);
                    $productArray        = array_filter($product_meta, function ($v) use ($product_id) {
                        return $v[ 'productId' ] == $product_id;
                    });
                    $productArray = reset($productArray);
                    $productType  = $productArray[ 'productType' ] ?? '';
                    if ($productType == 'variable') {
                        $product_price = array_filter($productArray[ 'variations' ], function ($v) use ($variation_id) {
                            return $v[ 'variationId' ] == $variation_id;
                        }) ?? null;
                        $product_price = reset($product_price)[ 'salesPrice' ];
                    } else if ($productType == 'simple') {
                        $product_price = $productArray[ 'salesPrice' ] ?? null;
                    }
                } else {
                    //如果是主商品=>這個price在加入購物車時其實用不到，只是為了讓程式不報錯
                    $product_price = \wc_get_product($product_id)->get_price();
                }

                if (empty($product_id) || !isset($product_price)) {
                    $return = array(
                        'message' => 'empty product_id or not isset product_price',
                        'data'    => [
                            'productType'   => $productArray,
                            'product_id'    => $product_id,
                            'quantity'      => $quantity,
                            'variation_id'  => $variation_id,
                            'product_price' => $product_price,
                            '$_POST'        => $_POST,
                         ],
                    );
                    \wp_send_json($return);
                }
                //加入購物車=>如果是加購商品，則需要傳入cart_item_data，否則不用
                // WC_Cart::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
                if ($parent_product_id !== 0) {
                    $cart_item_key = \WC()->cart->add_to_cart($product_id, $quantity, $variation_id, array(), array('product_addon_price' => $product_price, 'parent_product_id' => $parent_product_id));
                } else {
                    //跳過只能購買一個的商品
                    $max_purchase_quantity = \wc_get_product($product_id)->get_max_purchase_quantity();
                    if ($max_purchase_quantity == 1) {
                        //獲取購物車中的商品數量
                        $cart          = WC()->cart->get_cart();
                        $cart_quantity = 0;
                        foreach ($cart as $cart_item) {
                            if ($cart_item[ 'product_id' ] == $product_id) {
                                $cart_quantity += $cart_item[ 'quantity' ];
                            }
                        }
                        if ($cart_quantity < $max_purchase_quantity) {
                            $cart_item_key = \WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
                        }
                    } else {
                        $cart_item_key = \WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
                    }
                }

                if (!$cart_item_key) {
                    $return = array(
                        'message' => 'add_to_cart error',
                        'data'    => [
                            'product_id'    => $product_id,
                            'quantity'      => $quantity,
                            'variation_id'  => $variation_id,
                            'product_price' => $product_price,
                            '$_POST'        => $_POST,
                            'empty'         => empty($variation_id),
                         ],
                    );
                    \wp_send_json($return);
                }
            }
            // WooCommerce的函數，用於獲取更新後的fragments和cart_hash
            \WC_AJAX::get_refreshed_fragments();
        } else {
            $return = array(
                'message' => 'empty $_POST_items',
                'data'    => [
                    '$_POST' => $_POST,
                 ],
            );
            \wp_send_json($return);
        }
        die();
    }
    /**
     * 當購物車頁更新時重新取得並賦值Bootstrap::SNAKE . '_cart_data'資料
     */
    public function handle_update_cart_data_callback()
    {
        global $woocommerce;
        //取得購物車中的商品
        $cart_items = $woocommerce->cart->get_cart();
        $cartData   = [  ];

        foreach ($cart_items as $product) {
            //取得產品ID
            $productID = $product[ 'data' ]->get_id();
            //取得post_meta資料
            $product_meta_string = \get_post_meta($productID, Bootstrap::SNAKE . '_meta', true);
            $product_meta        = Functions::json_parse($product_meta_string, [  ], true);
            $handled_shop_meta   = Functions::handleShopMeta($product_meta);

            //post_meta 不為空時且product id 不存在cart_items中
            if (!empty($handled_shop_meta)) {
                foreach ($handled_shop_meta as $meta) {
                    //get product
                    $product_addon = Functions::get_product_data($meta);
                    $cartData[  ]  = [
                        'product' => $product_addon,
                        'meta'    => $meta,
                     ];
                }
            }
        }
        //需要排除的商品=>如果購物車裡面有加購商品,則不顯示加購商品
        $filterProducts = Functions::filter_same_elements($cartData, $cart_items);
        wp_send_json($filterProducts);
        wp_die();
    }
}
