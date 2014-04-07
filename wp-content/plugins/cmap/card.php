<?php
//var_dump($atts);

$cnlevel= $entry->getMeta(array( 'key' => 'cnlevels', 'single' => TRUE ));


?>


<h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-accordion-icons  ui-corner-top ui-corner-all" aria-selected="false">

<a href='#'><em class="right"><?php
    if ( $atts['show_addresses'] ) $entry->getAddressBlock( array( 'format' => '%city%, %state%' ,'link' => array('locality'=>false,'region'=>false,'postal_code'=>false,'country'=>false) ) );
    ?></em>&nbsp;&nbsp;<?php $entry->getNameBlock( array( 'format' => '%first%', 'link' => false ) );
?></a>
</h3>
    
<div  id="cn-cmap"  class="cn-template cn-cmap ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" id="cn-list-body" role="tabpanel" aria-expanded="false" aria-hidden="true" style="display:none;">
<input type="hidden" name="cnlevel" value="<?=$cnlevel?>"/>
    <div id="cn-list"  class="cn-list">
        <div class="businesscontainer connections-list cn-clear" rel="">
        
            <div id="entry-id-<?php echo $entry->getRuid(); ?>" class="cn-entry" rel="<?php echo $entry->getRuid(); ?>">
                
				<?php
					$hasImg = false;
					$block=$entry->getImage( array(
						'image'    => $atts['image'] ,
						'preset' => 'thumbnail',
						'return' => TRUE,
						'fallback' => array(
							'type'     => $atts['image_fallback'] ,
							'string'   => $atts['str_image']
							)
						)
					);
					//$block = $entry->getImageNameOriginal();
					if(strpos($block,"cn-image-none")===false){	
						echo '<div class="cn-right" style="float:right">'.$block.'</div>';
						$hasImg = true;
					}
				?>
                
            
                <div class="<?php echo ($hasImg?"cn-left":"");?>">
                    <div style="margin-bottom: 5px;">
                        <h3> <?php $entry->getNameBlock( array( 'format' => $atts['name_format'], 'link' => $atts['link'] ) ); ?></h3>
                        <?php if ( $atts['show_title'] ) $entry->getTitleBlock(); ?>
                        <?php if ( $atts['show_org'] ) $entry->getOrgUnitBlock(); ?>
                        <?php if ( $atts['show_contact_name'] ) $entry->getContactNameBlock( array( 'format' => $atts['contact_name_format'] , 'label' => $atts['str_contact'] ) ); ?>
            
                    </div>
                    <?php
            
                    if ( $atts['show_addresses'] ) $entry->getAddressBlock( array( 'format' => $atts['addr_format'] , 'type' => $atts['address_types'] ) );
            
                    if ( $atts['show_phone_numbers'] ) $entry->getPhoneNumberBlock( array( 'format' => $atts['phone_format'] , 'type' => $atts['phone_types'] ) );
            
                    if ( $atts['show_email'] ) $entry->getEmailAddressBlock( array( 'format' => $atts['email_format'] , 'type' => $atts['email_types'] ) );
            
                    if ( $atts['show_im'] ) $entry->getImBlock();
            
                    if ( $atts['show_social_media'] ) $entry->getSocialMediaBlock();
            
                    if ( $atts['show_dates'] ) $entry->getDateBlock( array( 'format' => $atts['date_format'] ) );
            
                    if ( $atts['show_links'] ) $entry->getLinkBlock( array( 'format' => $atts['link_format'] ) );
            
                    if ( $atts['show_family'] )$entry->getFamilyMemberBlock();
            
                    ?>
                </div>
            
                <div class="cn-clear"></div>
            
		<?php	
			$metadata     = $entry->getMeta(array(
										'key' => 'cnbenefits',
										'single' => TRUE
									));
		/*
		$tmp=array(
				'description'=>'',
				'wsuaa_discounts'=>1,
				'categories'=>'',
				'online'=>0
			);	
			*/	
			
			if( !empty($metadata) && $metadata['description']!="" ):	
			?>
			<div class="cn-clear"><hr/>
				<h4>Benefits</h4>
				<?php if( $metadata['wsuaa_discounts']==1): ?><h5>For WSUAA Members only</h5><?php endif; ?>
				
				
				
				<p><?=$metadata['description']?></p>
				<?php if( isset($metadata['members_card']) && $metadata['members_card']==1): ?><h5>Must show card</h5><?php endif; ?>
				<hr/>
			</div>
			
			<?php endif; ?>
			
			
			
			
                <div class="cn-left">
            
                    <?php
            
                    if ( $atts['enable_bio'] && $entry->getBio() != '' ) {
            
                        printf( '<a class="cn-bio-anchor toggle-div ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="bio-anchor-%1$s" href="#" data-uuid="%1$s" data-div-id="bio-block-%1$s" data-str-show="%2$s" data-str-hide="%3$s"><span class="ui-button-text">%2$s</span></a>',
                            $entry->getRuid(),
                            $atts['str_bio_show'],
                            $atts['str_bio_hide']
                        );
                    }
            /*
                    if ( ( $atts['enable_note'] && $entry->getNotes() != '' ) && ( $atts['enable_bio'] && $entry->getBio() != '' ) ) {
                        echo ' | ';
                    }
            
                    if ( $atts['enable_note'] && $entry->getNotes() != '' ) {
            
                        printf( '<a class="cn-note-anchor toggle-div" id="note-anchor-%1$s" href="#" data-uuid="%1$s" data-div-id="note-block-%1$s" data-str-show="%2$s" data-str-hide="%3$s">%2$s</a>',
                            $entry->getRuid(),
                            $atts['str_note_show'],
                            $atts['str_note_hide']
                        );
                    }*/
            
                    ?>
                </div>
                <div class="cn-right">
                <?php
        
                if ( $atts['enable_map'] ) {
        
                    $gMap = $entry->getMapBlock( array(
                        'height' => $atts['map_frame_height'] ,
                        'width'  => ( $atts['map_frame_width'] ) ? $atts['map_frame_width'] : NULL ,
                        'return' => TRUE ,
                        'zoom'   => $atts['map_zoom']
                        )
                    );
        
                    if ( ! empty( $gMap ) ) {
        
                        $mapDiv = '<div class="cn-gmap" id="map-container-' . $entry->getRuid() . '" style="display: none;">' . $gMap . '</div>';
						
						//@todo come back to and clean this up
						$lookupaddy = $entry->getAddressBlock( array( 'format' => '%line1% %line2% %city% %state% %zipcode% %country%' ,
																	'link' => array('locality'=>false,'region'=>false,'postal_code'=>false,'country'=>false),
																	 'return'=>true
																	) );
        				$address = trim(strip_tags(str_replace('  ',' ',str_replace('<span class="type" style="display: none;">work</span>',
															'', str_replace('<span class="type" style="display: none;">home</span>',
															'', $lookupaddy)) ) ) );
                        printf( '<a class="cn-map-anchor toggle-map ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" id="map-anchor-%1$s" href="#" data-uuid="%1$s" data-str-show="%2$s" data-str-hide="%3$s" data-entryID="%6$d" data-memLevel="%7$s"><span class="ui-button-text">%2$s</span></a><span class="tolocation"> | <a class="cn-map-get-directions  ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" target="_blank" href="https://maps.google.com/maps?daddr=%4$s" data-uuid="%1$s"><span class="ui-button-text">%5$s</span></a></span>',
                            $entry->getRuid(),
                            $atts['str_map_show'],
                            $atts['str_map_hide'],
							urlencode( $address ),
                            'Get directions',
							$entry->getID(),
							$cnlevel
                        );
                    }
                }
        
                ?>


                   <!-- <span class="cn-return-to-top"><?php cnTemplatePart::returnToTop() ?></span>-->
                </div>
                <div class="cn-clear"></div>
                <?php 
					if ( $atts['enable_bio'] && $entry->getBio() != '' ) {
						echo '<div class="cn-bio" id="bio-block-' , $entry->getRuid() , '" style="display: none;">';
							if ( $atts['enable_bio_head'] ) echo '<h4>' , $atts['str_bio_head'] , '</h4>';
							$entry->getImage( array(
								'image'    => $atts['tray_image'] ,
								'height'   => $atts['tray_image_height'] ,
								'width'    => $atts['tray_image_width'] ,
								'fallback' => array(
									'type'     => $atts['tray_image_fallback'] ,
									'string'   => $atts['str_tray_image']
									)
								)
							);
							$entry->getBioBlock();
							echo '<div class="cn-clear"></div>';
						echo '</div>';
					}
				/*	if ( $atts['enable_note'] && $entry->getNotes() != '' ) {
				
						echo '<div class="cn-notes" id="note-block-' , $entry->getRuid() , '" style="display: none;">';
				
							if ( $atts['enable_note_head'] ) echo '<h4>' , $atts['str_note_head'] , '</h4>';
				
							$entry->getNotesBlock();
				
							echo '<div class="cn-clear"></div>';
				
						echo '</div>';
					}*/
                ?>
                <?php if ( isset($mapDiv) ) echo $mapDiv; ?>
            </div>
        </div>
    </div>
</div>