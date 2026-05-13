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
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'custom_product_filter'
		);

		$set_id     = absint( $atts['id'] );
		$taxonomies = (array) get_post_meta( $set_id, '_cwaf_taxonomies', true );

		wp_enqueue_style( 'cwaf-style' );
		wp_enqueue_script( 'cwaf-script' );

		ob_start();
		?>
		<div class="cwaf-wrap" data-set-id="<?php echo esc_attr( $set_id ); ?>">
			<form class="cwaf-form">
				<?php foreach ( $taxonomies as $taxonomy ) : ?>
					<?php $terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) ); ?>
					<?php if ( is_wp_error( $terms ) || empty( $terms ) ) { continue; } ?>
					<div class="cwaf-filter-group">
						<h4><?php echo esc_html( get_taxonomy( $taxonomy )->label ); ?></h4>
						<?php foreach ( $terms as $term ) : ?>
							<label><input type="checkbox" name="<?php echo esc_attr( $taxonomy ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>"> <?php echo esc_html( $term->name ); ?></label>
						<?php endforeach; ?>
					</div>
				<?php endforeach; ?>
				<button type="button" class="cwaf-clear"><?php esc_html_e( 'Clear All', 'custom-woo-ajax-filter' ); ?></button>
			</form>
			<div class="cwaf-results"></div>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}
