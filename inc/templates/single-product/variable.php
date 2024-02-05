<?php
//解構賦值 $args
[
    'product'             => $product,
    'meta'                => $meta,
    'variationAttributes' => $variationAttributes,
 ] = $args;

//以防如果沒有變體屬性，賦予空陣列
$variationAttributes = $variationAttributes ?? [  ];
$img_id              = $product->get_image_id();
$img_src             = \wp_get_attachment_image_src($img_id, [ 450, 450 ]);
$name                = $product->get_name();

//解構賦值 $meta
[
    'productId'  => $product_addon_id,
    'variations' => $variations,
 ]                 = $meta;
$price_arr         = [  ];
$regular_price_arr = [  ];
$variation_arr     = [  ];
$variations        = $variations ?? [  ];

foreach ($variations as $variation) {
    if (empty((int) $variation[ 'salesPrice' ])) {
        $price_arr[  ] = (int) $variation[ 'regularPrice' ];
    } else {
        $price_arr[  ] = (int) $variation[ 'salesPrice' ];
    }
    $regular_price_arr[  ] = (int) $variation[ 'regularPrice' ];
}

$filtered_price_arr = array_filter($price_arr, function ($price) {
    return !empty($price);
});

$max               = empty($filtered_price_arr) ? '' : max($filtered_price_arr);
$min               = empty($filtered_price_arr) ? '' : min($filtered_price_arr);
$max_regular_price = empty($regular_price_arr) ? '' : max($regular_price_arr);

// if (empty($regular_price) && empty($sales_price)) {
//     // 商品類型轉換時，才會發生這種情況
//     $regular_price = $product->get_regular_price();
//     $sales_price   = $product->get_sale_price();
// }

$product_status = $product->get_status();
if ($product_status === 'publish'):
?>
<div class="variableProduct productAddon flex w-full pl-6 pb-5 relative"
	data-product_addon_id="<?=$product_addon_id?>" data-variable_id="">
	<div class="productAddonImg w-1/5 ">
		<input class="peer absolute left-0 top-5 " type="checkbox" />
		<img class="peer-checked:border-[5px] peer-checked:border-solid peer-checked:border-[#4562A8] "
			src="<?=$img_src[ 0 ]?>" alt="<?=$name?>">
	</div>
	<div class="productAddonInfo w-4/5 pl-6">
		<div class="productAddonName text-xl text-[#4562A8] font-bold mb-4"><?=$name?></div>
		<table class="font-bold text-base">
			<tbody>
				<?php foreach ($variationAttributes as $label => $valueArray): ?>
				<tr>
					<th class="border-0">
						<label class="text-nowrap" for="<?=$label?>"><?=\wc_attribute_label($label)?></label>
					</th>
					<td class="border-0">
						<select class="" data-label_key="<?=$label?>">
							<option value="" selected>請選取一個選項</option>
							<?php foreach ($valueArray as $value): ?>
							<option value="<?=urldecode($value)?>"><?=urldecode($value)?></option>
							<?php endforeach;?>
						</select>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<div class="clearLink hidden font-bold text-base"
			data-product_addon_id="<?=$product_addon_id?>"><a href="javascript:void(0);"
				class="!underline">清除</a>
		</div>
		<div class="productAddonPrice">
			<div class="flex flex-wrap text-xl text-[#4562A8] font-bold gap-2">
				<?php if ($max === $min && !empty($min)): ?>
				<?php if ($max === $max_regular_price): ?>
				<p class="mb-0 mt-1 salesPrice" data-original_price="<?=$min?>">NT$ <?=$min?></p>
				<?php else: ?>
				<p class="mb-0 mt-1 opacity-50 regularPrice" data-original_price="<?=$max_regular_price?>">
					<del>NT$ <?=$max_regular_price?></del>
				</p>
				<p class="mb-0 mt-1 salesPrice" data-original_price="<?=$min?>">NT$ <?=$min?></p>
				<?php endif;?>
				<?php else: ?>
				<?php if (!empty($max_regular_price)): ?>
				<p class="mb-0 mt-1 opacity-50 regularPrice" data-original_price="<?=$max_regular_price?>">
					<del>NT$ <?=$max_regular_price?></del>
				</p>
				<?php endif;?>
				<?php if (!empty($max)): ?>
				<p class="mb-0 mt-1 salesPrice" data-original_price="<?=$min . ' – NT$ ' . $max?>">
					NT$<?=$min?> – NT$<?=$max?>
				</p>
				<?php endif;?>
				<?php endif;?>
			</div>
		</div>
	</div>
</div>
<?php endif;?>