<?php
namespace J7\WpMyAppPlugin\MyApp\Inc;

[
	'filterProducts' => $filterProducts,
] = $args;

?>
<div
	class="productAddonWrap w-full bg-[#F6F6F6] border border-solid border-[#EDEDED] px-[15px] py-[10px]">
	<h3 class="goBuyTitle md:text-xl text-sm">尚有更多精彩優惠等著你！目前未享用：</h3>
</div>
<div id="productAddonList"
	class="w-full grid grid-cols-1 md:grid-cols-3 text-[#333333] font-semibold border border-t-0 border-solid border-[#EDEDED] mb-5 ">
	<?php
	foreach ( $filterProducts as $item ) {
		switch ( $item['meta']['productType'] ) {
			case 'variable':
				\load_template(
					Bootstrap::get_plugin_dir() . '/inc/templates/cart/variable.php',
					false,
					array(
						'product'             => $item['product']['productObj'],
						'meta'                => $item['meta'],
						'variationAttributes' => $item['product']['productObj']->get_variation_attributes( false ),
					)
				);
				break;
			case 'simple':
				\load_template(
					Bootstrap::get_plugin_dir() . '/inc/templates/cart/simple.php',
					false,
					array(
						'product' => $item['product']['productObj'],
						'meta'    => $item['meta'],
					)
				);
				break;
			default:
				\load_template(
					Bootstrap::get_plugin_dir() . '/inc/templates/cart/simple.php',
					false,
					array(
						'product' => $item['product']['productObj'],
						'meta'    => $item['meta'],
					)
				);
				break;
		}
	}
	?>
</div>
