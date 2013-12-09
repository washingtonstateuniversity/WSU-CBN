<div class="cn-entry cn-accordion">

	<?php

	echo '<div class="cn-left">';

		$entry->getImage( array(
			'image'    => 'photo' ,
			'height'   => 120 ,
			'width'    => 100 ,
			'fallback' => array(
				'type'     => 'block' ,
				'string'   => 'No Photo Available'
				)
			)
		);

	echo '</div>';  // end cn-left

	echo '<div class="cn-right">';

		echo '<h3 style="border-bottom: ' , $atts['color'] , ' 1px solid; color:' , $atts['color'] , ';">' , $entry->getNameBlock() , '</h3>';

		if ( $atts['show_title'] && $entry->getTitle() ) echo '<h4 class="title">' . $entry->getTitle() . '</h4>' . "\n";

		echo '<div class="cn-detail">';

			if ( $atts['show_org'] ) $entry->getOrgUnitBlock();

			if ( $atts['show_contact_name'] )$entry->getContactNameBlock( array( 'format' => $atts['contact_name_format'] , 'label' => $atts['str_contact'] ) );

			if ( $atts['show_family'] )$entry->getFamilyMemberBlock();

			if ( $atts['show_addresses'] ) $entry->getAddressBlock( array( 'format' => $atts['addr_format'] , 'type' => $atts['address_types'] ) );

			if ( $atts['show_phone_numbers'] ) $entry->getPhoneNumberBlock( array( 'format' => $atts['phone_format'] , 'type' => $atts['phone_types'] ) );

			if ( $atts['show_email'] ) $entry->getEmailAddressBlock( array( 'format' => $atts['email_format'] , 'type' => $atts['email_types'] ) );

			if ( $atts['show_dates'] ) $entry->getDateBlock( array( 'format' => $atts['date_format'] ) );

			if ( $atts['show_links'] ) $entry->getLinkBlock( array( 'format' => $atts['link_format'] ) );

			if ( $atts['show_im'] ) echo $entry->getImBlock();

			if ( $atts['show_social_media'] ) echo $entry->getSocialMediaBlock();

			if ( $atts['enable_bio'] && $entry->getBio() != '' ) {

				echo '<div class="cn-bio">';

					if ( $atts['enable_bio_head'] ) echo '<h5>' , $atts['str_bio_head'] , '</h5>';

					echo $entry->getBioBlock();

				echo '</div>';  // end cn-bio
			}

			if ( $atts['enable_note'] && $entry->getNotes() != '' ) {

				echo '<div class="cn-notes">';

					if ( $atts['enable_note_head'] )  echo '<h5>' , $atts['str_note_head'] , '</h5>';

					echo $entry->getNotesBlock();

				echo '</div>';  // cn-notes
			}

		echo '</div>';  // end cn-detail

	echo '</div>';  // end cn-right

	?>
</div>  <!-- end cn-entry -->