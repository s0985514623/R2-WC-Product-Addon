<?php

declare (strict_types = 1);

namespace J7\WpMyAppPlugin\MyApp\Inc;

use J7\WpMyAppPlugin\MyApp\Inc\Bootstrap;

final class CPT {

	public $post_type  = '';
	public $post_metas = array();
	public $rewrite    = array();

	function __construct( $cpt, $args ) {
		$this->post_type  = $cpt;
		$this->post_metas = $args['post_metas'];
		$this->rewrite    = $args['rewrite'] ?? array();

		if ( empty( $this->post_type ) ) {
			return;
		}

		\add_action( 'init', array( $this, 'init' ) );

		if ( ! empty( $args['post_metas'] ) ) {
			\add_action( 'rest_api_init', array( $this, 'add_post_meta' ) );
		}

		\add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		\add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );

		if ( ! empty( $args['rewrite'] ) ) {
			\add_filter( 'query_vars', array( $this, 'add_query_var' ) );
			\add_filter( 'template_include', array( $this, 'load_custom_template' ), 99 );
		}
	}

	public function init(): void {
		Functions::register_cpt( $this->post_type );

		// add {$this->post_type}/{slug}/test rewrite rule
		if ( ! empty( $this->rewrite ) ) {
			\add_rewrite_rule( '^' . $this->post_type . '/([^/]+)/' . $this->rewrite['slug'] . '/?$', 'index.php?post_type=' . $this->post_type . '&name=$matches[1]&' . $this->rewrite['var'] . '=1', 'top' );
			\flush_rewrite_rules();
		}
	}

	public function add_post_meta(): void {
		foreach ( $this->post_metas as $meta_key ) {
			\register_meta(
				'post',
				Bootstrap::SNAKE . '_' . $meta_key,
				array(
					'type'         => 'string',
					'show_in_rest' => true,
					'single'       => true,
				)
			);
		}
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox(): void {
		\add_action( 'add_meta_boxes', array( $this, 'add_metaboxs' ) );
		\add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
		\add_filter( 'rewrite_rules_array', array( $this, 'custom_post_type_rewrite_rules' ) );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metaboxs(): void {
		Functions::add_metabox(
			array(
				'id'        => Bootstrap::RENDER_ID_2,
				'label'     => __( 'Custom MetaBox' ),
				'post_type' => $this->post_type,
			)
		);
	}

	public function add_query_var( $vars ) {
		$vars[] = $this->rewrite['var'];
		return $vars;
	}

	public function custom_post_type_rewrite_rules( $rules ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
		return $rules;
	}

	public function save_metabox( $post_id, $post ) {

		/*
		* We need to verify this came from the our screen and with proper authorization,
		* because save_post can be triggered at other times.
		*/

		// Check if our nonce is set.
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['_wpnonce'];

		/*
		* If this is an autosave, our form has not been submitted,
		* so we don't want to do anything.
		*/
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$post_type = \sanitize_text_field( $_POST['post_type'] ?? '' );

		// Check the user's permissions.
		if ( $this->post !== $post_type ) {
			return $post_id;
		}

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		// Sanitize the user input.
		$meta_data = \sanitize_text_field( $_POST[ Bootstrap::SNAKE . '_meta' ] );

		// Update the meta field.
		\update_post_meta( $post_id, Bootstrap::SNAKE . '_meta', $meta_data );
	}

	/**
	 * 設定 {Bootstrap::KEBAB}/{slug}/report 的 php template
	 */
	public function load_custom_template( $template ) {
		$repor_template_path = Bootstrap::PLUGIN_DIR . 'inc/templates/' . $this->rewrite['template_path'];

		if ( \get_query_var( $this->rewrite['var'] ) ) {
			if ( file_exists( $repor_template_path ) ) {
				return $repor_template_path;
			}
		}
		return $template;
	}
}
