<?php

class wsuwp_products__simpleads {
    


    /*-------------------------------------
     * method: __construct
     *
     * Overload of the default class instantiation.
     *
     */
    function __construct($params) {
        
        // Properties with default values
        //
        $this->columns = 1;                 // How many columns/row in our display output.
        
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /*-------------------------------------
     * method: display_products
     *
     * Legacy Panhandler stuff that will eventually come out.
     * This method generates the HTML that will be used to display
     * the product list in WordPress when it renders the page.
     *
     */
    function display_products($products) {
        $product_output[] = '';
        $moneyFormat = get_option($this->prefix.'-money_format');
        $linkModifiers = get_option($this->prefix.'-link_modifiers');
        $currCol = 0;        
        foreach ($products as $product) {
            
            // If we are on the first column, start a new row div
            //
            if ($currCol == 0) {
                $product_output[] = '<div class="'.$this->css_prefix.'-row">';
            }
            
            $product_output[] = "<div class=\"{$this->css_prefix}-product\">";
            $product_output[] = "<h3>{$product->name}</h3>";
            $product_output[] = "<div class=\"{$this->css_prefix}-left\">";
            $product_output[] = "<a href=\"{$product->web_urls[0]}\" target=\"csa\" $linkModifiers>";
            $product_output[] = "<img src=\"{$product->image_urls[0]}\" alt=\"{$product->name}\" title=\"{$product->name}\" />";
            $product_output[] = '</a><br/>';
            $product_output[] = '<div class="'.$this->css_prefix.'-zoombox">';
            $product_output[] = '<a class="thickbox" href="'.$product->image_urls[0].'">&nbsp;</a>';
            $product_output[] = '</div>';
            $product_output[] = '</div>';
            $product_output[] = '<div class="'.$this->css_prefix . '-right">';
            $product_output[] = '<p class="' . $this->css_prefix . '-desc" >'.$product->description.'</p>';
            $product_output[] = '<p class="' . $this->css_prefix . '-price">'.$product->currency;
            if (function_exists('money_format') &&  ($moneyFormat != '')) {
                $product_output[] =                    
                    "<a href=\"{$product->web_urls[0]}\" target=\"csa\" $linkModifiers>".
                    apply_filters($this->prefix.'_money_prefix','$') .
                    trim(money_format($moneyFormat, (float)$product->price)) .
                    '</a>';
            } else {
                $product_output[] =
                    "<a href=\"{$product->web_urls[0]}\" target=\"csa\">".
                    apply_filters($this->prefix.'_money_prefix','$') .
                    trim(number_format((float)$product->price, 2)) .
                    '</a>';
            }
            $product_output[] = '</p>';
            $product_output[] = '</div>';
            $product_output[] = '<div class="'.$this->css_prefix.'-cleanup"></div>';            
            $product_output[] = '</div>';
          
            // Move to the next column, if we already hit the max desired
            // output columns, close the row and get ready for a new one
            //
            $currCol++;            
            if ($currCol == $this->columns) {
                $currCol = 0;
                $product_output[] = '</div>';
            }
        }
        
        // We did not end output on the last column
        // so we need to close the row div
        //
        if ($currCol > 0) {
            $currCol = 0;
            $product_output[] = '</div>';
        }

        return implode($product_output);
    }    


}
