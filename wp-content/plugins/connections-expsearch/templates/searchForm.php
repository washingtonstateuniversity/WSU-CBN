<?php
	global $connections;
	$atts				= get_transient( "atts" );
	$visiblefields		= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'visiable_search_fields' );
	$use_geolocation	= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'use_geolocation' );
	$radius				= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'radius' );
	$unit				= $connections->settings->get( 'connections_expsearch' , 'exp_defaults' , 'unit' );

	$searchValue		= ( get_query_var('cn-s') ) ? get_query_var('cn-s') : '';
	
?>

<div id="cn-form-container">
	<input type="hidden" value="<?=get_bloginfo('wpurl')?>" name="wpurl">
	<div id="cn-form-ajax-response"><ul></ul></div>
	<form id="cn-search-form" method="POST" enctype="multipart/form-data">
		<div class="row">
			<div class="twelve columns">
				<div class="row">
					<div class="six columns">
						<!-- non-meta search items -->

						<?php if(in_array('category',$visiblefields)):?>
							<?php 
								echo cnTemplatePartExended::flexSelect($connections->retrieve->categories(array('order'=>'parent ASC, name ASC')),array(
									'type'            => 'select',
									'group'           => FALSE,
									'default'         => __('Select a category', 'connections'),
									'label'           => __('Search by category', 'connections'),
									'show_select_all' => TRUE,
									'select_all'      => __('Any', 'connections'),
									'show_empty'      => FALSE,
									'show_count'      => FALSE,
									'depth'           => 0,
									'parent_id'       => array(),
									'exclude'         => array(),
									'return'          => TRUE,
									'class'				=>'search-select'
								));
							?>
							<hr/>
						<?php endif;?>		

						<?php if(in_array('region',$visiblefields)):?>
							<?php $display_code = $connections->settings->get('connections_form', 'exp_defaults', 'form_preference_regions_display_code'); ?> 
	
							<label class="search-select"><strong><?=__('Search by state' , 'connections_form' )?>:</strong></label><br/>
							<select name="cn-state" class="cn-state-select" id="cn-state" >
								<option value="" selected ><?=__('Any' , 'connections_form' )?></option>
								<?php foreach( cnGeo::getRegions() as $code => $regions ):?>
									<option value="<?=$code?>" ><?=( $display_code ? $code : $regions )?></option>
								<?php endforeach; ?> 
							</select>
							<hr/>
						<?php endif;?>						

						<?php if(in_array('country',$visiblefields)):?>
							<?php $display_code = $connections->settings->get('connections_form', 'exp_defaults', 'form_preference_countries_display_code'); ?> 
	
							<label class="search-select"><strong><?=__('Search by country' , 'connections_form' )?>:</strong></label><br/>
							<select name="cn-country" class="cn-country-select" id="cn-country" >
								<option value="" selected ><?=__('Any' , 'connections_form' )?></option>
								<?php foreach( cnGeo::getCountries() as $code => $country ):?>
									<option value="<?=$code?>" ><?=( $display_code ? $code : $country )?></option>
								<?php endforeach; ?> 
							</select>
							<hr/>
						<?php endif;?>
					</div>
					
					<div class="push_two four columns">
						<!-- meta search items -->
					
					</div>
				</div>
				
			<?php if(in_array('keywords',$visiblefields)):?>
				<div class="row">
					<div class="twelve columns">
						<label for="cn-s"><strong><?=__('Keywords' , 'connections_form' )?>:</strong></label><br/>
						<span class="cn-search" style="width:50%; display:inline-block">
							<input type="text" id="cn-search-input" name="cn-keyword" value="<?= esc_attr( $searchValue ) ?>" placeholder="<?= __('Search', 'connections') ?>"/>
						</span>
						<hr/>
					</div>
				</div>
			<?php endif;?>
	
			<?php if($use_geolocation):?>
				<div class="row">
					<div class="twelve columns"><?php //<!-- this is a little hardcoded.  Lets fix it --> ?>
						<h2><a id="mylocation" style="" class="button" hidefocus="true" href="#">[-]</a> <?=__('Search near my location' , 'connections_form' )?></h2>
						<input type="hidden" name="cn-near_addr" />
						<input type="hidden" name="cn-latitude" />
						<input type="hidden" name="cn-longitude" />
						<input type="hidden" name="cn-near-coord" />
						<input type="hidden" name="cn-radius" value="<?=$radius?>" />
						<input type="hidden" name="cn-unit" value="<?=$unit?>" />
					</div>
				</div>
			<?php endif;?>
			
			</div>
		</div>


		<div class="row">
			<div class="twelve columns">
				<hr/>
				<br/>
				<p class="cn-add">
					<input class="cn-button-shell cn-button red" id="cn-form-search" type="submit" name="start_search" value="<?=__('Submit' , 'connections_form' )?>" />
				</p>
				<br/>
			</div>
		</div>
	</form>
</div>
