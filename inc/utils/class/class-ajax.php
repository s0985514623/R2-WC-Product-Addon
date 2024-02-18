<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

class Ajax
{

    const GET_POST_META_ACTION     = 'addon_handle_get_post_meta';
    const UPDATE_POST_META_ACTION  = 'addon_handle_update_post_meta';
    const addon_handle_add_to_cart = 'addon_handle_add_to_cart';

    function __construct()
    {
        foreach ([ self::GET_POST_META_ACTION, self::UPDATE_POST_META_ACTION, self::addon_handle_add_to_cart ] as $action) {
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
        $productArray        = reset($productArray);
        $productType         = $productArray[ 'productType' ] ?? '';
        $product_addon_price = null;
        if ($productType == 'variable') {
            $product_addon_price = array_filter($productArray[ 'variations' ], function ($v) use ($variation_id) {
                return $v[ 'variationId' ] == $variation_id;
            }) ?? null;
            $product_addon_price = reset($product_addon_price)[ 'salesPrice' ];
        } else if ($productType == 'simple') {
            $product_addon_price = $productArray[ 'salesPrice' ] ?? null;
        }

        if (empty($product_id) || !isset($product_addon_price)) {
            $return = array(
                'message' => 'error',
                'data'    => [
                    'productType'         => $productArray,
                    'product_id'          => $product_id,
                    'quantity'            => $quantity,
                    'variation_id'        => $variation_id,
                    'product_addon_price' => $product_addon_price,
                    'variable'            => $_POST,
                    'empty'               => empty($variation_id),
                 ],
            );
            \wp_send_json($return);
        }

        // WC_Cart::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );
        $cart_item_key = \WC()->cart->add_to_cart($product_id, $quantity, $variation_id, array(), array('product_addon_price' => $product_addon_price, 'parent_product_id' => $parent_product_id));
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
            //                 'product_addon_price' => $product_addon_price,
            //                 'variable'            => $_POST,
            //                 'empty'               => empty($variation_id),
            //          ],
            // );
            // \wp_send_json($return);
        } else {
            $return = array(
                'message' => 'error',
                'data'    => [
                    'product_id'          => $product_id,
                    'quantity'            => $quantity,
                    'variation_id'        => $variation_id,
                    'product_addon_price' => $product_addon_price,
                    'variable'            => $_POST,
                    'empty'               => empty($variation_id),
                 ],
            );
            \wp_send_json($return);
        }
        die();
    }
}
