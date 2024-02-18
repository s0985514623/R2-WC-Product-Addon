<?php
//解構賦值 $args
[
    'product' => $product,
    'meta'    => $meta,
 ] = $args;

$img_id  = $product->get_image_id();
$img_src = \wp_get_attachment_image_src($img_id, [ 450, 450 ]);
$name    = $product->get_name();

//解構賦值 $meta
[
    'parentProductId' => $parent_product_id,
    'productId'       => $product_id,
    'regularPrice'    => $regular_price,
    'salesPrice'      => $sales_price,
 ] = $meta;

if (empty($regular_price) && empty($sales_price)) {
    // 商品類型轉換時，才會發生這種情況
    $regular_price = $product->get_regular_price();
    $sales_price   = $product->get_sale_price();
}

$product_status = $product->get_status();
if ($product_status === 'publish'):
?>
<div class="simpleProduct productAddon py-5 px-[15px] grid grid-cols-5"
	data-product_addon_id="<?=$product_id?>" data-parent_product_id="<?=$parent_product_id?>">
	<div class="productAddonImg aspect-square w-full col-span-2 ">
		<img src="<?=$img_src[ 0 ]?>" alt="<?=$name?>">
	</div>
	<div class="productAddonInfo px-[15px] flex flex-col justify-between col-span-3 ">
		<div class="flex flex-col gap-2">
			<span class="productAddonTitle md:text-base text-sm"><?=$name?></span>
			<div class="text-sm productAddonPrice">
				<?php if (!empty($sales_price)): ?>
				<span
					class="tracking-normal text-[#4562A8] opacity-50 line-through mr-[3px]">NT$<?=number_format($regular_price)?></span>
				<span
					class="tracking-normal text-[#4562A8] salesPrice">NT$<?=number_format($sales_price)?></span>
				<?php else: ?>
				<span
					class="tracking-normal text-[#4562A8] opacity-50 line-through mr-[3px] salesPrice">NT$<?=number_format($regular_price)?></span>
				<?php endif;?>
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
<?php endif;?>