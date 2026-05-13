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
				'label'        => __( 'Filter Sets', 'custom-woo-ajax-filter' ),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'supports'     => array( 'title' ),
				'menu_icon'    => 'dashicons-filter',
			)
		);
	}

	public function register_metabox(): void {
		add_meta_box( 'cwaf_config', __( 'Filter Configuration', 'custom-woo-ajax-filter' ), array( $this, 'render_metabox' ), 'cwaf_filter_set' );
	}

	public function render_metabox( \WP_Post $post ): void {
		wp_nonce_field( 'cwaf_save_filter_set', 'cwaf_nonce' );
		$filters  = (array) get_post_meta( $post->ID, '_cwaf_filters', true );
		$layout   = (string) get_post_meta( $post->ID, '_cwaf_layout', true );
		$relation = (string) get_post_meta( $post->ID, '_cwaf_relation', true );
		$taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
		$layout   = $layout ? $layout : 'sidebar';
		$relation = $relation ? $relation : 'AND';
		?>
		<p>
			<label for="cwaf_layout"><strong><?php esc_html_e( 'Filter Layout', 'custom-woo-ajax-filter' ); ?></strong></label>
			<select name="cwaf_layout" id="cwaf_layout">
				<option value="sidebar" <?php selected( $layout, 'sidebar' ); ?>><?php esc_html_e( 'Vertical Sidebar', 'custom-woo-ajax-filter' ); ?></option>
				<option value="topbar" <?php selected( $layout, 'topbar' ); ?>><?php esc_html_e( 'Horizontal Top Bar', 'custom-woo-ajax-filter' ); ?></option>
				<option value="accordion" <?php selected( $layout, 'accordion' ); ?>><?php esc_html_e( 'Accordion', 'custom-woo-ajax-filter' ); ?></option>
			</select>
		</p>
		<p>
			<label for="cwaf_relation"><strong><?php esc_html_e( 'Condition Relation', 'custom-woo-ajax-filter' ); ?></strong></label>
			<select name="cwaf_relation" id="cwaf_relation">
				<option value="AND" <?php selected( $relation, 'AND' ); ?>>AND</option>
				<option value="OR" <?php selected( $relation, 'OR' ); ?>>OR</option>
			</select>
		</p>
		<p><strong><?php esc_html_e( 'Attribute/Taxonomy Filters', 'custom-woo-ajax-filter' ); ?></strong></p>
		<table class="widefat">
			<thead><tr><th><?php esc_html_e( 'Taxonomy', 'custom-woo-ajax-filter' ); ?></th><th><?php esc_html_e( 'Label', 'custom-woo-ajax-filter' ); ?></th><th><?php esc_html_e( 'Type', 'custom-woo-ajax-filter' ); ?></th></tr></thead>
			<tbody>
			<?php for ( $i = 0; $i < 8; $i++ ) : $row = $filters[ $i ] ?? array(); ?>
			<tr>
				<td><select name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][taxonomy]"><option value=""><?php esc_html_e( 'Select', 'custom-woo-ajax-filter' ); ?></option><?php foreach ( $taxonomy_objects as $tax ) : ?><option value="<?php echo esc_attr( $tax->name ); ?>" <?php selected( $row['taxonomy'] ?? '', $tax->name ); ?>><?php echo esc_html( $tax->label ); ?></option><?php endforeach; ?></select></td>
				<td><input type="text" name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][label]" value="<?php echo esc_attr( $row['label'] ?? '' ); ?>"></td>
				<td><select name="cwaf_filters[<?php echo esc_attr( (string) $i ); ?>][type]"><option value="checkbox" <?php selected( $row['type'] ?? '', 'checkbox' ); ?>>Checkbox</option><option value="radio" <?php selected( $row['type'] ?? '', 'radio' ); ?>>Radio</option><option value="dropdown" <?php selected( $row['type'] ?? '', 'dropdown' ); ?>>Dropdown</option><option value="text" <?php selected( $row['type'] ?? '', 'text' ); ?>>Text Search</option></select></td>
			</tr>
			<?php endfor; ?>
			</tbody>
		</table>
		<?php
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
		$layout   = isset( $_POST['cwaf_layout'] ) ? sanitize_key( wp_unslash( $_POST['cwaf_layout'] ) ) : 'sidebar';
		$relation = isset( $_POST['cwaf_relation'] ) ? sanitize_key( wp_unslash( $_POST['cwaf_relation'] ) ) : 'AND';
		$rows     = isset( $_POST['cwaf_filters'] ) ? (array) wp_unslash( $_POST['cwaf_filters'] ) : array();
		$filters  = array();
		foreach ( $rows as $row ) {
			$taxonomy = isset( $row['taxonomy'] ) ? sanitize_key( $row['taxonomy'] ) : '';
			$type     = isset( $row['type'] ) ? sanitize_key( $row['type'] ) : 'checkbox';
			$label    = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
			if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
				$filters[] = compact( 'taxonomy', 'type', 'label' );
			}
		}
		update_post_meta( $post_id, '_cwaf_layout', $layout );
		update_post_meta( $post_id, '_cwaf_relation', strtoupper( $relation ) === 'OR' ? 'OR' : 'AND' );
		update_post_meta( $post_id, '_cwaf_filters', $filters );
	}
}
