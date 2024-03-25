<?php
namespace J7\WpMyAppPlugin\MyApp\Inc;

// 1.插入新的action checkbox
// 2.寫 css 與jQuery 做到顯示與排版與隱藏
// $(".bundled_product_optional_checkbox").contents().filter(function () {
// return this.nodeType === 3; // 过滤出文本节点
// }).remove();
// 3.jQuery模擬新的input checkbox點擊等於被隱藏的input checkbox點擊

// 載入新的input checkbox
\add_action(
	'woocommerce_bundled_item_details',
	function () {
		echo '<label class="customLabel">
	<input class="customInput" type="checkbox"  value="">
</label>';
	}
);
// 判斷購物車中的商品是否為綑綁商品
// 1.從cart_items中取出['stamp']的值=>在綑綁商品中的產品 有ID 及 折扣金額
// 2.透過ID取得綑綁商品的資料 標題/價格/折扣多少錢
// 3.CTA 前往購買綑綁商品
// 4.增加購物車頁面加購縮圖
/**5.加入購物車連結思路:
 * 移除現有對應的綑綁商品
 * 從Edit連結篩選出bundle_selected_optional_{id}=yes&bundle_quantity_{id}=1等資料其餘移除
 * 使用/?add-to-cart&bundle_selected_optional_{id}=yes&bundle_quantity_{id}=1的方式重新加入
 * 如果有出現商品代表尚未加入購物車
 * 則取出他的bundled_item_id加入在bundle_selected_optional_{id}
 * 將其添加在/?add-to-cart網址後面
 */

