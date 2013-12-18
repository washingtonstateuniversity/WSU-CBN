<?php
class wsuwp_helper__simpleads {

    /**
     *
     * @param type $params
     */
    function __construct($params=null) {

        // Defaults
        //

        // Set by incoming parameters
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }

        // Override incoming parameters

    }


    /**
     * Executes the included php (or html) file and returns the output as a string.
     *
     * Parameters:
     * @param string $file - required fully qualified file name
     */
    function get_string_from_phpexec($file) {
        if (file_exists($file)) {
            ob_start();
            include($file);
            return ob_get_clean();
        }
    }
    
    
     
    /**
     *
     * Executes the a php file in ./templates/ file and prints out the results.
     *
     * Makes for easy include templates that depend on processing logic to be
     * dumped mid-stream into a WordPress page. 
     *
     * @param string $file - required file name in the ./templates directory
     * @param type $dir - optional directory path, defaults to plugin_dir_path
     */
    function execute_and_output_template($file,$dir=null) {
        if ($dir == null) {
            $dir = $this->parent->plugin_path;
        }
        print $this->get_string_from_phpexec($dir.'templates/'.$file);
    }
    
    
    

    /**
     * Convert text in the WP readme file format (wiki markup) to basic HTML
     *
     * Parameters:
     * @param string $file - optional name of the file in the plugin dir defaults to readme.txt
     * @param type $dir - optional directory path, defaults to plugin_dir_path
     */
    function convert_text_to_html($file='readme.txt',$dir=null) {
        if ($dir == null) {
            $dir = $this->parent->plugin_path;
        }
        ob_start();
        include($dir.$file);
        $content=ob_get_contents();
        ob_end_clean();
        $content=preg_replace('#\=\=\= #', "<h2>", $content);
        $content=preg_replace('# \=\=\=#', "</h2>", $content);
        $content=preg_replace('#\=\= #', "<div id='wphead' style='color:white'><h1 id='site-heading'><span id='site-title'>", $content);
        $content=preg_replace('# \=\=#', "</h1></span></div>", $content);
        $content=preg_replace('#\= #', "<b><u>", $content);
        $content=preg_replace('# \=#', "</u></b>", $content);
        $content=do_hyperlink($content);
        return nl2br($content);
    }
 



    /**
     * function: SavePostToOptionsTable
     */
    function SavePostToOptionsTable($optionname,$default=null) {
        if ($default != null) {
            if (!isset($_POST[$optionname])) {
                $_POST[$optionname] = $default;
            }
        }
        if (isset($_POST[$optionname])) {
            update_option($optionname,$_POST[$optionname]);
        }
    }

    /**************************************
     ** function: SaveCheckboxToDB
     **
     ** Update the checkbox setting in the database.
     **
     ** Parameters:
     **  $boxname (string, required) - the name of the checkbox (db option name)
     **  $prefix (string, optional) - defaults to SLPLUS_PREFIX, can be ''
     **/
    function SaveCheckboxToDB($boxname,$prefix = null, $separator='-') {
        if ($prefix === null) { $prefix = $this->parent->prefix; }
        $whichbox = $prefix.$separator.$boxname;
        $_POST[$whichbox] = isset($_POST[$whichbox])?1:0;
        $this->SavePostToOptionsTable($whichbox,0);
    }

    /**
     * Saves a textbox from an option input form to the options table.
     *
     * @param string $boxname - base name of the option
     * @param string $prefix - the plugin prefix
     * @param string $separator - the separator char
     */
    function SaveTextboxToDB($boxname,$prefix = null, $separator='-') {
        if ($prefix === null) { $prefix = $this->parent->prefix; }
        $whichbox = $prefix.$separator.$boxname;
        $this->SavePostToOptionsTable($whichbox);
    }


}
