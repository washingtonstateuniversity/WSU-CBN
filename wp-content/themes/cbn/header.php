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
?><!DOCTYPE html>
<!--[if IE 7]><html class="ie ie7" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="ie ie8" <?php language_attributes(); ?>><![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<!--[if lt IE 9]><script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script><![endif]-->
	<?php wp_head(); ?>
    
    
	<script src="/content/themes/cbn/js/libs/modernizr-2.5.3.min.js"></script>
    <!--<link type="text/css" rel="stylesheet" href="/content/css/style.css" />-->
	<style  type="text/css">
        /*h2 { 
            color:#1B67B3;
        }*/ 
        h1, h2 {
            color:#800000;
        }
        .alert h5
        {
            color:red;
        }
        .ui-accordion .ui-accordion-header {
            /*position: static !important;*/
        }
        .businesscontainer{float:none !important;}
        .ui-accordion .ui-accordion-content-active {
            width:auto;
        }
    </style>
    <link type="text/css" rel="stylesheet" href="http://code.jquery.com/ui/1.8.22/themes/base/jquery-ui.css" media="all" />
		<link type='text/css' media='screen' href='http://images.wsu.edu/css/wsu_ui/jquery-ui-1.8.13.custom.css' rel='stylesheet' /> 
    
</head>
 <body <?php body_class(); ?>>
     <header>
        <h1><a href="http://alumni.wsu.edu/" id="wsuaa-logo">WSUAA Cougar Business Network</a><em><a href="/" id="cbn-logo"></a></em></h1>
    </header>

    <div role="main" id="main">
        <!--#parse("layouts/assests/nav.vm")-->
        
        
  	<nav role="navigation" id="nav">
    
    	<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
        <!--<ul>
        <li><a href="/">CBN Home</a></li> 
        <li><a href="showSearch.castle">Search CBN</a></li>
        <li><a href="register.castle">Register</a></li>
        <li><a href="http://alumni.wsu.edu/olc/pub/WHG/careersupport/main_careersupport_1.jsp">Career Support Home</a></li>
        <li class="last"><a href="http://alumni.wsu.edu/">WSUAA Home</a></li>
        </ul>-->
    </nav>
        
       <!-- 
        <nav id="site-navigation" class="navigation main-navigation" role="navigation">
            <h3 class="menu-toggle"><?php _e( 'Menu', 'twentythirteen' ); ?></h3>
            <a class="screen-reader-text skip-link" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentythirteen' ); ?>"><?php _e( 'Skip to content', 'twentythirteen' ); ?></a>
            
            
        </nav>-->
    	<div id="main-content">
 
 <!--
 
	<div id="page" class="hfeed site">
		<header id="masthead" class="site-header" role="banner">
			<a class="home-link" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
				<h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</a>

			<div id="navbar" class="navbar">
				<nav id="site-navigation" class="navigation main-navigation" role="navigation">
					<h3 class="menu-toggle"><?php _e( 'Menu', 'twentythirteen' ); ?></h3>
					<a class="screen-reader-text skip-link" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentythirteen' ); ?>"><?php _e( 'Skip to content', 'twentythirteen' ); ?></a>
					<?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
					<?php get_search_form(); ?>
				</nav>
			</div>>
		</header>

		<div id="main" class="site-main">
-->