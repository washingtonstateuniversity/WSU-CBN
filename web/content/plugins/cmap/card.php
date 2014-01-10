<?php
//var_dump($atts);
?>

<div class="accordion"> 
    <h3 tabindex="0" class="ui-accordion-header ui-helper-reset ui-state-default ui-accordion-icons  ui-corner-top">
    <span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-s"></span>
    <a style="" hidefocus="true" href="#">
    <em class="right"><?php 
    if ( $atts['show_addresses'] ) $entry->getAddressBlock( array( 'format' => '%city%, %state%' ,'link' => array('locality'=>false,'region'=>false,'postal_code'=>false,'country'=>false) ) );
    
    ?></em>&nbsp;&nbsp;<?php $entry->getNameBlock( array( 'format' => '%first%', 'link' => false ) ); ?></a>
    </h3>
    
    
    <div class="businesscontainer ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" id="ui-accordion-1-panel-0" role="tabpanel" aria-expanded="true" aria-hidden="false" aria-labelledby="ui-accordion-1-header-0" style="display: block;" rel="533">
    
        <div id="entry-id-<?php echo $entry->getRuid(); ?>" class="cn-entry" rel="<?php echo $entry->getRuid(); ?>">
        
            <div class="cn-left">
        
                <?php
        
                $entry->getImage( array(
                    'image'    => $atts['image'] ,
                    'height'   => $atts['image_height'] ,
                    'width'    => $atts['image_width'] ,
                    'fallback' => array(
                        'type'     => $atts['image_fallback'] ,
                        'string'   => $atts['str_image']
                        )
                    )
                );
        
                ?>
        
            </div>
        
            <div class="cn-right">
        
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
        
            <div class="cn-left">
        
                <?php
        
                if ( $atts['enable_bio'] && $entry->getBio() != '' ) {
        
                    printf( '<a class="cn-bio-anchor toggle-div" id="bio-anchor-%1$s" href="#" data-uuid="%1$s" data-div-id="bio-block-%1$s" data-str-show="%2$s" data-str-hide="%3$s">%2$s</a>',
                        $entry->getRuid(),
                        $atts['str_bio_show'],
                        $atts['str_bio_hide']
                    );
                }
        
                if ( ( $atts['enable_note'] && $entry->getNotes() != '' ) && ( $atts['enable_bio'] && $entry->getBio() != '' ) ) {
                    echo ' | ';
                }
        
                if ( $atts['enable_note'] && $entry->getNotes() != '' ) {
        
                    printf( '<a class="cn-note-anchor toggle-div" id="note-anchor-%1$s" href="#" data-uuid="%1$s" data-div-id="note-block-%1$s" data-str-show="%2$s" data-str-hide="%3$s">%2$s</a>',
                        $entry->getRuid(),
                        $atts['str_note_show'],
                        $atts['str_note_hide']
                    );
                }
        
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
        
                        printf( '<a class="cn-map-anchor toggle-map" id="map-anchor-%1$s" href="#" data-uuid="%1$s" data-str-show="%2$s" data-str-hide="%3$s">%2$s</a> | ',
                            $entry->getRuid(),
                            $atts['str_map_show'],
                            $atts['str_map_hide']
                        );
                    }
                }
        
                ?>
        
                <span class="cn-return-to-top"><?php cnTemplatePart::returnToTop() ?></span>
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
        
            if ( $atts['enable_note'] && $entry->getNotes() != '' ) {
        
                echo '<div class="cn-notes" id="note-block-' , $entry->getRuid() , '" style="display: none;">';
        
                    if ( $atts['enable_note_head'] ) echo '<h4>' , $atts['str_note_head'] , '</h4>';
        
                    $entry->getNotesBlock();
        
                    echo '<div class="cn-clear"></div>';
        
                echo '</div>';
            }
        
            ?>
        
            <?php if ( isset($mapDiv) ) echo $mapDiv; ?>
        </div>
    </div>
</div>