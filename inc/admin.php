<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use Kucrut\Vite;

class Bootstrap
{
    const PLUGIN_DIR  = __DIR__ . '/../';
    const APP_NAME    = 'My App';
    const KEBAB       = 'my-app';
    const SNAKE       = 'my_app';
    const BASE_URL    = '/';
    const RENDER_ID_1 = 'my_app';
    const RENDER_ID_2 = 'my_app_metabox';
    const API_TIMEOUT = '30000';

    function __construct()
    {

        new ShortCode(self::SNAKE . '_shortcode');
        new Ajax();
        new CPT(self::KEBAB, array(
            'post_metas' => [ 'meta', 'settings' ],
            'rewrite'    => array(
                'template_path' => 'test.php',
                'slug'          => 'test',
                'var'           => self::SNAKE . '_test',
            ),
        ));
    }

    public function init(): void
    {
        \add_action('admin_enqueue_scripts', [ $this, 'enqueue_script' ], 99);
        \add_action('wp_enqueue_scripts', [ $this, 'enqueue_script' ], 99);
        \add_action('wp_footer', [ $this, 'render_app' ]);
    }

    /**
     * Render application's markup
     */
    public function render_app(): void
    {
        echo '<div id="' . self::RENDER_ID_1 . '"></div>';
    }

    /**
     * Enqueue script
     */
    public function enqueue_script(): void
    {
        /*
         * enquene script on demand
        if (\is_admin()) {
        // match wp-admin screen_id
        $screen = \get_current_screen();
        if (($screen->id !== self::KEBAB)) return;
        } else {
        // match front-end post_type slug {self::KEBAB}
        if (strpos($_SERVER['REQUEST_URI'], self::KEBAB) === false) return;
        }
         */

        Vite\enqueue_asset(
            dirname(__DIR__) . '/js/dist',
            'js/src/main.tsx',
            [
                'handle'    => self::KEBAB,
                'in-footer' => true,
             ]
        );

        $post_id   = \get_the_ID();
        $permalink = \get_permalink($post_id);

        \wp_localize_script(self::KEBAB, self::SNAKE . '_data', array(
            'env' => [
                'siteUrl'     => \site_url(),
                'ajaxUrl'     => \admin_url('admin-ajax.php'),
                'userId'      => \wp_get_current_user()->data->ID ?? null,
                'postId'      => $post_id,
                'permalink'   => $permalink,
                "APP_NAME"    => Bootstrap::APP_NAME,
                "KEBAB"       => Bootstrap::KEBAB,
                "SNAKE"       => Bootstrap::SNAKE,
                "BASE_URL"    => Bootstrap::BASE_URL,
                "RENDER_ID_1" => Bootstrap::RENDER_ID_1,
                "RENDER_ID_2" => Bootstrap::RENDER_ID_2,
                "API_TIMEOUT" => Bootstrap::API_TIMEOUT,
             ],
        ));

        \wp_localize_script(self::KEBAB, 'wpApiSettings', array(
            'root'  => \untrailingslashit(\esc_url_raw(rest_url())),
            'nonce' => \wp_create_nonce('wp_rest'),
        ));
    }
}

require_once __DIR__ . '/utils/includes.php';
require_once __DIR__ . '/custom/includes.php';