add_action(
	'woocommerce_after_cart_table',
	function () {
		// 秀出被加入購物車中的商品
		$cart_items = WC()->cart->cart_contents;
		$cartData   = array();
		foreach ( $cart_items as $product ) {
			// 如果有綑綁商品且不是綑綁商品的子商品
			if ( $product['stamp'] !== null && $product['bundled_by'] == null ) {
				// 取得商品連結
				$productLink = $product['data']->get_permalink();
				// 取得產品ID
				$productID = $product['data']->get_id();
				// 優先取得已加入購物車的綑綁商品
				$selected = '';
				foreach ( $product['stamp'] as $index => $bundleProduct ) {
					if ( $bundleProduct['optional_selected'] === 'yes' ) {
						$selected .= 'bundle_selected_optional_' . $index . '=yes&bundle_quantity_' . $index . '=1&';
					}
				}
				$myObject                = new \stdClass();
				$myObject->productLink   = $productLink;
				$myObject->productID     = $productID;
				$myObject->selected      = $selected;
				$myObject->bundleProduct = array();
				// 再取得未被加入購物車的綑綁商品
				foreach ( $product['stamp'] as $index => $bundleProduct ) {
					if ( $bundleProduct['optional_selected'] === 'no' ) {
						// 取得bundled_item_id
						$bundled_item_id = $index;
						// 取得加入購物車連結
						$add_to_cart = 'bundle_selected_optional_' . $bundled_item_id . '=yes&bundle_quantity_' . $bundled_item_id . '=1&';
						// 取得綑綁商品的資料
						$bundleProductData = wc_get_product( $bundleProduct['product_id'] );
						// 取得綑綁商品的縮圖
						$bundleProductImg = $bundleProductData->get_image();
						// 取得綑綁商品的標題
						$bundleProductTitle = $bundleProductData->get_title();
						// 取得綑綁商品的價格
						$bundleProductPrice = $bundleProductData->get_price();
						// 取得綑綁商品的折扣金額
						$bundleProductDiscount = $bundleProduct['discount'];
						// 取得綑綁商品折扣後的金額
						$bundleProductDiscountPrice              = $bundleProductPrice - ( $bundleProductPrice * ( $bundleProductDiscount / 100 ) );
						$ProductData                             = new \stdClass();
						$ProductData->add_to_cart                = $add_to_cart;
						$ProductData->bundleProductTitle         = $bundleProductTitle;
						$ProductData->bundleProductPrice         = $bundleProductPrice;
						$ProductData->bundleProductDiscountPrice = $bundleProductDiscountPrice;
						$ProductData->bundleProductImg           = $bundleProductImg;
						$myObject->bundleProduct[]               = $ProductData;
					}
					$cartData[ $product['product_id'] ] = $myObject;
				}
			}
		}
		// 渲染畫面
		// 默認不執行
		$executeCode = false;
		foreach ( $cartData as $item ) {
			if ( ! empty( $item->bundleProduct ) ) {
				$executeCode = true; // 如果有一个非空的bundleProduct，设置为true
				break; // 可以提前结束循环，因为不需要继续检查
			}
		}
		if ( $executeCode ) {
			?>
<style>
/* 解不知為何會出現的P標籤BUG */
#promotion p {
	display: none;
}
</style>
<div
	class="bundleProductWrap w-full bg-[#F6F6F6] border border-solid border-[#EDEDED] px-[15px] py-[10px]">
	<h3 class="goBuyTitle md:text-xl text-sm">尚有更多精彩優惠等著你！目前未享用：</h3>
</div>
<div id="promotion"
	class="w-full grid grid-cols-1 md:grid-cols-3 text-[#333333] font-semibold border border-t-0 border-solid border-[#EDEDED] mb-5">
			<?php
			foreach ( $cartData as $item ) {
				if ( ! empty( $item->bundleProduct ) ) {
					foreach ( $item->bundleProduct as $bundleProduct ) {
						?>
	<div class="bundleProductItem py-5 px-[15px] grid grid-cols-5">
		<div class="bundleProductImg aspect-square w-full col-span-2 md:col-span-3">
						<?php echo $bundleProduct->bundleProductImg; ?>
		</div>
		<div class="bundleProductInfo px-[15px] flex flex-col justify-between col-span-3 md:col-span-2">
			<div class="flex flex-col gap-2">
				<span
					class="bundleProductTitle md:text-base text-sm"><?php echo $bundleProduct->bundleProductTitle; ?></span>
				<div class="text-sm">
					<span
						class="bundleProductPrice tracking-normal text-[#4562A8] opacity-50 line-through mr-[3px]">NT$<?php echo number_format( $bundleProduct->bundleProductPrice ); ?></span>
					<span
						class="bundleProductDiscountPrice tracking-normal text-[#4562A8]">NT$<?php echo number_format( $bundleProduct->bundleProductDiscountPrice ); ?></span>
				</div>
			</div>
			<div
				class="bundleAddToCart flex gap-1 w-fit whitespace-nowrap items-center cursor-pointer rounded text-center py-1.5 px-2.5 bg-black  border border-black border-solid hover:bg-[#dddddd]">
				<a onclick="clickHandler(event)" class="!text-white flex gap-1 items-center"
					data-parentsId="<?php echo $item->productID; ?>"
					data-href="<?php echo $item->productLink . '?add-to-cart=' . $item->productID . '&' . $item->selected . $bundleProduct->add_to_cart . ''; ?>"><svg
						class="loading hidden animate-spin fill-white" xmlns="http://www.w3.org/2000/svg"
						height="1em" viewBox="0 0 512 512">
						<path
							d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z">
						</path>
					</svg>加入購物車</a>
			</div>
		</div>
	</div>
						<?php
					}
				}
			}
			?>
</div>
			<?php
		}
	}
);
/**
 * AJAX 先將現有商品移除再加入新的商品
 */
//
// 註冊JS
function produce_bundles_enqueue() {
	// Enqueue javascript on the frontend.
	\wp_enqueue_script(
		'produce-bundles-ajax-script',
		Bootstrap::get_plugin_url() . 'inc/custom/bundles/js/produce-bundles.js',
		array( 'jquery' )
	);
	// The wp_localize_script allows us to output the ajax_url path for our script to use.
	\wp_localize_script(
		'produce-bundles-ajax-script',
		'produce_bundles_ajax_obj',
		array(
			'ajaxUrl' => \admin_url( 'admin-ajax.php' ),
			'nonce'   => \wp_create_nonce( 'bundles-nonce' ),
		)
	);
}
\add_action( 'wp_enqueue_scripts', '\J7\WpMyAppPlugin\MyApp\Inc\produce_bundles_enqueue' );
// AJAX接收
function produce_bundles_ajax_request() {
	$nonce = $_REQUEST['nonce'];
	if ( ! wp_verify_nonce( $nonce, 'bundles-nonce' ) ) {
		die( 'Nonce value cannot be verified.' );
	}
	// The $_REQUEST contains all the data sent via ajax
	if ( isset( $_REQUEST ) ) {
		$parentsId = $_REQUEST['parentsId'];
		// 移除現有對應的綑綁商品
		if ( $parentsId ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $cart_item['product_id'] == $parentsId ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
			wp_send_json( 'ok' );
		}
	}
	// Always die in functions echoing ajax content
	die();
}

