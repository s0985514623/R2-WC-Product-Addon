<?php
use J7\WpMyAppPlugin\MyApp\Inc\Functions;
// 解構賦值 $args
[
	'product'             => $product,
	'meta'                => $meta,
	'variationAttributes' => $variationAttributes,
] = $args;

// 以防如果沒有變體屬性，賦予空陣列
$variationAttributes = $variationAttributes ?? array();
$img_id              = $product->get_image_id();
$img_src             = \wp_get_attachment_image_src( $img_id, array( 450, 450 ) );
$name                = $product->get_name();

// 解構賦值 $meta
[
	'parentProductId' => $parent_product_id,
	'productId'       => $product_addon_id,
	'variations'      => $variations,
]                  = $meta;
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
<div class="variableProduct productAddon flex w-full pl-6 pb-5 relative"
	data-product_addon_id="<?php echo $product_addon_id; ?>" data-variable_id="<?php echo $variable_id; ?>"
	data-parent_product_id="<?php echo $parent_product_id; ?>">
	<div class="productAddonImg w-1/5 ">
		<input class="peer absolute left-0 top-5 " type="checkbox" />
		<img class="peer-checked:border-[5px] peer-checked:border-solid peer-checked:border-[#4562A8] "
			src="<?php echo $img_src[0]; ?>" alt="<?php echo $name; ?>">
	</div>
	<div class="productAddonInfo w-4/5 pl-6">
		<div class="productAddonName text-xl text-[#4562A8] font-bold mb-4"><?php echo $name; ?></div>
		<table class="font-bold text-base">
			<tbody>
				<?php foreach ( $variationAttributes as $label => $valueArray ) : ?>
				<tr>
					<th class="border-0">
						<label class="text-nowrap" for="<?php echo $label; ?>"><?php echo \wc_attribute_label( $label ); ?></label>
					</th>
					<td class="border-0">
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
		<div class="clearLink hidden font-bold text-base"
			data-product_addon_id="<?php echo $product_addon_id; ?>"><a href="javascript:void(0);"
				class="!underline">清除</a>
		</div>
		<div class="productAddonPrice">
			<div class="flex flex-wrap text-xl text-[#4562A8] font-bold gap-2">

				<?php if ( $max === $min && ! empty( $min ) ) : ?>
					<?php if ( $max === $max_regular_price ) : ?>
				<span class="mb-0 mt-1 salesPrice" data-original_price="<?php echo number_format( $min ); ?>">NT$
						<?php echo number_format( $min ); ?>
				</span>
				<?php else : ?>
				<span class="mb-0 mt-1 opacity-50 regularPrice"
					data-original_price="<?php echo number_format( $max_regular_price ); ?>">
					<del>NT$ <?php echo number_format( $max_regular_price ); ?></del>
				</span>
				<span class="mb-0 mt-1 salesPrice" data-original_price="<?php echo number_format( $min ); ?>">NT$
					<?php echo number_format( $min ); ?>
				</span>
				<?php endif; ?>
				<?php else : ?>
					<?php if ( ! empty( $max_regular_price ) ) : ?>
				<span class="mb-0 mt-1 opacity-50 regularPrice"
					data-original_price="<?php echo number_format( $max_regular_price ); ?>">
					<del>NT$ <?php echo number_format( $max_regular_price ); ?></del>
				</span>
				<?php endif; ?>
					<?php if ( ! empty( $variable_id ) ) : ?>
				<span class="mb-0 mt-1 salesPrice"
					data-original_price="<?php echo number_format( $min ) . ' – NT$ ' . number_format( $max ); ?>">
					NT$<?php echo number_format( $salesPrice ); ?>
				</span>
				<?php else : ?>
					<?php if ( ! empty( $max ) ) : ?>
				<span class="mb-0 mt-1 salesPrice"
					data-original_price="<?php echo number_format( $min ) . ' – NT$ ' . number_format( $max ); ?>">
					NT$<?php echo number_format( $min ); ?> – NT$<?php echo number_format( $max ); ?>
				</span>
				<?php endif; ?>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
