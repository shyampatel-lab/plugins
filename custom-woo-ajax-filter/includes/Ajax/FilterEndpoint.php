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
		$payload  = isset( $_POST['filters'] ) ? json_decode( (string) wp_unslash( $_POST['filters'] ), true ) : array();
		$payload  = is_array( $payload ) ? $payload : array();
		$paged    = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$relation = 'AND';

		$tax_query  = array( 'relation' => $relation );
		$meta_query = array();
		foreach ( $payload as $key => $value ) {
			if ( in_array( $key, array( 'search', 'orderby', 'price_min', 'price_max' ), true ) || str_ends_with( (string) $key, '_min' ) || str_ends_with( (string) $key, '_max' ) ) {
				continue;
			}
			if ( taxonomy_exists( sanitize_key( $key ) ) ) {
				$terms = is_array( $value ) ? array_map( 'sanitize_title', $value ) : array_filter( array( sanitize_title( (string) $value ) ) );
				if ( $terms ) {
					$tax_query[] = array( 'taxonomy' => sanitize_key( $key ), 'field' => 'slug', 'terms' => $terms, 'operator' => 'IN' );
				}
			}
		}

		$min_price = isset( $payload['price_min'] ) ? (float) $payload['price_min'] : 0;
		$max_price = isset( $payload['price_max'] ) ? (float) $payload['price_max'] : 0;
		if ( $min_price || $max_price ) {
			$meta_query[] = array( 'key' => '_price', 'value' => array( $min_price, $max_price ? $max_price : 99999999 ), 'compare' => 'BETWEEN', 'type' => 'DECIMAL' );
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => isset( $payload['per_page'] ) ? max( 1, min( 50, absint( $payload['per_page'] ) ) ) : 10,
			'paged'          => $paged,
			'tax_query'      => count( $tax_query ) > 1 ? $tax_query : array(),
			'meta_query'     => $meta_query,
			's'              => isset( $payload['search'] ) ? sanitize_text_field( $payload['search'] ) : '',
		);

		if ( isset( $payload['orderby'] ) ) {
			switch ( $payload['orderby'] ) {
				case 'popularity': $args['meta_key'] = 'total_sales'; $args['orderby'] = 'meta_value_num'; break;
				case 'rating': $args['meta_key'] = '_wc_average_rating'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
				case 'date': $args['orderby'] = 'date'; $args['order'] = 'DESC'; break;
				case 'price-desc': $args['meta_key'] = '_price'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
				case 'price': $args['meta_key'] = '_price'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'ASC'; break;
				default: $args['orderby'] = array( 'menu_order' => 'ASC', 'title' => 'ASC' );
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
			echo '<p>No products found.</p>';
		}
		wp_reset_postdata();

		wp_send_json_success(
			array(
				'html'       => ob_get_clean(),
				'max_pages'  => (int) $query->max_num_pages,
				'found'      => (int) $query->found_posts,
				'pagination' => paginate_links(
					array(
						'base'      => '#page/%#%',
						'format'    => '',
						'current'   => max( 1, $paged ),
						'total'     => max( 1, (int) $query->max_num_pages ),
						'type'      => 'array',
						'prev_text' => '&lsaquo;',
						'next_text' => '&rsaquo;',
					)
				),
			)
		);
	}
}
