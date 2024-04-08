<?php

declare(strict_types=1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

// use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

final class ProductAddon {


	public function __construct() {
		// 後台設定選單渲染
		\add_filter( 'woocommerce_product_data_tabs', array( $this, 'product_settings_tabs' ) );
		// 後台頁面渲染
		\add_action( 'woocommerce_product_data_panels', array( $this, 'render_app' ) );
		// 前台商品頁渲染
		\add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'render_product' ) );
		// 在購物車計算前，設定商品價格
		\add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_custom_cart_item_price' ) );
		// ELEMENTOR 計算價格前用到的Filter
		\add_filter( 'woocommerce_cart_item_price', array( $this, 'ele_custom_cart_item_price' ), 20, 3 );

		// 購物車驗證=如果主商品不在,加購商品要移除
		\add_action( 'woocommerce_cart_updated', array( $this, 'woocommerce_cart_updated' ) );
		// 結帳頁下方的加價購商品,要加上若有相同的加購商品出現則以價格低者出現
		\add_action( 'woocommerce_after_cart_table', array( $this, 'render_cart' ) );
		// 載入的js將type改成module
		\add_filter(
			'script_loader_tag',
			function ( $tag, $handle, $src ) {
				if ( 'add_to_cart' === $handle || 'cart_add_to_cart' === $handle ) {
					$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
				}
				return $tag;
			},
			10,
			3
		);
	}
	function ele_custom_cart_item_price( $price, $cart_item, $cart_item_key ) {
		// error_log(print_r($cart_item, true));
		if ( array_key_exists( 'product_addon_price', $cart_item ) ) {
			$price = 'NT$' . number_format( floatval( $cart_item['product_addon_price'] ) );
		}
		return $price;
	}
	function set_custom_cart_item_price( $cart_object ) {
		// wp_send_json($cart_object);
		foreach ( $cart_object->get_cart() as $item ) {
			if ( array_key_exists( 'product_addon_price', $item ) ) {
				$item['data']->set_price( $item['product_addon_price'] );
			}
		}
	}
	public function product_settings_tabs( $tabs ) {
		$tabs['r2_wcpa'] = array(
			'label'    => '加價購商品',
			'target'   => 'r2_wcpa',
			'class'    => array( 'show_if_simple', 'show_if_variable', 'hide_if_bundle' ),
			'priority' => 60,
		);
		return $tabs;
	}
	/**
	 * 後台頁面渲染
	 *
	 * @return void
	 */
	public function render_app() {
		echo '<div id="' . Bootstrap::RENDER_ID_1 . '" class="panel woocommerce_options_panel hidden">';
		echo '</div>';
	}
	/**
	 * 前台商品頁渲染
	 *
	 * @return void
	 */
	function render_product() {
		// 載入js
		\wp_enqueue_script( 'add_to_cart', Bootstrap::get_plugin_url() . '/inc/custom/js/add_to_cart.js', array( 'jquery' ), false, true );

		global $product;
		$product_type        = $product->get_type();
		$product_meta_string = \get_post_meta( $product->get_id(), Bootstrap::SNAKE . '_meta', true );
		$product_meta        = Functions::json_parse( $product_meta_string, array(), true );
		$handled_shop_meta   = Functions::handleShopMeta( $product_meta );
		// post_meta 不為空時
		if ( ! empty( $handled_shop_meta ) && ( $product_type == 'simple' || $product_type == 'variable' ) ) {
			echo '<div class="productAddonContainer w-full border border-solid border-[#ddd] my-4">';
			echo '<div class="productAddonTitle text-xl text-[#4562A8] font-bold p-4 bg-[#f9f9f9]">以優惠價加購商品</div>';
			echo '<div class="productAddonList p-4 pb-0">';
			foreach ( $handled_shop_meta as $meta ) {
				// get product
				$product_addon_id = $meta['productId'];
				/**
				 * @var \WC_Product_Variable $product_addon =>改善vscode會提示 defined錯誤
				 */
				$product_addon      = \wc_get_product( $product_addon_id );
				$product_addon_type = $product_addon->get_type();
				switch ( $product_addon_type ) {
					case 'variable':
						\load_template(
							Bootstrap::get_plugin_dir() . '/inc/templates/single-product/variable.php',
							false,
							array(
								'product'             => $product_addon,
								'meta'                => $meta,
								'variationAttributes' => $product_addon->get_variation_attributes( false ),
							)
						);
						break;
					case 'simple':
						\load_template(
							Bootstrap::get_plugin_dir() . '/inc/templates/single-product/simple.php',
							false,
							array(
								'product' => $product_addon,
								'meta'    => $meta,
							)
						);
						break;
					default:
						\load_template(
							Bootstrap::get_plugin_dir() . '/inc/templates/single-product/simple.php',
							false,
							array(
								'product' => $product_addon,
								'meta'    => $meta,
							)
						);
						break;
				}
			}
			echo '</div>';
			echo '</div>';
		}
	}
	/**
	 * 商品頁渲染
	 *
	 * @return void
	 */
	function render_cart() {
		// 載入js
		\wp_enqueue_script( 'cart_add_to_cart', Bootstrap::get_plugin_url() . '/inc/custom/js/cart_add_to_cart.js', array( 'jquery' ), false, true );
		global $woocommerce;
		// 取得購物車中的商品
		$cart_items = $woocommerce->cart->get_cart();
		$cartData   = array();

		foreach ( $cart_items as $product ) {
			// 取得產品ID
			$productID = $product['product_id'];
			// 取得post_meta資料
			$product_meta_string = \get_post_meta( $productID, Bootstrap::SNAKE . '_meta', true );
			// echo '$product_meta_string: ' . $product_meta_string . '<br>';
			$product_meta      = Functions::json_parse( $product_meta_string, array(), true );
			$handled_shop_meta = Functions::handleShopMeta( $product_meta );

			// post_meta 不為空時且product id 不存在cart_items中
			if ( ! empty( $handled_shop_meta ) ) {
				foreach ( $handled_shop_meta as $meta ) {
					// get product
					$product_addon = Functions::get_product_data( $meta );
					$cartData[]    = array(
						'product' => $product_addon,
						'meta'    => $meta,
					);
				}
			}
		}
		// 需要排除的商品=>如果購物車裡面有加購商品,則不顯示加購商品
		$filterProducts = Functions::filter_same_elements( $cartData, $cart_items );
		// 取出所有加購商品，包含以在購物車中的
		\wp_localize_script( Bootstrap::KEBAB, Bootstrap::SNAKE . '_cart_data', $filterProducts );
		// 渲染畫面
		// 默認不執行
		$executeCode = false;
		foreach ( $filterProducts as $item ) {
			if ( ! empty( $item['meta'] ) ) {
				$executeCode = true; // 如果有一个非空的bundleProduct，设置为true
				break; // 可以提前结束循环，因为不需要继续检查
			}
		}
		if ( $executeCode ) {
			// 將資料傳入js
			\load_template(
				Bootstrap::get_plugin_dir() . '/inc/templates/cart/index.php',
				false,
				array(
					'filterProducts' => $filterProducts,
				)
			);
		}
	}

	/**
	 * 購物車驗證=如果主商品不在,加購商品要移除
	 * 判斷購物車中的加價商品cart_item_data是否符合條件=>是否具有parent_product_id值
	 * 如果有parent_product_id值,則判斷購物車中是否有parent_product_id值的商品
	 * 如果沒有,則移除購物車中的加價商品
	 */
	public function woocommerce_cart_updated() {
		global $woocommerce;
		// 取得購物車中的商品
		$cart_items = $woocommerce->cart->get_cart();
		// error_log(print_r($cart_items, true));
		if ( ! empty( $cart_items ) ) {
			// 循環購物車中商品
			foreach ( $cart_items as $cart_item_key => $cart_item ) {
				// 如果購物車商品中有parent_product_id值
				if ( isset( $cart_item['parent_product_id'] ) ) {
					// 取得parent_product_id值
					$parent_product_id = $cart_item['parent_product_id'];
					// 取得購物車中的商品id陣列
					$ids_cart_items = array_column( $cart_items, 'product_id' );
					// 如果購物車中沒有parent_product_id值的商品,則移除購物車中的加價商品
					if ( ! in_array( $parent_product_id, $ids_cart_items ) ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
		}
	}
	/**
	 * (已經移動到Function)
	 * 檢查 shop_meta 裡面的商品與 woocommerce 裡面的商品是否 type 一致
	 * 如果不一致，就更新 shop_meta 裡面的 data
	 *
	 * @param array $shop_meta
	 * @return array
	 */
	private function handleShopMeta( array $shop_meta ): array {
		$need_update = false;
		// 檢查當前的 shop_meta 裡面的商品與 woocommerce 裡面的商品是否 type 一致
		foreach ( $shop_meta as $key => $meta ) {
			$meta_product_type = $meta['productType'] ?? '';
			if ( empty( $meta_product_type ) ) {
				// 如果舊版本用戶沒有存到 productType，就判斷給個預設值
				$is_variable_product = ! empty( $meta['variations'] );
				$meta_product_type   = $is_variable_product ? 'variable' : 'simple';
			}

			$product_id = $meta['productId'];
			/**
			 * @var \WC_Product_Variable $product =>改善vscode會提示 defined錯誤
			 */
			$product      = \wc_get_product( $product_id );
			$product_type = $product->get_type();

			if ( $meta_product_type !== $product_type ) {
				$need_update = true;
				// 如果不一致，就更新 shop_meta 裡面的 productType
				$shop_meta[ $key ]['productType'] = $product_type;

				if ( $product_type === 'simple' ) {
					$shop_meta[ $key ] = array(
						'productId'    => $product_id,
						'productType'  => $product_type,
						'regularPrice' => $product->get_regular_price(),
						'salesPrice'   => $product->get_sale_price(),
					);
				}

				if ( $product_type === 'variable' ) {
					$variations          = $product->get_available_variations();
					$formattedVariations = array();
					foreach ( $variations as $key => $variation ) {
						$formattedVariations[] = array(
							'variationId'  => $variation['variation_id'],
							'regularPrice' => $variation['display_regular_price'],
							'salesPrice'   => $variation['display_price'],
						);
					}

					$shop_meta[ $key ] = array(
						'productId'   => $product_id,
						'productType' => $product_type,
						'variations'  => $formattedVariations,
					);
				}
			}
		}

		if ( $need_update ) {
			// 更新 post_meta
			global $post;
			\update_post_meta( $post->ID, Bootstrap::SNAKE . '_meta', \wp_json_encode( $shop_meta ) );
		}

		return $shop_meta;
	}
}
