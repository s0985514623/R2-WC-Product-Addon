<?php
/**
 * AJAX class
 *
 * @package J7\WpMyAppPlugin\MyApp\Inc
 */

declare(strict_types=1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

/**
 * AJAX class
 */
class Ajax {
	const GET_POST_META_ACTION    = 'addon_handle_get_post_meta';
	const UPDATE_POST_META_ACTION = 'addon_handle_update_post_meta';
	// 只在cart_page使用.
	const ADDON_HANDLE_ADD_TO_CART = 'addon_handle_add_to_cart';
	const ADDON_HANDLE_DELETE_CART = 'addon_handle_delete_cart';
	// 通用型.
	const CUSTOM_HANDLE_ADD_TO_CART = 'custom_handle_add_to_cart';
	const HANDLE_UPDATE_CART_DATA   = 'handle_update_cart_data';

	/**
	 * Construct function
	 */
	public function __construct() {
		foreach ( array( self::GET_POST_META_ACTION, self::UPDATE_POST_META_ACTION, self::ADDON_HANDLE_ADD_TO_CART, self::ADDON_HANDLE_DELETE_CART, self::CUSTOM_HANDLE_ADD_TO_CART, self::HANDLE_UPDATE_CART_DATA ) as $action ) {
			\add_action( 'wp_ajax_' . $action, array( $this, $action . '_callback' ) );
			\add_action( 'wp_ajax_nopriv_' . $action, array( $this, $action . '_callback' ) );
		}
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function addon_handle_get_post_meta_callback() {
		// Security check.
		\check_ajax_referer( Bootstrap::KEBAB, 'nonce' );
		$post_id  = \sanitize_text_field( wp_unslash( $_POST['post_id'] ?? '' ) );
		$meta_key = \sanitize_text_field( wp_unslash( $_POST['meta_key'] ?? '' ) );

		if ( empty( $post_id ) ) {
			return;
		}

		$post_meta = empty( $meta_key ) ? \get_post_meta( $post_id ) : \get_post_meta( $post_id, $meta_key, true );

		$return = array(
			'message' => 'success',
			'data'    => array(
				'post_meta' => $post_meta,
			),
		);

		\wp_send_json( $return );
		\wp_die();
	}
	/**
	 * 更新post_meta
	 */
	public function addon_handle_update_post_meta_callback() {
		// Security check.
		\check_ajax_referer( Bootstrap::KEBAB, 'nonce' );
		$post_id    = \sanitize_text_field( wp_unslash( $_POST['post_id'] ?? '' ) );
		$meta_key   = \sanitize_text_field( wp_unslash( $_POST['meta_key'] ?? '' ) );
		$meta_value = \sanitize_text_field( wp_unslash( $_POST['meta_value'] ?? '' ) );

		if ( empty( $post_id ) || empty( $meta_key ) ) {
			return;
		}
		// 更新post_meta.
		$update_result = \update_post_meta( $post_id, $meta_key, $meta_value );

		$return = array(
			'message' => 'success',
			'data'    => array(
				'update_result' => $update_result,
				'meta_value'    => $meta_value,
			),
		);

		\wp_send_json( $return );

		\wp_die();
	}
	/**
	 * AJAX function 只在cart_page使用
	 *
	 * @return void
	 */
	public function addon_handle_add_to_cart_callback() {
		// Security check.
		\check_ajax_referer( Bootstrap::KEBAB, 'nonce', false );
		if ( isset( $_POST['parent_product_id'] ) && isset( $_POST['product_id'] ) && isset( $_POST['quantity'] ) && isset( $_POST['variable_id'] ) ) {
			// The $_REQUEST contains all the data sent via ajax.
			$parent_product_id = filter_var( wp_unslash( $_POST['parent_product_id'] ), FILTER_SANITIZE_NUMBER_INT );
			$parent_product_id = filter_var( $parent_product_id, FILTER_VALIDATE_INT ) ? $parent_product_id : 0;
			$product_id        = filter_var( wp_unslash( $_POST['product_id'] ), FILTER_SANITIZE_NUMBER_INT );
			$product_id        = filter_var( $product_id, FILTER_VALIDATE_INT ) ? $product_id : 0;
			$quantity          = filter_var( wp_unslash( $_POST['quantity'] ), FILTER_SANITIZE_NUMBER_INT );
			$quantity          = filter_var( $quantity, FILTER_VALIDATE_INT ) ? $quantity : 1;
			$variation_id      = filter_var( wp_unslash( $_POST['variable_id'] ), FILTER_SANITIZE_NUMBER_INT );
			$variation_id      = filter_var( $variation_id, FILTER_VALIDATE_INT ) ? $variation_id : 0;

			// 後端取得金額.
			$product_meta_string = \get_post_meta( $parent_product_id, Bootstrap::SNAKE . '_meta', true );
			$product_meta        = Functions::json_parse( $product_meta_string, array(), true );
			$product_array       = array_filter(
				$product_meta,
				function ( $v ) use ( $product_id ) {
					return $v['productId'] === $product_id;
				}
			);
			$product_array       = reset( $product_array );
			$product_type        = $product_array['productType'] ?? '';
			$product_price       = null;
			if ( 'variable' === $product_type ) {
				$product_price = array_filter(
					$product_array['variations'],
					function ( $v ) use ( $variation_id ) {
						return $v['variationId'] === $variation_id;
					}
				) ?? null;
				$product_price = reset( $product_price )['salesPrice'];
			} elseif ( 'simple' === $product_type ) {
				$product_price = $product_array['salesPrice'] ?? null;
			}

			if ( empty( $product_id ) || ! isset( $product_price ) ) {
				$return = array(
					'message' => 'error',
					'data'    => array(
						'product_type'  => $product_array,
						'product_id'    => $product_id,
						'quantity'      => $quantity,
						'variation_id'  => $variation_id,
						'product_price' => $product_price,
						'variable'      => $_POST,
						'empty'         => empty( $variation_id ),
					),
				);
				\wp_send_json( $return );
			}

			// WC_Cart::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );.
			$cart_item_key = \WC()->cart->add_to_cart(
				$product_id,
				$quantity,
				$variation_id,
				array(),
				array(
					'product_price'     => $product_price,
					'parent_product_id' => $parent_product_id,
				)
			);
			if ( $cart_item_key ) {
				// WooCommerce的函數，用於獲取更新後的fragments和cart_hash.
				\WC_AJAX::get_refreshed_fragments();
				// Debug.
				// $return = array(
				// 'message' => 'success',
				// 'data'    => [
				// 'product_id'          => $product_id,
				// 'quantity'            => $quantity,
				// 'variation_id'        => $variation_id,
				// 'product_price' => $product_price,
				// 'variable'            => $_POST,
				// 'empty'               => empty($variation_id),
				// ],
				// );
				// \wp_send_json($return);.
			} else {
				$return = array(
					'message' => 'error',
					'data'    => array(
						'product_id'    => $product_id,
						'quantity'      => $quantity,
						'variation_id'  => $variation_id,
						'product_price' => $product_price,
						'variable'      => $_POST,
						'empty'         => empty( $variation_id ),
					),
				);
				\wp_send_json( $return );
			}
			die();
		}
	}
	/**
	 * AJAX  delete_cart function
	 *
	 * @return void
	 */
	public function addon_handle_delete_cart_callback() {
		// Security check.
		\check_ajax_referer( Bootstrap::KEBAB, 'nonce', false );
		// The $_REQUEST contains all the data sent via ajax.
		if ( isset( $_POST ) && isset( $_POST['parentsId'] ) ) {
			$parents_id = sanitize_text_field( wp_unslash( ( $_POST['parentsId'] ) ) );
			// 移除現有對應的綑綁商品.
			if ( $parents_id ) {
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					if ( $cart_item['product_id'] === $parents_id ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
				$return = array(
					'message' => 'success',
					'data'    => array(
						'parentsId' => $parents_id,
						'variable'  => $_POST,
					),
				);
				wp_send_json( $return );
			}
		} else {
			$return = array(
				'message' => 'error',
				'data'    => array(
					'$_POST' => $_POST,
				),
			);
			wp_send_json( $return );
		}

		// Always die in functions echoing ajax content.
		die();
	}
	/**
	 * AJAX function
	 *
	 * @return void
	 */
	public function custom_handle_add_to_cart_callback() {
		// Security check.
		\check_ajax_referer( Bootstrap::KEBAB, 'nonce', false );
		// 如果未登入就加入購物車則檔下來強迫用Google登入.
		if ( ! \is_user_logged_in() ) {
			ob_start();
			\load_template(
				Bootstrap::get_plugin_dir() . 'inc/templates/googleLogin.php',
				false,
				array(
					'current_page_url' => isset( $_POST['current_page_url'] ) ? sanitize_text_field( wp_unslash( $_POST['current_page_url'] ) ) : '',
				)
			);
			$login_pup = ob_get_clean();
			status_header( 400 );
			wp_send_json( $login_pup );
			wp_die();
		}

		// 取得購物車中的商品.
		if ( isset( $_POST['items'] ) ) {
			// 這裡的$_POST['items']已經由Functions::recursive_sanitize_text_fields處理過了所以手動消除警告.
			// phpcs:ignore
			$post_items = Functions::recursive_sanitize_text_fields( wp_unslash( $_POST['items'] ) );
			foreach ( $post_items as $value ) {
				$parent_product_id = filter_var( $value['parent_product_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
				$parent_product_id = filter_var( $parent_product_id, FILTER_VALIDATE_INT ) ? $parent_product_id : 0;
				$product_id        = filter_var( $value['product_id'], FILTER_SANITIZE_NUMBER_INT );
				$product_id        = filter_var( $product_id, FILTER_VALIDATE_INT ) ? $product_id : 0;
				$quantity          = filter_var( $value['quantity'], FILTER_SANITIZE_NUMBER_INT );
				$quantity          = filter_var( $quantity, FILTER_VALIDATE_INT ) ? $quantity : 1;
				$variation_id      = filter_var( $value['variable_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT );
				$variation_id      = filter_var( $variation_id, FILTER_VALIDATE_INT ) ? $variation_id : 0;

				// 後端取得金額.
				$product_price = null;
				// 如果是加購商品.
				if ( 0 !== $parent_product_id ) {
					$product_meta_string = \get_post_meta( $parent_product_id, Bootstrap::SNAKE . '_meta', true );
					$product_meta        = Functions::json_parse( $product_meta_string, array(), true );
					$product_array       = array_filter(
						$product_meta,
						function ( $v ) use ( $product_id ) {
							return $v['productId'] == $product_id;
						}
					);
						$product_array   = reset( $product_array );
						$product_type    = $product_array['productType'] ?? '';
					if ( 'variable' === $product_type ) {
						$product_price     = array_filter(
							$product_array['variations'],
							function ( $v ) use ( $variation_id ) {
								return $v['variationId'] == $variation_id;
							}
						) ?? null;
							$product_price = reset( $product_price )['salesPrice'];
					} elseif ( 'simple' === $product_type ) {
						$product_price = $product_array['salesPrice'] ?? null;
					}
				} else {
					// 如果是主商品=>這個price在加入購物車時其實用不到，只是為了讓程式不報錯.
					$product_price = \wc_get_product( $product_id )->get_price();
				}
				// 當產品id為空或產品價格未設定時，直接跳出.
				if ( empty( $product_id ) || ! isset( $product_price ) ) {
					$return = array(
						'message' => 'empty product_id or not isset product_price',
						'data'    => array(
							'product_type'  => $product_array,
							'product_id'    => $product_id,
							'quantity'      => $quantity,
							'variation_id'  => $variation_id,
							'product_price' => $product_price,
							'$_POST'        => $_POST,
						),
					);
					\wp_send_json( $return );
				}
				// 當購物車驗證不成功直接跳出.
				$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
				if ( ! $passed_validation ) {
					$return = array(
						'message' => 'passed_validation error',
						'data'    => array(
							'passed_validation' => $passed_validation,
							'product_id'        => $product_id,
							'quantity'          => $quantity,
							'variation_id'      => $variation_id,
							'product_price'     => $product_price,
							'$_POST'            => $_POST,
							'empty'             => empty( $variation_id ),
						),
					);
					\wp_send_json( $return );
				}
				// 加入購物車=>　如果是加購商品則需要傳入　$cart_item_data，否則不用.
				// 公式：WC_Cart::add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );.
				if ( 0 !== $parent_product_id ) {
					$cart_item_key = \WC()->cart->add_to_cart(
						$product_id,
						$quantity,
						$variation_id,
						array(),
						array(
							'product_addon_price' => $product_price,
							'parent_product_id'   => $parent_product_id,
						)
					);
				} else {
					// 跳過只能購買一個的商品.
					$max_purchase_quantity = \wc_get_product( $product_id )->get_max_purchase_quantity();
					if ( 1 === $max_purchase_quantity ) {
						$cart_item_key = \WC()->cart->add_to_cart( $product_id, 1, $variation_id );
						// // 獲取購物車中的商品數量.
						// $cart          = WC()->cart->get_cart();
						// $cart_quantity = 0;
						// foreach ( $cart as $cart_item ) {
						// if ( $cart_item['product_id'] == $product_id ) {
						// $cart_quantity += $cart_item['quantity'];
						// }
						// }
						// if ( $cart_quantity < $max_purchase_quantity ) {
						// $cart_item_key = \WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
						// }
					} else {
						$cart_item_key = \WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
					}
				}

				if ( ! $cart_item_key ) {
					$return = array(
						'message' => 'add_to_cart error',
						'data'    => array(
							'product_id'    => $product_id,
							'quantity'      => $quantity,
							'variation_id'  => $variation_id,
							'product_price' => $product_price,
							'$_POST'        => $_POST,
							'empty'         => empty( $variation_id ),
						),
					);
					\wp_send_json( $return );
				}
			}

			// 當可以成功加入購物車時，如果有啟用r2-member-filter 套件,則調用CronNew::set_mail.
			// if ( class_exists( 'J7\WP_REACT_PLUGIN\React\Admin\CronNew' ) ) {
			// $user_id = \get_current_user_id();
			// \J7\WP_REACT_PLUGIN\React\Admin\CronNew::set_mail( $user_id, $product_id );
			// }.

			// WooCommerce的函數，用於獲取更新後的fragments和cart_hash.
			\WC_AJAX::get_refreshed_fragments();
		} else {
			$return = array(
				'message' => 'empty $post_items',
				'data'    => array(
					'$_POST' => $_POST,
				),
			);
			\wp_send_json( $return );
		}
		die();
	}
	/**
	 * 當購物車頁更新時重新取得並賦值Bootstrap::SNAKE . '_cart_data'資料
	 */
	public function handle_update_cart_data_callback() {
		global $woocommerce;
		// 取得購物車中的商品.
		$cart_items = $woocommerce->cart->get_cart();
		$cart_data  = array();

		foreach ( $cart_items as $product ) {
			// 取得產品ID.
			$product_id = $product['data']->get_id();
			// 取得post_meta資料.
			$product_meta_string = \get_post_meta( $product_id, Bootstrap::SNAKE . '_meta', true );
			$product_meta        = Functions::json_parse( $product_meta_string, array(), true );
			$handled_shop_meta   = Functions::handleShopMeta( $product_meta );

			// post_meta 不為空時且product id 不存在cart_items中.
			if ( ! empty( $handled_shop_meta ) ) {
				foreach ( $handled_shop_meta as $meta ) {
					// get product.
					$product_addon = Functions::get_product_data( $meta );
					$cart_data[]   = array(
						'product' => $product_addon,
						'meta'    => $meta,
					);
				}
			}
		}
		// 需要排除的商品=>如果購物車裡面有加購商品,則不顯示加購商品.
		$filter_products = Functions::filter_same_elements( $cart_data, $cart_items );
		wp_send_json( $filter_products );
		wp_die();
	}
}
