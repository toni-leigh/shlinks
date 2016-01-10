<?php    
    /*
         _get_value() - gets a value, first checking the post in the session (which will be set if there has been a form error thrown), then checking the $node array if this is an initial load
         @param array $node - the node to check, if there is no session post value then this is an initial load not an error load
         @param string $key - the array key to look at
         @return 
    */
    function get_value($node,$key)
    {
        $CI =& get_instance();
        
        if (is_array($CI->session->userdata('post')) ? $post=$CI->session->userdata('post') : $post=array() );
        $val='';
        
        if (isset($post[$key]))
        {
            $val=$post[$key];
        }
        else
        {
            if (isset($node[$key]))
            {
                $val=$node[$key];
            }
            else
            {
                $val='';
            }
        }
        
        // remove the value from the session now it has been reclaimed by a form
        unset($post[$key]);
        $CI->session->unset_userdata('post');
        $CI->session->set_userdata(array('post'=>$post));
        
        return $val;
    }
    /*
     converts a numerical value into a formatted pounds and pence value
    */
    function format_price($price)
    {
        return "&pound;".number_format($price,2);
    }
    function json_sanitise($in)
    {
        return utf8_encode(str_replace("'","",trim( preg_replace( '/\s+/', ' ', $in))));
    }

    /* *************************************************************************
        convert_urls() - finds URLs in text and converts them to clickable
        @param $text - the text to search
        @return $text - the converted text
    */
    function convert_urls($text)
    {
        // force http: on www.
        $text = ereg_replace( "www\.", "http://www.", $text );

        // eliminate duplicates after force
        $text = ereg_replace( "http://http://www\.", "http://www.", $text );
        $text = ereg_replace( "https://http://www\.", "https://www.", $text );

        // The Regular Expression filter
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";

        return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $text);
    }
?>