<?php
namespace CustomWooAjaxFilter\Frontend;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Shortcode {
	public function init(): void { add_shortcode( 'custom_product_filter', array( $this, 'render' ) ); }
	private function get_dynamic_range( string $source ): array { global $wpdb; $min=0.0; $max=100.0; if('price'===$source){$r=$wpdb->get_row("SELECT MIN(CAST(pm.meta_value AS DECIMAL(12,2))) min_value, MAX(CAST(pm.meta_value AS DECIMAL(12,2))) max_value FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} pm ON p.ID=pm.post_id WHERE p.post_type='product' AND p.post_status='publish' AND pm.meta_key='_price'",ARRAY_A); if($r&&null!==$r['min_value']){$min=(float)$r['min_value'];$max=(float)$r['max_value'];}} if($max<$min){$max=$min;} return array($min,$max);} 
	
	private function render_hierarchical_terms( string $source, string $type ): string {
		$terms = get_terms( array( 'taxonomy' => $source, 'hide_empty' => true, 'parent' => 0 ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) { return ''; }
		ob_start();
		foreach ( $terms as $parent ) {
			echo '<div class="cwaf-cat-parent">' . esc_html( $parent->name ) . '</div>';
			$children = get_terms( array( 'taxonomy' => $source, 'hide_empty' => true, 'parent' => (int) $parent->term_id ) );
			foreach ( $children as $child ) {
				echo '<label class="cwaf-term-label cwaf-child"><input type="' . esc_attr( 'radio' === $type ? 'radio' : 'checkbox' ) . '" name="' . esc_attr( $source . ( 'checkbox' === $type ? '[]' : '' ) ) . '" value="' . esc_attr( $child->slug ) . '"> <span class="cwaf-term-name">→ ' . esc_html( $child->name ) . '</span> <span class="cwaf-term-count">' . esc_html( (string) $child->count ) . '</span></label>';
			}
		}
		return (string) ob_get_clean();
	}

	public function render( array $atts ): string {
		$atts=shortcode_atts(array('id'=>0),$atts,'custom_product_filter'); $set_id=absint($atts['id']); $filters=(array)get_post_meta($set_id,'_cwaf_filters',true); wp_enqueue_style('cwaf-style'); wp_enqueue_script('cwaf-script'); ob_start(); ?>
		<div class="cwaf-root" data-relation="AND"><div class="cwaf-top-search"><input class="cwaf-live-search" type="search" name="search" placeholder="Zoeken naar producten..."><span class="cwaf-search-icon">⌕</span></div><div class="cwaf-meta-row"><span class="cwaf-results-count">0 results found</span></div><div class="cwaf-applied-wrap"></div><div class="cwaf-layout"><aside class="cwaf-sidebar"><form class="cwaf-form">
		<?php foreach($filters as $f): $source=$f['source']??''; $type=$f['type']??'checkbox'; $label=$f['label']??$source; ?>
		<section class="cwaf-acc-item" data-open="1"><button type="button" class="cwaf-acc-head"><?php echo esc_html(strtoupper($label));?><span>⌄</span></button><div class="cwaf-acc-body">
		<?php if('range'===$type): list($min,$max)=$this->get_dynamic_range($source); ?><div class="cwaf-range" data-source="<?php echo esc_attr($source);?>"><div class="cwaf-range-values"><span class="minv"><?php echo esc_html($min);?></span> - <span class="maxv"><?php echo esc_html($max);?></span></div><div class="cwaf-dual"><input type="range" min="<?php echo esc_attr((string)$min);?>" max="<?php echo esc_attr((string)$max);?>" value="<?php echo esc_attr((string)$min);?>" step="<?php echo esc_attr((string)($f['step']??1));?>" data-bound="min" data-source="<?php echo esc_attr($source);?>"><input type="range" min="<?php echo esc_attr((string)$min);?>" max="<?php echo esc_attr((string)$max);?>" value="<?php echo esc_attr((string)$max);?>" step="<?php echo esc_attr((string)($f['step']??1));?>" data-bound="max" data-source="<?php echo esc_attr($source);?>"><div class="track"></div></div></div><?php endif; ?>
		<?php if($source && 'price'!==$source && in_array($type,array('checkbox','radio','dropdown'),true)): $terms=get_terms(array('taxonomy'=>$source,'hide_empty'=>true)); if(!is_wp_error($terms)): if('dropdown'===$type): ?><select name="<?php echo esc_attr($source);?>"><option value="">Any</option><?php foreach($terms as $t):?><option value="<?php echo esc_attr($t->slug);?>"><?php echo esc_html($t->name);?></option><?php endforeach;?></select><?php else: if('product_cat'===$source){ echo $this->render_hierarchical_terms($source,$type); } else { $idx=0; foreach($terms as $t): $hidden=$idx>=10?' cwaf-hidden-opt':''; ?><label class="cwaf-term-label<?php echo esc_attr($hidden);?>"><input type="<?php echo esc_attr('radio'===$type?'radio':'checkbox');?>" name="<?php echo esc_attr($source.('checkbox'===$type?'[]':''));?>" value="<?php echo esc_attr($t->slug);?>"> <span class="cwaf-term-name"><?php echo esc_html($t->name);?></span> <span class="cwaf-term-count"><?php echo esc_html((string)$t->count);?></span></label><?php $idx++; endforeach; if($idx>10): ?><button type="button" class="cwaf-show-more" data-state="more">Show more</button><?php endif; } endif; endif; endif; ?>
		</div></section><?php endforeach; ?></form></aside><main class="cwaf-main"><div class="cwaf-head"><h2>Products</h2><div class="cwaf-controls"><select name="orderby" class="cwaf-sortby"><option value="menu_order">Default</option><option value="popularity">Sort by popularity</option><option value="rating">Sort by rating</option><option value="date">Sort by latest</option><option value="price">Sort by price: low to high</option><option value="price-desc">Sort by price: high to low</option></select><select name="per_page" class="cwaf-per-page"><option value="10">10 per page</option><option value="20">20 per page</option><option value="30">30 per page</option><option value="40">40 per page</option><option value="50">50 per page</option></select></div></div><div class="cwaf-results"></div><div class="cwaf-pagination"></div></main></div></div>
		<?php return (string)ob_get_clean(); }
}
