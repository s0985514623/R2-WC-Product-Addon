<?php
declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

// use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

class ProductAddon
{
    public function __construct()
    {
        \add_filter('woocommerce_product_data_tabs', [ $this, 'product_settings_tabs' ]);
        \add_action('woocommerce_product_data_panels', [ $this, 'render_app' ]);
    }

    public function product_settings_tabs($tabs)
    {
        $tabs[ 'r2_wcpa' ] = array(
            'label'    => '加價購商品',
            'target'   => 'r2_wcpa',
            'class'    => array('show_if_simple', 'show_if_variable', 'hide_if_bundle'),
            'priority' => 60,
        );
        return $tabs;
    }
    public function render_app()
    {
        echo '<div id="' . Bootstrap::RENDER_ID_1 . '" class="panel woocommerce_options_panel hidden">';
        echo '</div>';
    }
}