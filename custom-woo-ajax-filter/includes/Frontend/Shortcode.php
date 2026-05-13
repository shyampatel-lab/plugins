<?php
namespace CustomWooAjaxFilter\Frontend;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Shortcode {
	public function init(): void { add_shortcode( 'custom_product_filter', array( $this, 'render' ) ); }
	private function get_dynamic_range( string $source ): array {
		global $wpdb;
		$min = 0.0; $max = 100.0;
		if ( 'price' === $source ) {
			$r = $wpdb->get_row( "SELECT MIN(CAST(pm.meta_value AS DECIMAL(12,2))) min_value, MAX(CAST(pm.meta_value AS DECIMAL(12,2))) max_value FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id WHERE p.post_type='product' AND p.post_status='publish' AND pm.meta_key='_price'", ARRAY_A );
			if ( $r && null !== $r['min_value'] ) { $min = (float) $r['min_value']; $max = (float) $r['max_value']; }
		} elseif ( taxonomy_exists( $source ) ) {
			$r = $wpdb->get_row( $wpdb->prepare( "SELECT MIN(CAST(t.name AS DECIMAL(12,2))) min_value, MAX(CAST(t.name AS DECIMAL(12,2))) max_value FROM {$wpdb->terms} t INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id=tt.term_id INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id=tt.term_taxonomy_id INNER JOIN {$wpdb->posts} p ON p.ID=tr.object_id WHERE tt.taxonomy=%s AND p.post_type='product' AND p.post_status='publish' AND t.name REGEXP '^[0-9]+(\\.[0-9]+)?$'", $source ), ARRAY_A );
			if ( $r && null !== $r['min_value'] ) { $min = (float) $r['min_value']; $max = (float) $r['max_value']; }
		}
		if ( $max < $min ) { $max = $min; }
		return array( $min, $max );
	}
	public function render( array $atts ): string {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'custom_product_filter' ); $set_id=absint($atts['id']); $filters=(array)get_post_meta($set_id,'_cwaf_filters',true); wp_enqueue_style('cwaf-style'); wp_enqueue_script('cwaf-script'); ob_start(); ?>
		<div class="cwaf-root" data-relation="AND"><div class="cwaf-top-search"><input class="cwaf-live-search" type="search" name="search" placeholder="Zoeken naar producten..."><span class="cwaf-search-icon">⌕</span></div><div class="cwaf-meta-row"><span class="cwaf-results-count">0 results found</span></div><div class="cwaf-layout"><aside class="cwaf-sidebar"><div class="cwaf-selected"><h4>Selected filters:</h4><div class="cwaf-active-list">None</div></div><form class="cwaf-form">
		<?php foreach($filters as $f): $source=$f['source']??''; $type=$f['type']??'checkbox'; $label=$f['label']??$source; ?>
		<section class="cwaf-acc-item" data-open="1"><button type="button" class="cwaf-acc-head"><?php echo esc_html(strtoupper($label));?><span>⌄</span></button><div class="cwaf-acc-body">
		<?php if('range'===$type): list($min,$max)=$this->get_dynamic_range($source); ?><div class="cwaf-range" data-source="<?php echo esc_attr($source);?>"><div class="cwaf-range-values"><span class="minv"><?php echo esc_html($min);?></span> - <span class="maxv"><?php echo esc_html($max);?></span></div><div class="cwaf-dual cwaf-single"><input type="range" min="<?php echo esc_attr((string)$min);?>" max="<?php echo esc_attr((string)$max);?>" value="<?php echo esc_attr((string)$max);?>" step="<?php echo esc_attr((string)($f['step']??1));?>" data-bound="max" data-source="<?php echo esc_attr($source);?>"><div class="track"></div></div></div><?php endif; ?>
		<?php if($source && 'price'!==$source && in_array($type,array('checkbox','radio','dropdown'),true)): $terms=get_terms(array('taxonomy'=>$source,'hide_empty'=>true)); if(!is_wp_error($terms)): if('dropdown'===$type): ?><select name="<?php echo esc_attr($source);?>"><option value="">Any</option><?php foreach($terms as $t):?><option value="<?php echo esc_attr($t->slug);?>"><?php echo esc_html($t->name);?></option><?php endforeach;?></select><?php else: foreach($terms as $t):?><label><input type="<?php echo esc_attr('radio'===$type?'radio':'checkbox');?>" name="<?php echo esc_attr($source.('checkbox'===$type?'[]':''));?>" value="<?php echo esc_attr($t->slug);?>"> <?php echo esc_html($t->name);?></label><?php endforeach; endif; endif; endif; ?>
		</div></section><?php endforeach; ?></form></aside><main class="cwaf-main"><div class="cwaf-head"><h2>Products</h2><div class="cwaf-sort"><select name="orderby"><option value="date">Default</option><option value="price">Price</option><option value="title">A-Z</option></select></div></div><div class="cwaf-results"></div><div class="cwaf-pagination"></div></main></div></div>
		<?php return (string)ob_get_clean(); }
}
