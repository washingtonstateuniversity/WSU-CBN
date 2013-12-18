<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
?>		
		<?php //get_search_form(); ?>
		<?php wp_footer(); ?>
		</div><!-- #main -->
		<!--<footer id="colophon" class="site-footer" role="contentinfo">
			<?php get_sidebar( 'main' ); ?>

			<div class="site-info">
				<?php do_action( 'twentythirteen_credits' ); ?>
				<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'twentythirteen' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'twentythirteen' ); ?>"><?php printf( __( 'Proudly powered by %s', 'twentythirteen' ), 'WordPress' ); ?></a>
			</div>
		</footer>-->
	</div><!-- #main-content -->

	
    <footer id="footer">
        <div id="social-icons">
            <ul>
                <li id="Twitter"><a href="https://twitter.com/WSUCougarPride" title="Twitter">Twitter</a></li>
                <li id="Facebook"><a href="https://www.facebook.com/WSUAA" title="Facebook">Facebook</a></li>
                <li id="Pinterest"><a href="http://pinterest.com/wsuaa/" title="Pinterest">Pinterest</a></li>
                <li id="LinkedIn"><a href="http://www.linkedin.com/groups?gid=35258" title="LinkedIn">LinkedIn</a></li>
                <li id="Join-WSUAA"><a href="https://secure.alumni.wsu.edu/olc/pub/WHG/membershipform/showGivingForm.jsp?form_id=117412" title="Join WSUAA">Join WSUAA</a></li>
            </ul>
        </div><!--#social-icons-->
            
        <div id="footer-crimson" >
            
            <div id="footer-bkgrd">
                <a href="http://alumni.wsu.edu/">Washington State University Alumni Association</a> | <a href="http://wsu.edu/">Washington State University</a>, PO Box 646150, Pullman, WA 99164-6150 | <a href="http://alumni.wsu.edu/olc/pub/WHG/volunteer/page_volunteer_1.jsp">Contact Us</a><br>
                <span class="small-text">
                    Copyright &copy; 2012 Board of Regents, Washington State University | <a href="http://access.wsu.edu/">Accessibility</a> | <a href="http://policies.wsu.edu/">Policies</a> | <a href="http://publishing.wsu.edu/copyright/WSU.html">Copyright</a> 
                </span><!--.small-text-->
            </div><!--#footer-bkgrd-->
        </div>
        
    </footer>   
    
    
    <!-- Adding jQuery UI -->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    
    
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
    
    <script src="http://code.jquery.com/ui/1.10.1/jquery-ui.js" type="text/javascript"></script>
    
    <script type="text/javascript" src="/content/themes/cbn/js/jquery.defaultvalue.js"></script>
    
    
    <script type="text/javascript" src="/content/themes/cbn/js/markerclusterer.min.js"></script>
    <script type="text/javascript" src="/content/themes/cbn/js/jquery.ui.map.js"></script>
    <script type="text/javascript" src="/content/themes/cbn/js/infobox.js"></script>
    <!-- scripts concatenated and minified via build script   -->
    <script type="text/javascript" src="/content/themes/cbn/js/ini.js"></script>
    
    
    
    
    <script type="text/javascript" src="/content/themes/cbn/js/plugins.js"></script>
    <script type="text/javascript" src="/content/themes/cbn/js/script.js"></script>
    
</body>
</html>