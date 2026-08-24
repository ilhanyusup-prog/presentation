<?php
/**
 * The header for the theme.
 *
 * @package Weldman
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'weldman' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container site-header__inner">
			<div class="site-branding">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-text" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<button
				id="menu-toggle"
				class="menu-toggle"
				aria-controls="primary-menu"
				aria-expanded="false"
			>
				<span class="menu-toggle__bar"></span>
				<span class="menu-toggle__bar"></span>
				<span class="menu-toggle__bar"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'weldman' ); ?></span>
			</button>

			<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'weldman' ); ?>">
				<?php weldman_primary_nav(); ?>

				<?php if ( function_exists( 'pll_the_languages' ) ) : ?>
					<ul class="language-switcher">
						<?php
						echo pll_the_languages(
							array(
								'raw'                       => 0,
								'show_flags'                => 0,
								'show_names'                => 1,
								'display_names_as'          => 'slug',
								'force_home'                => 0,
								'echo'                      => 0,
							)
						);
						?>
					</ul>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<main id="content" class="site-content">
