<?php

class simpleadsWidget extends WP_Widget {

    var $simpleads;

	public function __construct() {
        $this->simpleads = $GLOBALS['SimpleAds'];

		parent::__construct(
	 		$this->simpleads->prefix.'_widget', // Base ID
			__('SimpleAds Ad',$this->simpleads->prefix), // Name
			array( 
			    'description' => __( 'Add an SimpleAds ad to any widget box location.', $this->simpleads->prefix ), 
			    ) 
		);
	}

 	public function form( $instance ) {
 	    print __('Enter the ID or shorthand code for an ad.', $this->simpleads->prefix);
		print $this->formatFormEntry($instance, 'id'        , __( 'Ad ID:', $this->simpleads->prefix)           ,''); 
		print $this->formatFormEntry($instance, 'shorthand' , __( 'Ad Shorthand:', $this->simpleads->prefix)    ,''); 
    }

	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	public function widget( $args, $instance ) {
        $this->simpleads->is_widget = true;
		print apply_filters($this->simpleads->prefix."Render", $instance);
	}
	
	private function formatFormEntry($instance, $id,$label,$default) {
	    $fldID = $this->get_field_id( $id );
	    $content= '<p>'.
            '<label for="'.$fldID.'">'.$label.'</label>'. 
            '<input class="widefat" type="text" '.  
                'id="'      .$fldID                                                     .'" '. 
                'name="'    .$this->get_field_name( $id )                               .'" '. 
                'value="'   .esc_attr( isset($instance[$id])?$instance[$id]:$default )  .'" '. 
                '/>'.
             '</p>';
        return $content;             
	}
	
}
