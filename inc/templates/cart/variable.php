<?php
use J7\WpMyAppPlugin\MyApp\Inc\Functions;
// 解構賦值 $args
[
	'product'             => $product,
	'meta'                => $meta,
	'variationAttributes' => $variationAttributes,
] = $args;
// 解構賦值 $meta
[
	'parentProductId' => $parent_product_id,
	'productId'       => $product_addon_id,
	'variations'      => $variations,
] = $meta;

// 以防如果沒有變體屬性，賦予空陣列
$variationAttributes = $variationAttributes ?? array();
$img_id              = $product->get_image_id();
$img_src             = \wp_get_attachment_image_src( $img_id, array( 450, 450 ) );
$name                = $product->get_name();
$permalink           = get_permalink( $product->get_id() ) . '?parentProductId=' . $parent_product_id;


$price_arr         = array();
$regular_price_arr = array();
$variation_arr     = array();
$variations        = $variations ?? array();

foreach ( $variations as $variation ) {
	if ( empty( (int) $variation['salesPrice'] ) ) {
		$price_arr[] = (int) $variation['regularPrice'];
	} else {
		$price_arr[] = (int) $variation['salesPrice'];
	}
	$regular_price_arr[] = (int) $variation['regularPrice'];
}

$filtered_price_arr = array_filter(
	$price_arr,
	function ( $price ) {
		return ! empty( $price );
	}
);

$max               = empty( $filtered_price_arr ) ? '' : max( $filtered_price_arr );
$min               = empty( $filtered_price_arr ) ? '' : min( $filtered_price_arr );
$max_regular_price = empty( $regular_price_arr ) ? '' : max( $regular_price_arr );

// if (empty($regular_price) && empty($sales_price)) {
// 商品類型轉換時，才會發生這種情況
// $regular_price = $product->get_regular_price();
// $sales_price   = $product->get_sale_price();
// }

$product_status     = $product->get_status();
$default_attributes = $product->get_default_attributes();
$variable_id        = '';
$salesPrice         = 0;
if ( ! empty( $default_attributes ) ) {
	// 獲取所有變體
	$get_variations = $product->get_available_variations();

	foreach ( $get_variations as $variation ) {
		$match = true; // 假設找到匹配
		foreach ( $default_attributes as $attribute => $value ) {
			if ( $variation['attributes'][ 'attribute_' . $attribute ] != $value ) {
				$match = false;
				break;
			}
		}
		if ( $match ) {
			// 找到預設變體，輸出其 ID
			$variable_id      = $variation['variation_id'];
			$variationsFilter = array_filter(
				$variations,
				function ( $v ) use ( $variable_id ) {
					return $v['variationId'] == $variable_id;
				}
			);
			$salesPrice       = reset( $variationsFilter )['salesPrice'];
			break;
		}
	}
	$default_attributes = Functions::format_attributes( $default_attributes );
}

if ( $product_status === 'publish' ) :
	?>
<div class="variableProduct productAddon py-5 px-[15px] grid grid-cols-5"
	data-product_addon_id="<?php echo $product_addon_id; ?>" data-variable_id="<?php echo $variable_id; ?>"
	data-parent_product_id="<?php echo $parent_product_id; ?>">
	<div class="productAddonImg aspect-square w-full col-span-2">
		<img src="<?php echo $img_src[0]; ?>" alt="<?php echo $name; ?>">
	</div>
	<div class="productAddonInfo px-[15px] flex flex-col justify-between col-span-3 ">
		<div class="flex flex-col gap-2">
			<div class="productAddonTitle md:text-base text-sm"><a href="<?php echo $permalink; ?>"><?php echo $name; ?></a>
			</div>
			<table class="font-bold text-base">
				<tbody>
					<?php foreach ( $variationAttributes as $label => $valueArray ) : ?>
					<tr>
						<th class="border-0 hidden">
							<label class="text-nowrap" for="<?php echo $label; ?>"><?php echo \wc_attribute_label( $label ); ?></label>
						</th>
						<td class="border-0 p-2">
							<select class="" data-label_key="<?php echo $label; ?>">
								<option value="">請選取一個選項</option>
								<?php foreach ( $valueArray as $value ) : ?>
								<option value="<?php echo urldecode( $value ); ?>"
									<?php echo ! empty( $default_attributes ) && $default_attributes->$label == urldecode( $value ) ? 'selected' : 'none'; ?>>
									<?php echo urldecode( $value ); ?></option>

								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div class="clearLink hidden font-bold text-sm"
				data-product_addon_id="<?php echo $product_addon_id; ?>"><a href="javascript:void(0);"
					class="!underline">清除</a>
			</div>
			<div class="productAddonPrice">
				<div class="flex flex-wrap text-sm text-[#4562A8] font-bold gap-2 ">
					<?php if ( $max === $min && ! empty( $min ) ) : ?>
						<?php if ( $max === $max_regular_price ) : ?>
					<span class="mb-0 mt-1 salesPrice tracking-normal"
						data-original_price="<?php echo number_format( $min ); ?>">NT$
							<?php echo number_format( $min ); ?>
					</span>
					<?php else : ?>
					<span class="mb-0 mt-1 opacity-50 regularPrice tracking-normal"
						data-original_price="<?php echo number_format( $max_regular_price ); ?>">
						<del>NT$ <?php echo number_format( $max_regular_price ); ?></del>
					</span>
					<span class="mb-0 mt-1 salesPrice tracking-normal"
						data-original_price="<?php echo number_format( $min ); ?>">NT$
						<?php echo number_format( $min ); ?>
					</span>
					<?php endif; ?>
					<?php else : ?>
						<?php if ( ! empty( $max_regular_price ) ) : ?>
					<span class="mb-0 mt-1 opacity-50 regularPrice tracking-normal"
						data-original_price="<?php echo number_format( $max_regular_price ); ?>">
						<del>NT$ <?php echo number_format( $max_regular_price ); ?></del>
					</span>
					<?php endif; ?>
						<?php if ( ! empty( $variable_id ) ) : ?>
					<span class="mb-0 mt-1 salesPrice tracking-normal"
						data-original_price="<?php echo number_format( $min ) . ' – NT$ ' . number_format( $max ); ?>">
						NT$<?php echo number_format( $salesPrice ); ?>
					</span>
					<?php else : ?>
						<?php if ( ! empty( $max ) ) : ?>
					<span class="mb-0 mt-1 salesPrice tracking-normal"
						data-original_price="<?php echo number_format( $min ) . ' – NT$ ' . number_format( $max ); ?>">
						NT$<?php echo number_format( $min ); ?> – NT$<?php echo number_format( $max ); ?>
					</span>
					<?php endif; ?>
					<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div
			class="productAddToCart flex gap-1 w-fit whitespace-nowrap items-center cursor-pointer rounded text-center py-1.5 px-2.5 bg-black  border border-black border-solid hover:bg-[#dddddd]">
			<a class="!text-white flex gap-1 items-center"><svg
					class="loading hidden animate-spin fill-white" xmlns="http://www.w3.org/2000/svg"
					height="1em" viewBox="0 0 512 512">
					<path
						d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z">
					</path>
				</svg>加入購物車</a>
		</div>
	</div>
</div>
<?php endif; ?>
