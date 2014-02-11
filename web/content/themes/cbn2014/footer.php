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
    
    
    <!-- Adding jQuery UI
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
    
     -->
    <!--<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>-->
    

   
    
    
    <!-- <script type="text/javascript" src="/content/themes/cbn2014/js/markerclusterer.min.js"></script>
    <script type="text/javascript" src="/content/themes/cbn2014/js/jquery.ui.map.js"></script>
    <script type="text/javascript" src="/content/themes/cbn2014/js/infobox.js"></script>
    scripts concatenated and minified via build script 
    
    
    
    
    
    <script type="text/javascript" src="/content/themes/cbn2014/js/plugins.js"></script>
    
  -->









  <!-- Grab Google CDN's jQuery, fall back to local if offline -->
  <!-- 2.0 for modern browsers, 1.10 for .oldie -->
  <script>
  var oldieCheck = Boolean(document.getElementsByTagName('html')[0].className.match(/\soldie\s/g));
  if(!oldieCheck) {
    document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"><\/script>');
  } else {
    document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"><\/script>');
  }
  </script>
  <script>
  if(!window.jQuery) {
    if(!oldieCheck) {
      document.write('<script src="/content/themes/cbn2014/js/libs/jquery-2.0.2.min.js"><\/script>');
    } else {
      document.write('<script src="/content/themes/cbn2014/js/libs/jquery-1.10.1.min.js"><\/script>');
    }
  }
  </script>
  <script type="text/javascript" src="http://code.jquery.com/ui/1.10.1/jquery-ui.js" type="text/javascript"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/jquery.defaultvalue.js"></script>
  <!--
  Include gumby.js followed by UI modules followed by gumby.init.js
  Or concatenate and minify into a single file -->
  <script type="text/javascript" gumby-touch="js/libs" src="/content/themes/cbn2014/js/libs/gumby.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.retina.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.fixed.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.skiplink.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.toggleswitch.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.checkbox.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.radiobtn.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.tabs.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/gumby.navbar.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/ui/jquery.validation.js"></script>
  <script type="text/javascript" src="/content/themes/cbn2014/js/libs/gumby.init.js"></script>

  <!--
  Google's recommended deferred loading of JS
  gumby.min.js contains gumby.js, all UI modules and gumby.init.js
  <script type="text/javascript">
  function downloadJSAtOnload() {
  var element = document.createElement("script");
  element.src="/content/themes/cbn2014/js/libs/gumby.min.js";
  document.body.appendChild(element);
  }
  if (window.addEventListener)
  window.addEventListener("load", downloadJSAtOnload, false);
  else if (window.attachEvent)
  window.attachEvent("onload", downloadJSAtOnload);
  else window.onload = downloadJSAtOnload;
  </script> -->
<script type="text/javascript" src="/content/themes/cbn2014/js/ini.js"></script>
<script type="text/javascript" src="/content/themes/cbn2014/js/plugins.js"></script>
<script type="text/javascript" src="/content/themes/cbn2014/js/main.js"></script>
<script type="text/javascript" src="/content/themes/cbn2014/js/script.js"></script>

  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
     chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
  <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
  <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->














    
</body>
</html>