<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use function _\find;
use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

final class Functions {

	/**
	 * Register CPT
	 *
	 * @param string $label - the name of CPT
	 * @param array  $meta_keys - the meta keys of CPT ex ['meta', 'settings']
	 * @return void
	 */
	public static function register_cpt( $label ): void {

		$kebab = str_replace( ' ', '-', strtolower( $label ) );
		$snake = str_replace( ' ', '_', strtolower( $label ) );

		$labels = array(
			'name'                     => \esc_html__( $label, Bootstrap::KEBAB ),
			'singular_name'            => \esc_html__( $label, Bootstrap::KEBAB ),
			'add_new'                  => \esc_html__( 'Add new', Bootstrap::KEBAB ),
			'add_new_item'             => \esc_html__( 'Add new item', Bootstrap::KEBAB ),
			'edit_item'                => \esc_html__( 'Edit', Bootstrap::KEBAB ),
			'new_item'                 => \esc_html__( 'New', Bootstrap::KEBAB ),
			'view_item'                => \esc_html__( 'View', Bootstrap::KEBAB ),
			'view_items'               => \esc_html__( 'View', Bootstrap::KEBAB ),
			'search_items'             => \esc_html__( 'Search ' . $label, Bootstrap::KEBAB ),
			'not_found'                => \esc_html__( 'Not Found', Bootstrap::KEBAB ),
			'not_found_in_trash'       => \esc_html__( 'Not found in trash', Bootstrap::KEBAB ),
			'parent_item_colon'        => \esc_html__( 'Parent item', Bootstrap::KEBAB ),
			'all_items'                => \esc_html__( 'All', Bootstrap::KEBAB ),
			'archives'                 => \esc_html__( $label . ' archives', Bootstrap::KEBAB ),
			'attributes'               => \esc_html__( $label . ' attributes', Bootstrap::KEBAB ),
			'insert_into_item'         => \esc_html__( 'Insert to this ' . $label, Bootstrap::KEBAB ),
			'uploaded_to_this_item'    => \esc_html__( 'Uploaded to this ' . $label, Bootstrap::KEBAB ),
			'featured_image'           => \esc_html__( 'Featured image', Bootstrap::KEBAB ),
			'set_featured_image'       => \esc_html__( 'Set featured image', Bootstrap::KEBAB ),
			'remove_featured_image'    => \esc_html__( 'Remove featured image', Bootstrap::KEBAB ),
			'use_featured_image'       => \esc_html__( 'Use featured image', Bootstrap::KEBAB ),
			'menu_name'                => \esc_html__( $label, Bootstrap::KEBAB ),
			'filter_items_list'        => \esc_html__( 'Filter ' . $label . ' list', Bootstrap::KEBAB ),
			'filter_by_date'           => \esc_html__( 'Filter by date', Bootstrap::KEBAB ),
			'items_list_navigation'    => \esc_html__( $label . ' list navigation', Bootstrap::KEBAB ),
			'items_list'               => \esc_html__( $label . ' list', Bootstrap::KEBAB ),
			'item_published'           => \esc_html__( $label . ' published', Bootstrap::KEBAB ),
			'item_published_privately' => \esc_html__( $label . ' published privately', Bootstrap::KEBAB ),
			'item_reverted_to_draft'   => \esc_html__( $label . ' reverted to draft', Bootstrap::KEBAB ),
			'item_scheduled'           => \esc_html__( $label . ' scheduled', Bootstrap::KEBAB ),
			'item_updated'             => \esc_html__( $label . ' updated', Bootstrap::KEBAB ),
		);
		$args   = array(
			'label'                 => \esc_html__( $label, Bootstrap::KEBAB ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'hierarchical'          => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => false,
			'show_in_rest'          => true,
			'query_var'             => false,
			'can_export'            => true,
			'delete_with_user'      => true,
			'has_archive'           => false,
			'rest_base'             => '',
			'show_in_menu'          => true,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-store',
			'capability_type'       => 'post',
			'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author' ),
			'taxonomies'            => array(),
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'               => array(
				'with_front' => true,
			),
		);

