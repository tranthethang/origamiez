<?php
$sidebar = apply_filters('origamiez_get_current_sidebar', 'footer-1', 'footer-1');

if (is_active_sidebar($sidebar)):
	$classes = apply_filters('origamiez_get_footer_classes', array('col-xs-12', 'col-sm-6', 'col-md-3'), 'footer-1');
?>
	<div id="origamiez-footer-1" class="widget-area <?php echo esc_attr(implode(' ', $classes)); ?>" role="complementary">    
	    <?php dynamic_sidebar($sidebar); ?>    
	</div>
<?php
endif;