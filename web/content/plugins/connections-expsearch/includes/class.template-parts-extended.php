<?php
if (!defined('ABSPATH'))
    exit;
class cnTemplatePartExended {
    public static function test() {
        return "hello cnTemplatePartExended ";
    }
    //note later
    public static function flexSelect($selectObj,$atts) {
        global $connections;
        $selected = array();
        if (get_query_var('cn-cat')) {
            $selected = get_query_var('cn-cat');
        } elseif (get_query_var('cn-cat-slug')) {
            // If the category slug is a descendant, use the last slug from the URL for the query.
            $queryCategorySlug = explode('/', get_query_var('cn-cat-slug'));
            if (isset($queryCategorySlug[count($queryCategorySlug) - 1]))
                $selected = $queryCategorySlug[count($queryCategorySlug) - 1];
        }
        // If value is a string, strip the white space and covert to an array.
        if (!is_array($selected)) {
            $selected = str_replace(' ', '', $selected);
            $selected = explode(',', $selected);
        }
        $level      = 1;
        $out        = '';
        $categories = $selectObj;
        $defaults   = array(
            'type' => 'select',
            'group' => FALSE,
            'default' => __('Select Category', 'connections'),
            'show_select_all' => TRUE,
            'select_all' => __('Show All Categories', 'connections'),
            'show_empty' => TRUE,
            'show_count' => FALSE,
            'depth' => 0,
            'parent_id' => array(),
            'exclude' => array(),
            'return' => FALSE,
			'onSelect' => FALSE
        );
        $atts       = wp_parse_args($atts, $defaults);
        if (!is_array($atts['parent_id'])) {
            // Trim extra whitespace.
            $atts['parent_id'] = trim(str_replace(' ', '', $atts['parent_id']));
            // Convert to array.
            $atts['parent_id'] = explode(',', $atts['parent_id']);
        }
        if (!is_array($atts['exclude'])) {
            // Trim extra whitespace.
            $atts['exclude'] = trim(str_replace(' ', '', $atts['exclude']));
            // Convert to array.
            $atts['exclude'] = explode(',', $atts['exclude']);
        }
		$out .= "\n" . '<label class="'.$atts['class'].'"><strong>'.$atts['label'].':</strong></label><br/>';
		
        $out .= "\n" . '<select class="cn-cat-select " name="'.(($atts['type'] == 'multiselect') ? 'cn-cat[]' : 'cn-cat').'" ' 
					. (($atts['type'] == 'multiselect') ? ' MULTIPLE ' : '') 
					. (($atts['type'] == 'multiselect'  || $atts['onSelect']==false ) ? '' : ' onchange="this.form.submit()" ') 
					. ' data-placeholder="' . esc_attr($atts['default']) . '" >';
        $out .= "\n" . '<option value=""></option>';
        if ($atts['show_select_all'])
            $out .= "\n" . '<option value="" selected >'.esc_attr($atts['select_all']).'</option>';
        foreach ($categories as $key => $category) {
            // Limit the category tree to only the supplied root parent categories.
            if (!empty($atts['parent_id']) && !in_array($category->term_id, $atts['parent_id']))
                continue;
            // Do not show the excluded category as options.
            if (!empty($atts['exclude']) && in_array($category->term_id, $atts['exclude']))
                continue;
            // If grouping by root parent is enabled, open the optiongroup tag.
            if ($atts['group'] && !empty($category->children))
                $out .= sprintf('<optgroup label="%1$s">', $category->name);
            // Call the recursive function to build the select options.
            $out .= self::categorySelectOption($category, $level, $atts['depth'], $selected, $atts);
            // If grouping by root parent is enabled, close the optiongroup tag.
            if ($atts['group'] && !empty($category->children))
                $out .= '</optgroup>' . "\n";
        }
        $out .= '</select>' . "\n";
        if ($atts['type'] == 'multiselect')
            $out .= self::submit(array(
                'return' => TRUE
            ));
        if ($atts['return'])
            return $out;
        echo $out;
    }
    /**
     * The private recursive function to build the select options.
     *
     * Accepted option for the $atts property are:
     *         group (bool) Whether or not to create option groups using the root parent as the group label. Used for select && multiselect only.
     *         show_empty (bool) Whether or not to display empty categories.
     *         show_count (bool) Whether or not to display the category count.
     *
     * @param object $category A category object.
     * @param int $level The current category level.
     * @param int $depth The depth limit.
     * @param array $selected An array of the selected category IDs / slugs.
     * @param array $atts
     * @return string
     */
    private static function categorySelectOption($category, $level, $depth, $selected, $atts) {
        $out      = '';
        $defaults = array(
            'group' => FALSE,
            'show_empty' => TRUE,
            'show_count' => TRUE,
            'exclude' => array()
        );
        $atts     = wp_parse_args($atts, $defaults);
        // Do not show the excluded category as options.
        if (!empty($atts['exclude']) && in_array($category->term_id, $atts['exclude']))
            return $out;
        // The padding in px to indent descendant categories. The 7px is the default pad applied in the CSS which must be taken in to account.
        $pad = ($level > 1) ? $level * 12 + 7 : 7;
        //$pad = str_repeat($atts['pad_char'], max(0, $level));
        // Set the option SELECT attribute if the category is one of the currently selected categories.
        if (is_array($selected)) {
            $strSelected = ((in_array($category->term_id, $selected)) || (in_array($category->slug, $selected))) ? ' SELECTED ' : '';
        } else {
            $strSelected = (($selected == $category->term_id) || ($selected == $category->slug)) ? ' SELECTED ' : '';
        }
        // $strSelected = $selected ? ' SELECTED ' : '';
        // Category count to be appended to the category name.
        $count = ($atts['show_count']) ? ' (' . $category->count . ')' : '';
        // If option grouping is TRUE, show only the select option if it is a descendant. The root parent was used as the option group label.
        if (($atts['group'] && $level > 1) && ($atts['show_empty'] || !empty($category->count) || !empty($category->children))) {
            $out .= sprintf('<option style="padding-left: %1$dpx !important" value="%2$s"%3$s>' . /*$pad .*/ $category->name . $count . '</option>', $pad, $category->term_id, $strSelected);
        }
        // If option grouping is FALSE, show the root parent and descendant options.
        elseif (!$atts['group'] && ($atts['show_empty'] || !empty($category->count) || !empty($category->children))) {
            $out .= sprintf('<option style="padding-left: %1$dpx !important" value="%2$s"%3$s>' . /*$pad .*/ $category->name . $count . '</option>', $pad, $category->term_id, $strSelected);
        }
        /*
         * Only show the descendants based on the following criteria:
         *         - There are descendant categories.
         *         - The descendant depth is < than the current $level
         *
         * When descendant depth is set to 0, show all descendants.
         * When descendant depth is set to < $level, call the recursive function.
         */
        if (!empty($category->children) && ($depth <= 0 ? -1 : $level) < $depth) {
            foreach ($category->children as $child) {
                $out .= self::categorySelectOption($child, $level + 1, $depth, $selected, $atts);
            }
        }
        return $out;
    }
}
?>

