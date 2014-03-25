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
	<!--[if lt IE 9]><script src="<?=$THEME_dir?>/js/html5.js"></script><![endif]-->
    <script src="<?=$THEME_dir?>/js/libs/modernizr-2.5.3.min.js"></script>
    <link rel="stylesheet" href="<?=$THEME_dir?>/css/gumby.css">
    
    
	<?php wp_head(); ?>
    
    

    <!--<link type="text/css" rel="stylesheet" href="/wp-content/css/style.css" />-->
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
	<div id="body_wrap">
		<div class="row">
			<header class="row">
				<div id="displayheader" class="twelve columns row">
					<h1 class="two columns logo">
						<a href="#">
							<img src="<?=$THEME_dir?>/img/wsuaa-logo-w-trans.png" gumby-retina />
						</a>
					</h1>
				</div>
				<div class=" twelve columns row navbar" id="nav1">
					<a class="toggle" gumby-trigger="#nav1 > ul" href="#"><i class="icon-menu"></i></a>
					<?php wp_nav_menu( array( 'theme_location' => 'primary','container' => false,  'menu_class' => 'ten columns nav-menu' ) ); ?>
					<div class="two columns">
					</div>
				</div>
			</header>
			<div role="main" id="main" class="twelve columns">
				<div id="main-content">