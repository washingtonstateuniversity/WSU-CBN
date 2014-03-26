<?php
/**
 * The Header template for our theme
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
 
 $THEME_dir = get_template_directory_uri();
 
?><!DOCTYPE html>
<!--[if IE 7]><html class="ie ie7" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="ie ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<?php wp_head(); ?>    
</head>
<body <?php body_class(); ?>>
	<div id="body_wrap">
		<div class="">
			<header class="row">
				<div id="displayheader" class="twelve columns">
					<div class="row">
						<h1 class="two columns logo">
							<a href="http://alumni.wsu.edu/">
								<img src="<?=$THEME_dir?>/img/wsuaa-logo-w-trans.png" gumby-retina />
							</a>
						</h1>
					</div>
				</div>
				<div class=" twelve columns">
					<div class="row navbar" id="nav1">
						<a class="toggle" gumby-trigger="#nav1 > ul" href="#"><i class="icon-menu"></i></a>
						<?php wp_nav_menu( array( 'theme_location' => 'primary','container' => false,  'menu_class' => 'eight columns' ) ); ?>
					</div>
				</div>
			</header>
			<div role="main" id="main" class="twelve columns">
				<div id="main-content">