<?php
/*
	a set of functions for converting database retrieved values into html snippets
	used by the data array model primarily
*/

/* *************************************************************************
    get_config() - gets the config array in helper context
    @param $item - the item to retrieve
    @return $config - array of config items
*/
function lookup_config($item)
{
    $config=array();

    $CI =& get_instance();

    $config=$CI->config->item($item);    

    return $config;
}

/* *************************************************************************
    backstage_toggle() - shows either a back stage or frontstage link depending 
        on page veiwed
    @param $admin - Boolen is this an admin page or not
    @param $user - the currently signed in user
    @return $backstage_toggle - array with link and label
*/
function backstage_toggle($admin,$user)
{
    $backstage_toggle=array(
    	'link'=>'',
    	'label'=>''
    );
    
    if (is_numeric($user['user_id']))
    {
    	if (1==$admin)
    	{
        	$backstage_toggle['link']="/";
        	$backstage_toggle['label']='front stage';
    	}
        else
        {
        	$redirect=lookup_config('redirect_signin');
            $backstage_toggle['link']="/".$redirect[$user['user_type']];
            $backstage_toggle['label']='back stage';
        }
    }

    return $backstage_toggle;
}

/* *************************************************************************
    body_classes() - gets a set of classes for the body tag
    @param $admin - Boolean is this an admin page or not
    @return $classes - string containing space delimited classes
*/
function body_classes($admin)
{
    $body_classes='';
	if (1==$admin)
	{
		$body_classes.="admin";
	}

    return $body_classes;
}

/* *************************************************************************
    get_body_map_onload() - gets an initialise function for the body tag
    @param $admin - Boolean is this an admin page or not
    @param $map_item_count - the number of items to display
    @param $map - whether or not the map is switched on at the node level
    @return $body_map_onload - string containing an initialise onload attribute
*/
function body_map_onload($admin,$map_item_count,$map)
{
    $body_map_onload="";

    $body_map_onload='';
	if ((1==$map && 1==$admin) or 
		($map_item_count && 0==$admin && 1==$map))
	{
		$body_map_onload.=" onload='initialise()'";
	}	    

    return $body_map_onload;
}

/* *************************************************************************
    css_files() - gets a list of link tags with css files in place
    @param $admin - Boolean is this an admin page or not
    @return $css_links - a string containing the links for output
*/
function css_files($admin)
{
    $css_links="";

    $css_config=lookup_config('css_files');
    
	if (is_array($css_config) &&
		count($css_config)>1)
	{			
		foreach ($css_config as $css)
		{
			$css_links.="<link rel='stylesheet' href='/style/".$css.".css'/>";
		}
	}
	else
	{
		$css_links="<link rel='stylesheet' href='/style/frontstage.css'/>";
	}
	
	// add admin css	
	if (1==$admin)
	{
		$css_links.="<link rel='stylesheet' href='/style/backstage.css'/>";
	}

    return $css_links;
}

/* *************************************************************************
    fonts() - gets the fonts as a piece of css with the IE fonts stored separately
        so non IE users don't download IE specific fonts
    @return $fonts - an array containing IE and none IE font strings
*/
function fonts()
{
    $fonts=array(
		'cb_fonts'=>'',
		'ie_fonts'=>''
	);

    $fonts_config=lookup_config('fonts');
    
	// iterate over all the fonts from the config file
	if (is_array($fonts_config))
	{
		foreach ($fonts_config as $font=>$types)
		{
			$fonts['cb_fonts'].="@font-face { font-family: ".ucfirst($font)."; src: url('/fonts/".$font.".".$types['cb_ext']."') format('".$types['cb_type']."'); }\n";
			$fonts['ie_fonts'].="@font-face { font-family: ".ucfirst($font)."; src: url('/fonts/".$font.".".$types['ie_ext']."') format('".$types['ie_type']."'); }\n";
		}
	}

    return $fonts;
}

/* *************************************************************************
    get_ie_tags() - gets the surrounding tags for internet explorer override
        styling
    @return $ie - array containing opening and closing tags
*/
function ie_tags()
{
    $ie=array();
    
	$ie['open']='';
	$ie['open'].="<!--[if IE 6]> <div id='ie6' class='ie ie6'> <![endif]-->";
	$ie['open'].="<!--[if IE 7]> <div id='ie7' class='ie ie7'> <![endif]-->";
	$ie['open'].="<!--[if IE 8]> <div id='ie8' class='ie ie8'> <![endif]-->";
	$ie['open'].="<!--[if IE 9]> <div id='ie9' class='ie ie9'> <![endif]-->";
	$ie['open'].="<!--[if IE 10]> <div id='ie10' class='ie ie10'> <![endif]-->";
	
	$ie['close']="<!--[if lte IE 10]></div><![endif]-->";

    return $ie;
}

/* *************************************************************************
    no_follow() - gets a no follow tag to avoid search engines crawling test server
    @return $no_follow - string containing the tag
*/
function no_follow()
{
    $no_follow="";
    
	if (1==lookup_config('dev'))
	{
		$no_follow="<meta name='ROBOTS' content='noindex, nofollow'>";
	}

    return $no_follow;
}