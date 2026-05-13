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
		register_post_type( 'cwaf_filter_set', array( 'label' => __( 'Filter Sets', 'custom-woo-ajax-filter' ), 'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'supports' => array( 'title' ), 'menu_icon' => 'dashicons-filter' ) );
	}

	public function register_metabox(): void {
		add_meta_box( 'cwaf_config', __( 'Filter Configuration', 'custom-woo-ajax-filter' ), array( $this, 'render_metabox' ), 'cwaf_filter_set' );
	}

	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'cwaf_save_filter_set', 'cwaf_nonce' );
		$filters          = (array) get_post_meta( $post->ID, '_cwaf_filters', true );
		$taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
		?>
		<table class="widefat"><thead><tr><th>Source</th><th>Label</th><th>Type</th><th>Min</th><th>Max</th><th>Step</th></tr></thead><tbody>
		<?php for ( $i = 0; $i < 10; $i++ ) : $row = $filters[ $i ] ?? array(); ?>
			<tr>
			<td><select name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][source]"><option value="">Select</option><option value="price" <?php selected( $row['source'] ?? '', 'price' ); ?>>Price</option><?php foreach ( $taxonomy_objects as $tax ) : ?><option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $row['source'] ?? '', $tax->name ); ?>><?php echo esc_html( $tax->label ); ?></option><?php endforeach; ?></select></td>
			<td><input type="text" name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][label]" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>"></td>
			<td><select name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][type]"><option value="checkbox" <?php selected( $row['type'] ?? '', 'checkbox' ); ?>>Checkbox</option><option value="radio" <?php selected( $row['type'] ?? '', 'radio' ); ?>>Radio</option><option value="dropdown" <?php selected( $row['type'] ?? '', 'dropdown' ); ?>>Dropdown</option><option value="text" <?php selected( $row['type'] ?? '', 'text' ); ?>>Text</option><option value="range" <?php selected( $row['type'] ?? '', 'range' ); ?>>Range Slider</option></select></td>
			<td><input type="number" name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][min]" value="<?php echo esc_attr( $row['min'] ?? '' ); ?>"></td>
			<td><input type="number" name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][max]" value="<?php echo esc_attr( $row['max'] ?? '' ); ?>"></td>
			<td><input type="number" name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][step]" value="<?php echo esc_attr( $row['step'] ?? '1' ); ?>"></td>
			</tr>
		<?php endfor; ?></tbody></table>
		<p><em><?php esc_html_e( 'Filter logic is fixed to AND by default.', 'custom-woo-ajax-filter' ); ?></em></p>
		<?php
	}

	public function save_metabox( int $post_id ): void {
		if ( ! isset( $_POST['cwaf_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cwaf_nonce'] ) ), 'cwaf_save_filter_set' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$rows    = isset( $_POST['cwaf_filters'] ) ? (array) wp_unslash( $_POST['cwaf_filters'] ) : array();
		$filters = array();
		foreach ( $rows as $row ) {
			$source = isset( $row['source'] ) ? sanitize_key( $row['source'] ) : '';
			if ( ! $source || ( 'price' !== $source && ! taxonomy_exists( $source ) ) ) {
				continue;
			}
			$filters[] = array( 'source' => $source, 'type' => isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'checkbox', 'label' => isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '', 'min' => isset( $row['min'] ) ? (float) $row['min'] : 0, 'max' => isset( $row['max'] ) ? (float) $row['max'] : 1000, 'step' => isset( $row['step'] ) ? (float) $row['step'] : 1 );
		}
		update_post_meta( $post_id, '_cwaf_relation', 'AND' );
		update_post_meta( $post_id, '_cwaf_filters', $filters );
	}
}
