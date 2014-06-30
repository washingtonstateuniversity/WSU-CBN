<?php
	global $connections;
	$results = get_transient( "results" );
	$atts = get_transient( "atts" );
	$categories = $connections->retrieve->categories();
	
	if(empty($results)){
		echo __('No results' , 'connections_form' );
		return;
	}
	
	$location_posted=isset($_POST['location_alert']) ? $_POST['location_alert'] : false;
	
	$json_basedir = CN_IMAGE_PATH . "/tmps/json/";
	if(!file_exists($json_basedir)) mkdir($json_basedir,0777,true);

	$hash=md5(serialize($results));
	$json_file= $json_basedir."${hash}.tmp";
	if(!file_exists($json_file)){		
		$markers = new stdClass();
		$markers->markers=array();
		foreach($results as $entry){
			$entryObj=new stdClass();
			$entryObj->id=$entry->id;
			$entryObj->title= $entry->organization;
			$entryObj->position=new stdClass();
			$addy = unserialize ($entry->addresses);
			$array = (array) $addy;
			$addy = array_pop($addy);
			if(!empty($addy['latitude']) && !empty($addy['longitude'])){
				$entryObj->position->latitude=$addy['latitude'];
				$entryObj->position->longitude=$addy['longitude'];
				$markers->markers[]= $entryObj;
			}
		}
		$markerJson=json_encode($markers);
		file_put_contents($json_file, $markerJson, LOCK_EX);
	}else{
		$markerJson = file_get_contents($json_file);
	}	
	$out = "";
		
?>
<div id="tabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all" rel="<?=($location_posted?"location_posted":"")?>">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		
		<li class="ui-state-default ui-corner-top ui-tabs-selected ui-state-active"><a href="#tabs-2"><?=__('Listings' , 'connections_form' )?></a></li>
		<li class="ui-state-default ui-corner-top"><a href="#tabs-1"><?=__('Map' , 'connections_form' )?></a></li>
	</ul>

	<div id="tabs-2" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
		<?php if($atts['category']==NULL){
			$state = isset($_POST['cn-state']) ? $_POST['cn-state'] : "";
			$state_message = !empty($state)?' in '.$state:'';
			foreach($categories as $cat){
				$cat_id=$cat->term_id;
				$atts['category']=$cat_id;
				
				$cat_basedir = CN_IMAGE_PATH . "/tmps/cats/${cat_id}/";
				if(!file_exists($cat_basedir)) mkdir($cat_basedir,0777,true);
				
				
				$cat_file= $cat_basedir."${cat_id}_${state}_${hash}.tmp";
				if(!file_exists($cat_file)){	
					
					ob_start();
					
					
					$catblocks = array();
					foreach($results as $result){
						$id = $result->id;
						$basedir = CN_IMAGE_PATH . "/tmps/id/${id}/";
						if(!file_exists($basedir)) mkdir($basedir,0777,true);
						$file= $basedir."${id}_${cat_id}_${state}.tmp";
						if(!file_exists($file)){							
							$atts['id'] = $result->id;
							$block = connectionsList( $atts,NULL,'connections' );
							if(strpos($block,'No results')!==false){
								$block="";
							}
							file_put_contents($file, $block, LOCK_EX);
						}else{
							$block = file_get_contents($file);
						}
						if(!empty($block) && strpos($block,'No results')===false){
							$catblocks[] = $block;
						}
					}
					
					if(count($catblocks)>0){
						//var_dump($catblock);
						
						?>
						<h2><?=$cat->name.$state_message?></h2>
						
							<?php
							$e=0;
							
							$mem_resulting="";
							foreach($catblocks as $catblock){
								if(strpos($catblock,'<input type="hidden" name="cnlevel" value="member"/>')>0){	
									$e++;			
									$mem_resulting .= $catblock;
								}
							}
							
							if($e>0){
								?><h4>CBN Members</h4><?php
								?><div class="accordion"><?php
									echo $mem_resulting;
								?></div><?php
							}
							//CBN Affiliates
							$e=0;
							$aff_resulting="";
							foreach($catblocks as $catblock){
								if(strpos($catblock,'<input type="hidden" name="cnlevel" value="member"/>')===false){	
									$e++;			
									$aff_resulting .= $catblock;
								}
	
							}
							if($e>0){
								?><h4>CBN Affiliates</h4><?php
								?><div class="accordion"><?php
									echo $aff_resulting;
								?></div><?php
							}
							?>
						<?php	
					}
					
					$HTML = ob_get_clean();
					if(strpos($HTML,'No results')!==false){
						$HTML = "";
					}
					file_put_contents($cat_file, $HTML, LOCK_EX);
				}else{
					$HTML = file_get_contents($cat_file);
				}	
				echo $HTML;
			}
		}else{
			$state = isset($_POST['cn-state']) && !empty($_POST['cn-state'])?$_POST['cn-state'].' and ':'';
			$category = $connections->retrieve->category($atts['category']);
			?>
			<h2><?=$state.$category->name?></h2>
			<div class="accordion ui-accordion ui-widget ui-helper-reset">
				<?=connectionsList( $atts,NULL,'connections' )?>
			</div>
			<?php
		}
		?>

	</div>
	<div id="tabs-1" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
		<h2><?=__('Hover on a point to find a business and click for more information' , 'connections_form' )?></h2>
		<div id="mapJson"><?=$markerJson?></div>
		<div id="front_cbn_map" class="byState " rel="<?=(isset($_POST['cn-state'])?$_POST['cn-state']:"")?>" style="width:100%;height:450px;"></div>
		<div class="ui-widget-content ui-corner-bottom" style="padding:5px 15px;">
			<div id="data_display"></div>
			<div style="clear:both;"></div>
		</div>
	</div>
</div>



