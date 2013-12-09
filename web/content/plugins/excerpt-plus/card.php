<?php
	$uuid = uniqid( $entry->getId(), FALSE );
?>

<div id="entry-id-<?php echo $uuid; ?>" class="cn-entry cn-accordion">

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

		if ( $atts['link'] ) {
			echo '<h3 style="border-bottom: ' , $atts['color'] , ' 1px solid; color:' , $atts['color'] , ';">' , $entry->getNameBlock( array( 'format' => $atts['name_format'], 'link' => $atts['link'] ) ) , '</h3>';
		} else {
			echo '<h3 class="cn-accordion-item" id="cn-accordion-item-' , $uuid , '" data-uuid="' , $uuid , '"' , ' style="border-bottom: ' , $atts['color'] , ' 1px solid; color:' , $atts['color'] , ';"><span class="cn-sprite" id="cn-toggle-' , $uuid , '" style="background-color: ' , $atts['color'] , ';"></span>' , $entry->getNameBlock( array( 'format' => $atts['name_format'], 'link' => $atts['link'] ) ) , '</h3>';
		}

		if ( $atts['show_title'] && $entry->getTitle() ) echo '<h4 class="title">' . $entry->getTitle() . '</h4>' . "\n";

		echo '<div class="cn-excerpt" id="cn-excerpt-' , $uuid , '">';

			echo strip_shortcodes( $entry->getExcerpt() );

			if ( $atts['link'] ) {
				echo '<span class="cn-link-more">' . cnURL::permalink( array( 'slug' => $entry->getSlug(), 'text' => $atts['str_read_more'], 'return' => TRUE ) ) . '</span>';
			} else {
				echo '<span class="cn-show-more" data-uuid="' . $uuid . '">' . $atts['str_read_more'] . '</span>';
			}

		echo '</div>';  // end cn-excerpt

		echo '<div class="cn-detail cn-hide" id="cn-detail-' , $uuid , '">';

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

				echo '<div class="cn-bio" id="cn-bio-' , $uuid , '">';

					if ( $atts['enable_bio_head'] ) echo '<h5>' , $atts['str_bio_head'] , '</h5>';

					echo $entry->getBioBlock();

				echo '</div>';  // end cn-bio
			}

			if ( $atts['enable_note'] && $entry->getNotes() != '' ) {

				echo '<div class="cn-notes" id="cn-bio-' , $uuid , '">';

					if ( $atts['enable_note_head'] )  echo '<h5>' , $atts['str_note_head'] , '</h5>';

					echo $entry->getNotesBlock();

				echo '</div>';  // cn-notes
			}

		echo '</div>';  // end cn-detail

	echo '</div>';  // end cn-right

	?>
</div>  <!-- end cn-entry -->