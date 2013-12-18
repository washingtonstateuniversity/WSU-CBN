<?php
class wsuwp_notifications__simpleads {

    /**
     * Build a new notification object.
     *
     * @param type $params
     */
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Add a notification to the notice stack
     *
     * @param type $level
     * @param type $content
     * @param type $link
     */
    function add_notice($level = 1, $content, $link = null) {
        $this->notices[] = new wsuwp_notifications_notice__simpleads(
            array(
                'level' => $level,
                'content' => $content,
                'link' => $link
            )
        );
    }

    /**
     * Render the notices to the browser page.
     */
    function display() {
        echo $this->get();
    }

   /**
    * Return a formatted HTML string representing the notification.
    *
    * @param boolean $simple - set to true to see simplified unformatted notices.
    * @return string - the HTML or simple string output
    */
   function get($simple=false) {

        // No need to do anything if there aren't any notices
        if (!isset($this->notices)) return;

        foreach ($this->notices as $notice) {
            $levels[$notice->level][] = $notice;
        }

        ksort($levels, SORT_NUMERIC);
        $difference = max(array_keys($levels));

        $notice_output = '';
        $actionMessage = __('needs attention',WSUWP__simpleads__VERSION);
        foreach ($levels as $key => $value) {
            if (!$simple) {
                $color = round($difference);
                switch ($difference) {
                case 1:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 60, 60);'>\n";
                    break;
                case 1:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 102, 0);'>\n";
                    break;
                case 4:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 204, 0);'>\n";
                    break;
                case 3:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 165, 104);'>\n";
                    break;
                case 2:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 165, 0);'>\n";
                    break;
                case 5:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(255, 201, 202);'>\n";
                    break;
                case 6:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(224, 255, 255);'>\n";
                    break;
                case 7:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(144, 238, 144);'>\n";
                    break;
                case 9:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(250, 250, 210);'>\n";
                    $actionMessage = __('wants you to know',WSUWP__simpleads__VERSION);
                    break;
                case 8:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(245, 222, 179);'>\n";
                    break;
                default:
                    $notice_output .= "<div id='{$this->prefix}_notice' class='updated fade'
                    style='background-color: rgb(245, 245, 220);'>\n";
                    break;
                }
                $notice_output .= sprintf(
                    __('<p><strong><a href="%s">%s</a> %s: </strong>',WSUWP__simpleads__VERSION),
                    $this->url,
                    $this->name,
                    $actionMessage
                );
                $notice_output .= "<ul>\n";
            }
            foreach ($value as $notice) {
                if (!$simple) { $notice_output .= '<li>'; }
                $notice_output .= $notice->display();
                if (!$simple) { $notice_output .= '</li>'; }
                $notice_output .= "\n";
            }
            if (!$simple) {
                $notice_output .= "</ul>\n";
                $notice_output .= "</p></div>\n";
            }
        }

        return $notice_output;
    }
}

/**
 * This class represents each individual notice.
 *
 */
class wsuwp_notifications_notice__simpleads {

    function __construct($params) {
        foreach($params as $name => $value) {
            $this->$name = $value;
        }
    }

    function display() {
        $retval = $this->content;
        if ( isset($this->link)     && 
             !is_null($this->link)  && 
             ($this->link != '')
            ) {
           $retval .= " (<a href=\"{$this->link}\">Details</a>)";
        }
        return $retval;
    }
}

?>
