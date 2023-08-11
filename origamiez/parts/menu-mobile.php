<?php

$menu_slug = has_nav_menu( 'mobile-nav' ) ? 'mobile-nav' : ( has_nav_menu( 'main-nav' ) ? 'main-nav' : false );
if ( $menu_slug ):
	?>
    <nav id="origamiez-mmenu">
		<?php
        // class="sb-slidebar sb-left sb-width-custom"
		wp_nav_menu(
			array(
				'theme_location' => $menu_slug,
				'container'      => false,
				'menu_id'        => 'mobile-menu',
				'menu_class'     => 'clearfix'
			)
		);
		?>
    </nav>
<?php
endif;