\add_action( 'wp_ajax_produce_bundles', '\J7\WpMyAppPlugin\MyApp\Inc\produce_bundles_ajax_request' );
// If you wanted to also use the function for non-logged in users (in a theme for example)
\add_action( 'wp_ajax_nopriv_produce_bundles', '\J7\WpMyAppPlugin\MyApp\Inc\produce_bundles_ajax_request' );

\add_action(
	'wp_footer',
	function () {
		// 在此寫入jQuery 跟css
		?>
	<style>
/* 商品頁 */
.bundled_item_optional {
	padding-left: 30px;
}

.bundled_item_optional .customLabel {
	position: absolute;
	left: 0;
	top: 20px;
}

.bundled_product_title .item_title {
	font-size: 20px;
	color: #4562A8;
}

.bundled_item_optional .price {
	color: #4562A8 !important;
}

.bundled_item_optional .price ins,
.bundle_wrap .price ins {
	text-decoration: none !important;
}

.bundle_wrap .price {
	text-align: center;
}

.bundle_wrap .woocommerce-Price-amount {
	font-size: 28px;
	color: #4562A8;
	font-weight: 700;
	letter-spacing: 1.4px;
}

.bundle_button button {
	width: 100%;
}

.bundled_product_checkbox {
	display: none;
}

/* 选中状态的复选框 */
.bundled_item_optional:has([type="checkbox"]:checked) .bundled_product_images {
	border: 5px solid #4562A8;
}

@media screen and (max-width: 769px) {
	.bundled_item_optional {
		padding-left: 20px;
		display: flex;
	}

	.bundled_item_optional .bundled_product_images {
		width: 70px !important;
		height: 70px !important;
		aspect-ratio: 1/1;
		margin-right: 10px !important;
	}
}

/* 購物車頁的Edit按鈕隱藏 */
.bundle_table_item .product-name a {
	display: none;
}

.edit_bundle_in_cart_text.edit_in_cart_text:not(:first-child) {
	display: none;
}

/*購物車頁手機版tr高度*/
.woocommerce td.product-quantity {
	min-height: 40px !important;
}
</style>
<script>
(function($) {
	$(function() {
		//購物車頁
		if ($('.bundleProductItem').length == 0) {
			$('.bundleProductWrap').hide();
			$('.promotion').hide()
		}
		const EditLink = $('.edit_bundle_in_cart_text.edit_in_cart_text').attr('href');
		const goBuyLink = $('.goBuyLink').attr('href', EditLink);

		//商品頁=>隱藏Add to 文字
		$(".bundled_product_optional_checkbox").contents().filter(function() {
			return this.nodeType === 3; // 过滤出文本节点
		}).remove();

		//當原始input改變狀態時,同步新增的input狀態=>從購物車回來跳轉的商品會自動勾選原始input
		$("input.bundled_product_checkbox").change(function() {
			let isChecked = $(this).is(":checked");
			$(this).closest('label.bundled_product_optional_checkbox').closest('.details')
				.siblings('.customLabel').find('input.customInput').prop("checked",
					isChecked);
		});
		//當新增的input改變狀態時,同步原始的input狀態
		$("input.customInput").change(function() {
			// let isChecked = $(this).is(":checked");
			$(this).closest('label').siblings('.details').find(
				'label.bundled_product_optional_checkbox').find(
				'input.bundled_product_checkbox').click();

		});


		//當有綑綁商品時隱藏原始價格
		if ($('.bundle_form').length > 0) {
			$('.elementor-widget-woocommerce-product-price').hide();
		}


	})
})(jQuery)
</script>
		<?php
	}
);
