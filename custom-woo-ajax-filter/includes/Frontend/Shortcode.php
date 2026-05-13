<?php

namespace CustomWooAjaxFilter\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {
	public function init(): void {
		add_shortcode( 'custom_product_filter', array( $this, 'render' ) );
	}

	public function render( array $atts ): string {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'custom_product_filter' );
		$set_id   = absint( $atts['id'] );
		$filters  = (array) get_post_meta( $set_id, '_cwaf_filters', true );
		$layout   = (string) get_post_meta( $set_id, '_cwaf_layout', true );
		$relation = (string) get_post_meta( $set_id, '_cwaf_relation', true );

		wp_enqueue_style( 'cwaf-style' );
		wp_enqueue_script( 'cwaf-script' );

		ob_start();
		?>
		<div class="cwaf-wrap cwaf-layout-<?php echo esc_attr( $layout ?: 'sidebar' ); ?>" data-set-id="<?php echo esc_attr( $set_id ); ?>" data-relation="<?php echo esc_attr( $relation ?: 'AND' ); ?>">
			<form class="cwaf-form">
				<?php foreach ( $filters as $filter ) : $taxonomy = $filter['taxonomy'] ?? ''; if ( ! $taxonomy ) { continue; } ?>
					<?php $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ); ?>
					<?php if ( is_wp_error( $terms ) || empty( $terms ) ) { continue; } ?>
					<div class="cwaf-filter-group">
						<h4><?php echo esc_html( $filter['label'] ?: get_taxonomy( $taxonomy )->label ); ?></h4>
						<?php if ( 'radio' === $filter['type'] ) : foreach ( $terms as $term ) : ?><label><input type="radio" name="<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo esc_attr( $term->slug ); ?>"> <?php echo esc_html( $term->name ); ?></label><?php endforeach; ?><?php endif; ?>
						<?php if ( 'dropdown' === $filter['type'] ) : ?><select name="<?php echo esc_attr( $taxonomy ); ?>"><option value=""><?php esc_html_e( 'Any', 'custom-woo-ajax-filter' ); ?></option><?php foreach ( $terms as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?></select><?php endif; ?>
						<?php if ( 'text' === $filter['type'] ) : ?><input type="search" name="search" placeholder="<?php esc_attr_e( 'Search products', 'custom-woo-ajax-filter' ); ?>"><?php endif; ?>
						<?php if ( ! in_array( $filter['type'], array( 'radio', 'dropdown', 'text' ), true ) ) : foreach ( $terms as $term ) : ?><label><input type="checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"> <?php echo esc_html( $term->name ); ?></label><?php endforeach; endif; ?>
					</div>
				<?php endforeach; ?>
				<div class="cwaf-filter-group"><h4><?php esc_html_e( 'Price', 'custom-woo-ajax-filter' ); ?></h4><input type="number" name="min_price" placeholder="Min"><input type="number" name="max_price" placeholder="Max"></div>
				<div class="cwaf-filter-group"><h4><?php esc_html_e( 'Sort', 'custom-woo-ajax-filter' ); ?></h4><select name="orderby"><option value="date"><?php esc_html_e( 'Latest', 'custom-woo-ajax-filter' ); ?></option><option value="price"><?php esc_html_e( 'Price', 'custom-woo-ajax-filter' ); ?></option><option value="title"><?php esc_html_e( 'A-Z', 'custom-woo-ajax-filter' ); ?></option><option value="rating"><?php esc_html_e( 'Rating', 'custom-woo-ajax-filter' ); ?></option></select></div>
				<button type="button" class="cwaf-clear"><?php esc_html_e( 'Clear All', 'custom-woo-ajax-filter' ); ?></button>
			</form><div class="cwaf-results"></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
