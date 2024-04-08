<?php
/**
 * Plugin Name: R2 WC Product Addon
 * Description: WC 商品加購功能外掛,整合AJAX加入購物車,及product bundle套件客製化功能
 * Author URI: https://github.com/s0985514623
 * License: GPLv2
 * Version: 1.2.6
 * Requires PHP: 7.4.0
 */

/**
 * Tags: woocommerce, shop, order
 * Requires at least: 4.6
 * Tested up to: 4.8
 * Stable tag: 4.3
 */

namespace J7\WpMyAppPlugin\MyApp\Inc;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
if ( ! \class_exists( 'J7\WpMyAppPlugin\MyApp\Inc\Plugin' ) ) {
	/**
		 * Class Plugin
		 */
	final class Plugin {
		const KEBAB       = 'r2-wc-product-addon';
		const GITHUB_REPO = 'https://github.com/s0985514623/R2-WC-Product-Addon';

		/**
		 * Instance
		 *
		 * @var Plugin
		 */
		private static $instance;

		/**
		 * Constructor
		 */
		public function __construct() {
			require_once __DIR__ . '/vendor/autoload.php';
			require_once __DIR__ . '/inc/admin.php';

			// 整入舊得bundle代碼
			require_once __DIR__ . '/inc/custom/bundles/php/produce-bundles.php';

			$this->plugin_update_checker();
		}
		/**
		 * Plugin update checker
		 *
		 * @return void
		 */
		public function plugin_update_checker(): void {
			$update_checker = PucFactory::buildUpdateChecker(
				self::GITHUB_REPO,
				__FILE__,
				self::KEBAB
			);
			/**
			 * Type
			 *
			 * @var \Puc_v4p4_Vcs_PluginUpdateChecker $update_checker
			 */
			$update_checker->setBranch( 'master' );
			// if your repo is private, you need to set authentication
			// $update_checker->setAuthentication(self::$github_pat);
			$update_checker->getVcsApi()->enableReleaseAssets();
		}

		/**
		 * Instance
		 *
		 * @return Plugin
		 */
		public static function instance() {
			if ( empty( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	}
	// require_once __DIR__ . '/vendor/autoload.php';
	// require_once __DIR__ . '/inc/admin.php';

	// // 整入舊得bundle代碼
	// require_once __DIR__ . '/inc/custom/bundles/php/produce-bundles.php';

	Plugin::instance();
}
