<?php

/**
 * Plugin Name: R2 WC Product Addon
 * Description: WC 商品加購功能外掛,整合AJAX加入購物車,及product bundle套件客製化功能
 * Author URI: https://github.com/s0985514623
 * License: GPLv2
 * Version: 1.1.4
 * Requires PHP: 7.4.0
 */

/**
 * Tags: woocommerce, shop, order
 * Requires at least: 4.6
 * Tested up to: 4.8
 * Stable tag: 4.3
 */

namespace J7\WpMyAppPlugin\MyApp\Inc;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/inc/admin.php';

//整入舊得bundle代碼
require_once __DIR__ . '/inc/custom/bundles/php/produce-bundles.php';

$instance = new Bootstrap();
$instance->init();
