<div id="entry-id-<?php echo $entry->getRuid(); ?>" class="cn-entry" style="height: <?php echo $atts['card_height']; ?>px; width: <?php echo $atts['card_width']; ?>px;">

	<?php if ( $atts['show_image'] ) { ?>

		<div style="float: left; margin-right: 10px;">

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

	<?php } ?>

	<div style="display: inline-block; width: <?php echo ( ( $atts['card_width'] - 20 ) / 2 ); ?>px;">

    	<div style="margin: 0 0 5px;">

			<?php $entry->getNameBlock( array( 'format' => $atts['name_format'], 'link' => $atts['link'] ) ); ?>
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

	<?php
		/*
		 * @TODO
		 * Using the preferred link would be the best, but the previous was to use the intial website address.
		 * For backward compatiblity if there is no preferred link this should output the intial link.
		 */
		$links = $entry->getWebsites();

		if ( ! empty( $links ) && $atts['enable_website_link'] )
		{
			echo '<div class="cn-card-link">';
				$website = $entry->getWebsites();
				if ($website[0]->address != NULL) echo '<a class="url" href="' , $website[0]->address , '" target="' , $website[0]->target , '"' , ( ( empty( $website[0]->followString ) ? '' : ' rel="' . $website[0]->followString . '"' ) ) , '>' , ( ( $atts['str_visit_website'] != 'Visit Website' ) ? $atts['str_visit_website'] : $website[0]->title ) , '</a>' , "\n";
			echo '</div>';
		}
	?>
</div>