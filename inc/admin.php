<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use Kucrut\Vite;

final class Bootstrap {

	const PLUGIN_DIR  = __DIR__ . '/../';
	const APP_NAME    = 'R2 WCPA';
	const KEBAB       = 'r2-wcpa';
	const SNAKE       = 'r2_wcpa';
	const BASE_URL    = '/';
	const RENDER_ID_1 = 'r2_wcpa';
	const RENDER_ID_2 = 'r2_wcpa_metabox';
	const API_TIMEOUT = '30000';

	function __construct() {

		new ShortCode( self::SNAKE . '_shortcode' );
		new Ajax();
		new ProductAddon();
		// new CPT(self::KEBAB, array(
		// 'post_metas' => [ 'meta', 'settings' ],
		// 'rewrite'    => array(
		// 'template_path' => 'test.php',
		// 'slug'          => 'test',
		// 'var'           => self::SNAKE . '_test',
		// ),
		// ));
	}

	public function init(): void {
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ), 99 );
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_script' ), 99 );
		// \add_action('wp_footer', [ $this, 'render_app' ]);
	}

	/**
	 * Render application's markup
	 */
	// public function render_app(): void
	// {
	// echo '<div id="' . self::RENDER_ID_1 . '"></div>';
	// }

	/**
	 * Enqueue script
	 */
	public function enqueue_script(): void {

		// enquene script on demand
		if ( \is_admin() ) {
			// match wp-admin screen_id is 'product'
			$screen = \get_current_screen();
			if ( ( $screen->id !== 'product' ) ) {
				return;
			}
		} else {
			// match front-end post_type slug 'product' or 'cart' or 'checkout'
			if ( ! ( strpos( $_SERVER['REQUEST_URI'], 'product' ) == true || strpos( $_SERVER['REQUEST_URI'], 'cart' ) == true || strpos( $_SERVER['REQUEST_URI'], 'checkout' ) == true ) ) {
				return;
			}
		}

		Vite\enqueue_asset(
			Plugin::$dir . '/js/dist',
			'js/src/main.tsx',
			array(
				'handle'    => self::KEBAB,
				'in-footer' => true,
			)
		);

		$post_id       = \get_the_ID();
		$permalink     = \get_permalink( $post_id );
		$products_info = Functions::get_products_info( $post_id );

		// 找出指定的 meta_id by meta_key
		// _report_password & _settings 欄位都是用 Modal儲存，不用往自訂欄位塞值
		global $wpdb;
		$power_shop_meta_meta_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s",
				$post_id,
				self::SNAKE . '_meta'
			)
		);
		\wp_localize_script(
			self::KEBAB,
			self::SNAKE . '_data',
			array(
				'products_info' => $products_info,
				'settings'      => array(
					'power_shop_meta_meta_id' => $power_shop_meta_meta_id,
				),
				'env'           => array(
					'siteUrl'     => \site_url(),
					'ajaxUrl'     => \admin_url( 'admin-ajax.php' ),
					'userId'      => \wp_get_current_user()->data->ID ?? null,
					'postId'      => $post_id,
					'permalink'   => $permalink,
					'APP_NAME'    => self::APP_NAME,
					'KEBAB'       => self::KEBAB,
					'SNAKE'       => self::SNAKE,
					'BASE_URL'    => self::BASE_URL,
					'RENDER_ID_1' => self::RENDER_ID_1,
					// "RENDER_ID_2" => Bootstrap::RENDER_ID_2,
					'API_TIMEOUT' => self::API_TIMEOUT,
				),
			)
		);

		\wp_localize_script(
			self::KEBAB,
			'wpApiSettings',
			array(
				'root'  => \untrailingslashit( \esc_url_raw( rest_url() ) ),
				'nonce' => \wp_create_nonce( 'wp_rest' ),
			)
		);

		// 获取目录中的所有文件
		$files = glob( self::get_plugin_dir() . '/js/dist/assets/*.css' );

		// 遍历文件并使用wp_enqueue_style加载它们
		foreach ( $files as $file ) {
			$file_url = self::get_plugin_url() . '/js/dist/assets/' . basename( $file );
			\wp_enqueue_style( basename( $file, '.css' ), $file_url );
		}
	}
	public static function get_plugin_dir(): string {
		$plugin_dir = \wp_normalize_path( \plugin_dir_path( __DIR__ . '../' ) );
		return $plugin_dir;
	}

	public static function get_plugin_url(): string {
		$plugin_url = \plugin_dir_url( self::get_plugin_dir() . 'plugin.php' );
		return $plugin_url;
	}
	public static function get_plugin_ver(): string {
		$plugin_data = \get_plugin_data( self::get_plugin_dir() . 'plugin.php' );
		$plugin_ver  = $plugin_data['Version'];
		return $plugin_ver;
	}
}

require_once __DIR__ . '/utils/includes.php';
require_once __DIR__ . '/custom/includes.php';
