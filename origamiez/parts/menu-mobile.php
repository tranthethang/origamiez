<?php

$menu_slug = has_nav_menu( 'mobile-nav' ) ? 'mobile-nav' : ( has_nav_menu( 'main-nav' ) ? 'main-nav' : false );
if ( $menu_slug ):
	?>
    <nav id="origamiez-mobile-nav" class="sb-slidebar sb-left sb-width-custom clearfix">
        <div id="origmiez-mobile-nav__toggle">
            <i class="fa fa-times"></i>
        </div>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => $menu_slug,
				'container'      => 'nav',
				'menu_id'        => 'mobile-menu',
				'menu_class'     => 'clearfix',
			)
		);
		?>
    </nav>
<?php
endif;