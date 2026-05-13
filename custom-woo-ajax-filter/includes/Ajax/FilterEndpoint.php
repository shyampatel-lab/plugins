<?php

namespace CustomWooAjaxFilter\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FilterEndpoint {
	public function init(): void {
		add_action( 'wp_ajax_cwaf_filter_products', array( $this, 'handle' ) );
		add_action( 'wp_ajax_nopriv_cwaf_filter_products', array( $this, 'handle' ) );
	}

	public function handle(): void {
		check_ajax_referer( 'cwaf_nonce', 'nonce' );

		$filters_raw = isset( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : '';
		$filters     = is_string( $filters_raw ) ? json_decode( $filters_raw, true ) : array();
		$filters     = is_array( $filters ) ? $filters : array();
		$paged   = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

		$tax_query = array( 'relation' => 'AND' );
		foreach ( $filters as $taxonomy => $terms ) {
			$taxonomy = sanitize_key( $taxonomy );
			$terms    = array_map( 'sanitize_title', (array) $terms );
			if ( taxonomy_exists( $taxonomy ) && ! empty( $terms ) ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $terms,
					'operator' => 'IN',
				);
			}
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'paged'          => $paged,
			'tax_query'      => count( $tax_query ) > 1 ? $tax_query : array(),
			'no_found_rows'  => false,
		);

		$query = new \WP_Query( $args );
		ob_start();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
		} else {
			echo '<p>' . esc_html__( 'No products found.', 'custom-woo-ajax-filter' ) . '</p>';
		}
		wp_reset_postdata();

		wp_send_json_success(
			array(
				'html'      => ob_get_clean(),
				'max_pages' => (int) $query->max_num_pages,
			)
		);
	}
}
