<?php
namespace CustomWooAjaxFilter\Frontend;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Shortcode {
	public function init(): void { add_shortcode( 'custom_product_filter', array( $this, 'render' ) ); }
	public function render( array $atts ): string {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'custom_product_filter' );
		$set_id = absint( $atts['id'] );
		$filters = (array) get_post_meta( $set_id, '_cwaf_filters', true );
		$layout = (string) get_post_meta( $set_id, '_cwaf_layout', true );
		$relation = (string) get_post_meta( $set_id, '_cwaf_relation', true );
		wp_enqueue_style( 'cwaf-style' ); wp_enqueue_script( 'cwaf-script' ); ob_start(); ?>
		<div class="cwaf-wrap cwaf-layout-<?php echo esc_attr( $layout ?: 'sidebar' ); ?>" data-relation="<?php echo esc_attr( $relation ?: 'AND' ); ?>"><form class="cwaf-form">
		<?php foreach ( $filters as $i => $filter ) : $source = $filter['source'] ?? ''; $type = $filter['type'] ?? 'checkbox'; ?>
		<div class="cwaf-filter-group"><h4><?php echo esc_html( $filter['label'] ?: ucfirst( str_replace( 'pa_', '', $source ) ) ); ?></h4>
		<?php if ( 'text' === $type ) : ?><input type="search" name="search" placeholder="Search products"><?php endif; ?>
		<?php if ( 'range' === $type ) : ?><input type="range" name="range_min_<?php echo esc_attr( (string) $i ); ?>" min="<?php echo esc_attr( (string) $filter['min'] ); ?>" max="<?php echo esc_attr( (string) $filter['max'] ); ?>" step="<?php echo esc_attr( (string) $filter['step'] ); ?>" value="<?php echo esc_attr( (string) $filter['min'] ); ?>" data-source="<?php echo esc_attr( $source ); ?>" data-bound="min"><input type="range" name="range_max_<?php echo esc_attr( (string) $i ); ?>" min="<?php echo esc_attr( (string) $filter['min'] ); ?>" max="<?php echo esc_attr( (string) $filter['max'] ); ?>" step="<?php echo esc_attr( (string) $filter['step'] ); ?>" value="<?php echo esc_attr( (string) $filter['max'] ); ?>" data-source="<?php echo esc_attr( $source ); ?>" data-bound="max"><?php endif; ?>
		<?php if ( $source && 'price' !== $source && in_array( $type, array( 'checkbox', 'radio', 'dropdown' ), true ) ) : $terms = get_terms( array( 'taxonomy' => $source, 'hide_empty' => true ) ); if ( ! is_wp_error( $terms ) ) : ?>
		<?php if ( 'dropdown' === $type ) : ?><select name="<?php echo esc_attr( $source ); ?>"><option value="">Any</option><?php foreach ( $terms as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?></select><?php else : foreach ( $terms as $term ) : ?><label><input type="<?php echo esc_attr( 'radio' === $type ? 'radio' : 'checkbox' ); ?>" name="<?php echo esc_attr( $source . ( 'checkbox' === $type ? '[]' : '' ) ); ?>" value="<?php echo esc_attr( $term->slug ); ?>"> <?php echo esc_html( $term->name ); ?></label><?php endforeach; endif; ?>
		<?php endif; endif; ?></div><?php endforeach; ?>
		<div class="cwaf-filter-group"><h4>Sort</h4><select name="orderby"><option value="date">Latest</option><option value="price">Price</option><option value="title">A-Z</option><option value="rating">Rating</option></select></div><button type="button" class="cwaf-clear">Clear All</button></form><div class="cwaf-results"></div></div>
		<?php return (string) ob_get_clean(); }
}
