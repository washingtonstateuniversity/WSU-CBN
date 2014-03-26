<div id="entry-id-<?php echo $entry->getRuid(); ?>" class="cn-entry-single">

	<div class="cn-left">

		<div style="margin-bottom: 5px; display:none;">
			<h3><?php $entry->getNameBlock( array( 'format' => $atts['name_format'], 'link' => $atts['link'] ) ); ?></h3>
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

	<div class="cn-right">

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

	<div class="cn-clear"></div>

	<?php

	if ( $atts['enable_bio'] && $entry->getBio() != '' ) {

		echo '<div class="cn-bio-single">';

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

		echo '<div class="cn-notes-single">';

			if ( $atts['enable_note_head'] ) echo '<h4>' , $atts['str_note_head'] , '</h4>';

			$entry->getNotesBlock();

			echo '<div class="cn-clear"></div>';

		echo '</div>';
	}

	if ( $atts['enable_map'] ) {

		$gMap = $entry->getMapBlock( array(
			'height' => $atts['map_frame_height'] ,
			'width'  => ( $atts['map_frame_width'] ) ? $atts['map_frame_width'] : NULL ,
			'return' => TRUE ,
			'zoom'   => $atts['map_zoom']
			)
		);

		if ( ! empty( $gMap ) )  $mapDiv = '<div class="cn-gmap-single" id="cn-gmap-single" data-gmap-id="' . $entry->getRuid() . '">' . $gMap . '</div>';

	}

	if ( isset($mapDiv) ) echo $mapDiv;

	?>
</div>