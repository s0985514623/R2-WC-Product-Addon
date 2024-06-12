<?php
// 解構賦值 $args
[
	'product' => $product,
	'meta'    => $meta,
] = $args;

// 解構賦值 $meta
[
	'parentProductId' => $parent_product_id,
	'productId'       => $product_id,
	'regularPrice'    => $regular_price,
	'salesPrice'      => $sales_price,
] = $meta;

$img_id    = $product->get_image_id();
$img_src   = \wp_get_attachment_image_src( $img_id, array( 450, 450 ) );
$name      = $product->get_name();
$permalink = get_permalink( $product->get_id() ) . '?parentProductId=' . $parent_product_id;



if ( empty( $regular_price ) && empty( $sales_price ) ) {
	// 商品類型轉換時，才會發生這種情況
	$regular_price = $product->get_regular_price();
	$sales_price   = $product->get_sale_price();
}

$product_status = $product->get_status();
if ( $product_status === 'publish' ) :
	?>
<div class="simpleProduct productAddon flex w-full pl-6 pb-5 relative"
	data-product_addon_id="<?php echo $product_id; ?>" data-parent_product_id="<?php echo $parent_product_id; ?>">
	<div class="productAddonImg w-1/5 ">
		<input class="peer absolute left-0 top-5 " type="checkbox" />
		<img class="peer-checked:border-[5px] peer-checked:border-solid peer-checked:border-[#4562A8] "
			src="<?php echo $img_src[0]; ?>" alt="<?php echo $name; ?>">
	</div>
	<div class="productAddonInfo w-4/5 pl-6">
		<div class="productAddonName text-xl  font-bold mb-4"><a class="!text-[#4562A8]" href="<?php echo $permalink; ?>"><?php echo $name; ?></a></div>
		<div class="productAddonPrice">
			<div class="flex flex-wrap text-xl text-[#4562A8] font-bold gap-2">
	<?php if ( ! empty( $sales_price ) ) : ?>
				<span class="mb-0 mt-1 opacity-50"><del>NT$ <?php echo number_format( $regular_price ); ?></del></span>
				<span class="mb-0 mt-1 salesPrice">NT$ <?php echo number_format( $sales_price ); ?></span>
				<?php else : ?>
				<span class="mb-0 mt-1 salesPrice">NT$ <?php echo number_format( $regular_price ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
