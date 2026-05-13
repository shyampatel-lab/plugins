<?php

namespace CustomWooAjaxFilter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FilterSets {
	public function init(): void {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post_cwaf_filter_set', array( $this, 'save_metabox' ) );
	}

	public function register_post_type(): void {
		register_post_type(
			'cwaf_filter_set',
			array(
				'label'       => __( 'Filter Sets', 'custom-woo-ajax-filter' ),
				'public'      => false,
				'show_ui'     => true,
				'show_in_menu'=> true,
				'supports'    => array( 'title' ),
				'menu_icon'   => 'dashicons-filter',
			)
		);
	}

	public function register_metabox(): void {
		add_meta_box( 'cwaf_config', __( 'Filter Configuration', 'custom-woo-ajax-filter' ), array( $this, 'render_metabox' ), 'cwaf_filter_set' );
	}

	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'cwaf_save_filter_set', 'cwaf_nonce' );
		$taxonomies = get_object_taxonomies( 'product', 'objects' );
		$selected   = (array) get_post_meta( $post->ID, '_cwaf_taxonomies', true );
		echo '<p><label>' . esc_html__( 'Enable taxonomies/attributes', 'custom-woo-ajax-filter' ) . '</label></p>';
		echo '<select name="cwaf_taxonomies[]" multiple style="width:100%;min-height:150px;">';
		foreach ( $taxonomies as $tax ) {
			echo '<option value="' . esc_attr( $tax->name ) . '" ' . selected( in_array( $tax->name, $selected, true ), true, false ) . '>' . esc_html( $tax->label ) . '</option>';
		}
		echo '</select>';
	}

	public function save_metabox( int $post_id ): void {
		if ( ! isset( $_POST['cwaf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cwaf_nonce'] ) ), 'cwaf_save_filter_set' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$taxonomies = isset( $_POST['cwaf_taxonomies'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['cwaf_taxonomies'] ) ) : array();
		update_post_meta( $post_id, '_cwaf_taxonomies', $taxonomies );
	}
}