		\register_post_type( $kebab, $args );
	}
	public static function add_metabox( array $args ): void {
		\add_meta_box(
			$args['id'],
			__( $args['label'], Bootstrap::KEBAB ),
			array( __CLASS__, 'render_metabox' ),
			$args['post_type'],
			'advanced',
			'default',
			array( 'id' => $args['id'] )
		);
	}

	/**
	 * Renders the meta box.
	 */
	public static function render_metabox( $post, $metabox ): void {
		echo "<div id='{$metabox[ 'args' ][ 'id' ]}'></div>";
	}

	/**
	 * JSON Parse
	 */
	public static function json_parse( $stringfy, $default = array(), $associative = null ) {
		$out_put = '';
		try {
			$out_put = json_decode( str_replace( '\\', '', $stringfy ), $associative ) ?? $default;
		} catch ( \Throwable $th ) {
			$out_put = $default;
		} finally {
			return $out_put;
		}
	}
	public static function get_products_info( $post_id ): array {
		$shop_meta_string = \get_post_meta( $post_id, Bootstrap::SNAKE . '_meta', true ) ?? '[]';

		try {
			$shop_meta = self::json_parse( $shop_meta_string, array(), true );
		} catch ( \Throwable $th ) {
			$shop_meta = array();
		}

		// 原本的寫法
		// $products = array_map(__NAMESPACE__ . "\get_product_data", $shop_meta);

		// 新的寫法 但用static可以考慮實際調用的類別=>這個會比較好,因為考慮到繼承
		$products = array_map( array( self::class, 'get_product_data' ), $shop_meta );
		// 用self會依照實際調用的類別,如果方法是在父類別被定義,則會使用父類別的方法
		// $products = array_map(array('self', 'get_product_data'), $shop_meta);

		$products_info = array(
			'products' => $products,
			'meta'     => $shop_meta,
		);

		return $products_info;
	}
	public static function get_product_data( array $meta ): array {
		$meta = (array) $meta ?? array();
		if ( empty( $meta['productId'] ) ) {
			return array();
		}
		/**
		 * @var \WC_Product_Variable $product =>改善vscode會提示 defined錯誤
		 */
		$product          = \wc_get_product( $meta['productId'] );
		$feature_image_id = $product->get_image_id();
		$attachment_ids   = array( $feature_image_id, ...$product->get_gallery_image_ids() );
		$images           = array();
		foreach ( $attachment_ids as $attachment_id ) {
			$images[] = \wp_get_attachment_url( $attachment_id );
		}
		// format data
		$product_data                         = array();
		$product_data['productObj']           = $product;
		$product_data['id']                   = $meta['productId'];
		$product_data['type']                 = $product->get_type();
		$product_data['name']                 = $product->get_name();
		$product_data['images']               = $images;
		$product_data['is_sold_individually'] = $product->is_sold_individually();
		$product_data['is_in_stock']          = $product->is_in_stock();
		$product_data['is_purchasable']       = $product->is_purchasable();
		$product_data['total_sales']          = $product->get_total_sales();

		if ( 'simple' === $product->get_type() ) {
			$product_data['regularPrice'] = $meta['regularPrice'];
			$product_data['salesPrice']   = $meta['salesPrice'];
		}
		if ( 'variable' === $product->get_type() && ! empty( $meta['variations'] ) ) {
			$variation_meta                       = $meta['variations']; // Undefined array key "variations"
			$product_data['variations']           = array();
			$product_data['variation_attributes'] = $product->get_variation_attributes();

			foreach ( $product->get_available_variations() as $key => $variation ) {
				$variation_id                       = $variation['variation_id'];
				$variation_product                  = \wc_get_product( $variation_id );
				$theMeta                            = find( $variation_meta, array( 'variationId' => $variation_id ) );
				$product_data['variations'][ $key ] = $variation;
				$product_data['variations'][ $key ]['attributes']   = self::format_attributes( $variation['attributes'] );
				$product_data['variations'][ $key ]['regularPrice'] = $theMeta['regularPrice'];
				$product_data['variations'][ $key ]['salesPrice']   = $theMeta['salesPrice'];
				$product_data['variations'][ $key ]['stock']        = array(
					'manageStock'   => $variation_product->get_manage_stock(),
					'stockQuantity' => $variation_product->get_stock_quantity(),
					'stockStatus'   => $variation_product->get_stock_status(),
				);
			}
		}

		return $product_data;
	}
	public static function format_attributes( $attributes ) {
		try {
			// code...
			$formatAttributes = new \stdClass();
			foreach ( $attributes as $key => $value ) {
				// 檢查是否以 "attribute_" 開頭
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					// 去除 "attribute_" 前綴
					$key = substr( $key, strlen( 'attribute_' ) );
				}

				// 檢查是否以 "pa_" 開頭
				if ( strpos( $key, 'pa_' ) !== 0 ) {
					// 如果不是，進行 urldecode 轉換
					$key = urldecode( $key );
				}
				$formatAttributes->$key = urldecode( $value );
			}
			return $formatAttributes;
		} catch ( \Throwable $th ) {
			// throw $th;
			return $attributes;
		}
	}
	/**
	 * 檢查 shop_meta 裡面的商品與 woocommerce 裡面的商品是否 type 一致
	 * 如果不一致，就更新 shop_meta 裡面的 data
	 *
	 * @param array $shop_meta
	 * @return array
	 */
	public static function handleShopMeta( array $shop_meta ): array {
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
	/**
	 * 對陣列進行 篩選 / 去重 / 取價格低
	 *
	 * @param array $arr1 需要被篩選的陣列
	 * @param array $arr2 用來比較的陣列
	 * @return array 回傳經過篩選的arr1陣列
	 */
	public static function filter_same_elements( array $arr1, array $arr2 = array() ): array {
		// 去重 及 取價格低
		$filteredProducts = array();

		// 判斷arr1 中的[ 'meta' ][ 'productId' ]是否具有相同的id值 ,如果有則取價格低的
		foreach ( $arr1 as $product ) {
			// 簡易商品處理方式
			if ( $product['meta']['productType'] === 'simple' ) {
				$productId  = $product['meta']['productId'];
				$salesPrice = $product['meta']['salesPrice'];

				// 檢查是否已經有相同的productId
				if ( isset( $filteredProducts[ $productId ] ) ) {
					// 比較salesPrice，保留較低的那個
					if ( $filteredProducts[ $productId ]['meta']['salesPrice'] > $salesPrice ) {
						$filteredProducts[ $productId ] = $product;
					}
				} else {
					// 如果沒有相同的productId，直接添加到結果數組中
					$filteredProducts[ $productId ] = $product;
				}
			}
			// 可變商品處理方式
			elseif ( $product['meta']['productType'] === 'variable' ) {
				$productId = $product['meta']['productId'];
				// 檢查是否已經有相同的productId
				if ( isset( $filteredProducts[ $productId ] ) ) {
					// 循環變體
					foreach ( $product['meta']['variations'] as $value ) {
						// 檢查是否已經有相同的variationId
						if ( isset( $filteredProducts[ $productId ]['meta']['variations'][ $value['variationId'] ] ) ) {
							// 比較salesPrice，保留較低的那個
							if ( $filteredProducts[ $productId ]['meta']['variations'][ $value['variationId'] ]['salesPrice'] > $value['salesPrice'] ) {
								$filteredProducts[ $productId ]['meta']['parentProductId']                                      = $product['meta']['parentProductId'];
								$filteredProducts[ $productId ]['meta']['variations'][ $value['variationId'] ]                  = $value;
								$filteredProducts[ $productId ]['product']['variations'][ $value['variationId'] ]['salesPrice'] = $value['salesPrice'];
							}
						} else {
							echo '這邊不會執行';
							// 如果沒有相同的productId，直接添加到結果數組中
							$filteredProducts[ $productId ]['meta']['variations'][ $value['variationId'] ] = $value;
						}
					}
				} else {
					// 篩選原始陣列中的元素排除variations key 值
					$filteredProducts[ $productId ] = $product;
					unset( $filteredProducts[ $productId ]['meta']['variations'] );
					unset( $filteredProducts[ $productId ]['product']['variations'] );
					// 重新賦予具有variationId的key值
					foreach ( $product['meta']['variations'] as $value ) {
						$filteredProducts[ $productId ]['meta']['variations'][ $value['variationId'] ] = $value;
					}
					foreach ( $product['product']['variations'] as $value ) {
						$filteredProducts[ $productId ]['product']['variations'][ $value['variation_id'] ] = $value;
					}
				}
			}
		}
		// 篩選=>從B陣列中提取id值
		$idsInB           = array_column( $arr2, 'product_id' );
		$filteredProducts = array_filter(
			$filteredProducts,
			function ( $v ) use ( $idsInB ) {
				return ! in_array( $v['meta']['productId'], $idsInB );
			}
		);
		return $filteredProducts;
	}
	/**
	 * Sanitize Array function
	 *
	 * @param mixed $value The value to be sanitized.
	 * @return mixed The sanitized value.
	 */
	public static function recursive_sanitize_text_fields( $value ) {
		if ( is_array( $value ) ) {
			// 如果是数组，递归调用当前函数.
			return array_map( array( self::class, 'recursive_sanitize_text_fields' ), $value );
		} elseif ( is_string( $value ) ) {
			// 如果是字符串，使用sanitize_text_field清理.
			return sanitize_text_field( $value );
		}
		// 如果是其他类型，直接返回原值（不处理）.
		return $value;
	}
}
