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
		$payload = isset( $_POST['filters'] ) ? json_decode( (string) wp_unslash( $_POST['filters'] ), true ) : array();
		$payload = is_array( $payload ) ? $payload : array();
		$paged   = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$relation = isset( $_POST['relation'] ) && 'OR' === strtoupper( sanitize_text_field( wp_unslash( $_POST['relation'] ) ) ) ? 'OR' : 'AND';

		$tax_query = array( 'relation' => $relation );
		$meta_query = array();
		foreach ( $payload as $key => $value ) {
			if ( in_array( $key, array( 'search', 'min_price', 'max_price', 'orderby' ), true ) ) {
				continue;
			}
			if ( taxonomy_exists( sanitize_key( $key ) ) ) {
				$terms = is_array( $value ) ? array_map( 'sanitize_title', $value ) : array_filter( array( sanitize_title( (string) $value ) ) );
				if ( $terms ) {
					$tax_query[] = array( 'taxonomy' => sanitize_key( $key ), 'field' => 'slug', 'terms' => $terms, 'operator' => 'IN' );
				}
			}
		}
		$min_price = isset( $payload['min_price'] ) ? (float) $payload['min_price'] : 0;
		$max_price = isset( $payload['max_price'] ) ? (float) $payload['max_price'] : 0;
		if ( $min_price || $max_price ) {
			$meta_query[] = array( 'key' => '_price', 'value' => array( $min_price, $max_price ? $max_price : 99999999 ), 'compare' => 'BETWEEN', 'type' => 'DECIMAL' );
		}

		$args = array(
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 12,
			'paged' => $paged,
			'tax_query' => count( $tax_query ) > 1 ? $tax_query : array(),
			'meta_query' => $meta_query,
			's' => isset( $payload['search'] ) ? sanitize_text_field( $payload['search'] ) : '',
		);
		if ( isset( $payload['orderby'] ) ) {
			if ( 'price' === $payload['orderby'] ) {
				$args['orderby'] = 'meta_value_num';
				$args['meta_key'] = '_price';
				$args['order'] = 'ASC';
			} elseif ( 'title' === $payload['orderby'] ) {
				$args['orderby'] = 'title';
				$args['order'] = 'ASC';
			} elseif ( 'rating' === $payload['orderby'] ) {
				$args['meta_key'] = '_wc_average_rating';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'DESC';
			}
		}

		$query = new \WP_Query( $args );
		ob_start();
		if ( $query->have_posts() ) {
			echo '<ul class="products columns-4">';
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			echo '</ul>';
		} else {
			echo '<p>' . esc_html__( 'No products found.', 'custom-woo-ajax-filter' ) . '</p>';
		}
		wp_reset_postdata();
		wp_send_json_success( array( 'html' => ob_get_clean(), 'max_pages' => (int) $query->max_num_pages ) );
	}
}
