<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 ADMIN NAV - appears on all pages, so permissions here
*/
    // permissions, this should reflect the db, but due to the nature of the admin nav it is easier to
    // use config to set this
        $config['admin_nav_permissions']=array(
            'page'=>array('super_admin'),
            'blog'=>array('super_admin','supplier_user','signedup_user'),
            'seoarticle'=>array('super_admin'),
            'user'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'group'=>array('super_admin'),
            'all_images'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'order_list'=>array('super_admin','supplier_user','customer_user'),
            'product'=>array('super_admin','supplier_user'),
            'voucher'=>array('super_admin','supplier_user'),
            'postage'=>array('super_admin','supplier_user'),
            'variation'=>array('super_admin','supplier_user'),
            'calendar'=>array('super_admin'),
            'event'=>array('super_admin'),
            'newsletter'=>array('super_admin'),
            'misc'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'credentials'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'contacts'=>array('super_admin','admin_user')
        );

/*
 BROWSER
*/
    // js / old browser related config
        $config['html_canvas']=0;
        $config['html5shiv']=0;
        $config['modernizr']=0;

/*
 CSS DECORATIVE
*/
    // background image - set to empty string to remove
        $config['background']="background-image:url('/img/background.png'); background-position:top center;";

    // colours
        $config['colours']=array(
            'dark_colour'=>'#181818',
            'light_colour'=>'#dedede',
            'error_colour'=>'#800000',
            'error_bg'=>'#f0a0a0',
            'field_border'=>'#181818',
            'field_bg'=>'#ffffff',
            'field_focus_border'=>'#ff7c00',
            'field_focus_bg'=>'#ffffff'
        );

    // files
        $config['css_files']=array('style');

    // fonts
        $config['fonts']=array(
            'Rationale-Regular-kRB'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),
              'jura-book-kRB'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),
              'jura-demi-bold-kRB'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),
              'enigma2-webfont'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),
              'kameron-regular-webfont'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),

            // open type and IE
                //'enigma2-webfont'=>array('cb_ext'=>'oft','cb_type'=>'open_type','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),

            // true type and IE
                //'kameron-regular-webfont'=>array('cb_ext'=>'ttf','cb_type'=>'truetype','ie_ext'=>'eot?#iefix','ie_type'=>'eot'),

            // woff (USE THIS IF POSSIBLE) and IE
                //'<FONT_NAME>'=>array('cb_ext'=>'woff','cb_type'=>'woff','ie_ext'=>'eot?#iefix','ie_type'=>'eot')
        );

/*
 DEVELOPMENT OUTPUT CONFIG
*/
    // 404 output
        $config['dev_404']=0;

/*
  EXTERNAL CONFIG - google analytics, facebook etc.
*/
    // facebook
        $config['fbadmin']='568870659';

    // feedburner for blog
        $config['feedburner_link']="";

    // google analytics
        $config['ga']='#TEST_GA_ID';

/*
  E-COMMERCE
*/
    // always show payment button - sometimes for style reasons we show a deactivated payment button when no products are bagged
        $config['always_show_pay_button']=0;
        $config['pay_in_header_basket']=0;
        $config['mediaset_show_tabs']=0;

    // header basket stuff - add an image or text link in to have a link from the header to the basket
    // also define whether certain numerical basket outputs are shown
        $config['show_header_basket']=1;
        $config['basket_link']="";
        $config['hbasket_prod']=1;
        $config['hbasket_postage']=1;
        $config['hbasket_count']=1;
        $config['hbasket_pay']=1;
        $config['hbasket_total_text']="total";

    // payment live
        $config['payment_live']='TEST';

    // payment processing
        $config['payment_processor']='worldpay';

        $config['paypal_vendor']='alysoun@lovevideotours.com';
        $config['paypal_currency']='GBP';
        $config['paypal_button_text']='pay with paypal';

        $config['sage_pay']='https://test.sagepay.com/Simulator/VSPServerGateway.asp?Service=VendorRegisterTx';

        $config['worldpay_mode']=100; // worldpay test mode, 0 for live, 1+ for test
        $config['worldpay']="https://select-test.wp3.worldpay.com/wcc/purchase";
        $config['worldpay_currency']='GBP';

        $config['zkey']="";
        $config['zfolder']="members";
        $config['zsite_id']="";
        $config['zrecurr_id']="";
        $config['zcredits_id']="";

/*
 ENGAGE CONFIG
*/
    // login redirects
        $config['redirect_signin']=array(
            'designer'=>'%_USERNAME/all',
            'developer'=>'%_USERNAME/all',
            'seo'=>'%_USERNAME/all',
            'signedup_user'=>'%_USERNAME/all',
            'super_admin'=>'%_USERNAME/all'
        );

    // logout redirect
        $config['logout_url']='login';

    // profile completion encouragement - do we want to encourage profiles to be completed (yes for communities)
        $config['welcome_encouragement']=0;

/*
 EVENTS
*/
    // booking button text
        $config['bookbut_text']='set in config';

    // full month name on output
        $config['full_month_names']=1;

/*
 IMAGE CONFIGS
*/
    // allowed types for upload
        $config['image_allowed_types']='gif|jpg|png';

    // default images
        $config['default_image_large']='/img/default_image_large.png';
        $config['default_image']='/img/default_image.png';
        $config['default_image_small']='/img/default_image_small.png';

    // image edit link (front or back, where the back stage image edit link goes)
        $config['image_upload_link']='back';

    // sizes
        // !! bewary of changing default sizes when updating a current template site as images will have been saved with the old config values and not necessarily the new ones !! //
        // these four scales cannot be changed, they are prepared for different mobile screen sizes
         $config['scale_sizes']=array(940,740,460,300);
        $config['thumb_sizes']=array(40,100,200,300,460);
        $config['base_image_width']=940; // NB this is just for upload back office functionality
        $config['display_individual_width']=940; // needs to be one of the scale sizes, this is just for the front end image display, important to leave it separate from the last value

/*
 MAP
*/
    $config['map_centre']=array(
        'latitude'=>54.8,
        'longitude'=>-3.0
    );

    $config['map_options']=array(
        'zoom'=>7,
        'mapTypeId'=>'google.maps.MapTypeId.ROADMAP',
        'scrollwheel'=>'false'
    );

    $config['map_types']=array(
    );

    $config['map_qs']='?key=SET_ME&sensor=false';

/*
 MOBILE
*/
    // mobile site specifically designed then don't squash the site
        $config['mobile_site_design']=0;

/*
 NODE RELATED - CONFIGS BASED ON TYPE
*/
    // stream actions
        // 1 = singular
        // n = plural
        $config['stream_actions']=array(
            0=>array(
                '1'=>'added',
                'n'=>'added'
            ),
            1=>array(
                '1'=>'added by',
                'n'=>'added by'
            ),
            2=>array(
                '1'=>'updated',
                'n'=>'updated %_COUNT times'
            ),
            3=>array(
                '1'=>'updated by',
                'n'=>'updated %_COUNT times by'
            ),
            4=>array(
                '1'=>'added image to',
                'n'=>'added %_COUNT images to'
            ),
            5=>array(
                '1'=>'image added by',
                'n'=>'%_COUNT images added by'
            ),
            6=>array(
                '1'=>'commented on',
                'n'=>'commented on'
            ),
            7=>array(
                '1'=>'commented on by',
                'n'=>'commented on by'
            ),
            8=>array(
                '1'=>'befriended',
                'n'=>'befriended'
            ),
            9=>array(
                '1'=>'is joined by',
                'n'=>'is joined by'
            ),
            10=>array(
                '1'=>'joins',
                'n'=>'joins'
            ),
            11=>array(
                '1'=>'added',
                'n'=>'added %_COUNT'
            ),
            12=>array(
                '1'=>'receives',
                'n'=>'recieves %_COUNT'
            ),
            13=>array(
                '1'=>'voted up',
                'n'=>'voted %_COUNT up'
            ),
            14=>array(
                '1'=>'was voted up',
                'n'=>'was voted up %_COUNT times'
            ),
            15=>array(
                '1'=>'voted down',
                'n'=>'voted %_COUNT down'
            ),
            16=>array(
                '1'=>'was voted down',
                'n'=>'was voted down %_COUNT times'
            )
        );

    // which tabs (first is default tab)
        $config['tabs']=array(
            'blog'=>array('details','images','stream'),
            'calendar'=>array('details','images','stream'),
            'category'=>array('links','activity'),
            'event'=>array('details','images','members','stream'),
            'group'=>array('details','images','members','stream','messages'),
            'link'=>array('details','images','stream'),
            'page'=>array('details','images','stream'),
            'product'=>array('details','images','stream'),
            'user'=>array('votes','links','all','activity','follows')
        );

/*
 NODE TYPE ARRAYS CONFIG - FOR WHERE THE SITE NEEDS A SUBSET OF ALL NODE TYPES
*/
    // scroller types
        $config['scroller_array']=array('product','blog');

    // search nodes - the node types to search
        $config['search_nodes']=array();

    // sitemap removes
        $config['nositemap']=array();

/*
 OTHERS
*/
    // blog author
        $config['default_blog_author']="<set default blog author>";

    // dates
        $config['date_format']=array(
            'calendar'=>'jS M',
            'date'=>'d-m-y',
            'time'=>'H:i:s',
            'human'=>'dS M y'
        );

    // default order by for lists
        $config['default_list_order']=array(
            'blog'=>'created desc',
            'event'=>'name asc',
            'project'=>'created desc',
            'product'=>'name asc',
            'search'=>'name asc'
        );

    // permissions, this should reflect the db, but due to the nature of the admin nav it is easier to
    // use config to set this
        $config['admin_nav_permissions']=array(
            'links'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'scores'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'about'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'me'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'page'=>array('super_admin'),
            'category'=>array('super_admin'),
            'blog'=>array(),
            'user'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user'),
            'group'=>array(),
            'all_images'=>array(),
            'order_list'=>array(),
            'product'=>array(),
            'voucher'=>array(),
            'postage'=>array(),
            'variation'=>array(),
            'calendar'=>array(),
            'event'=>array(),
            'newsletter'=>array(),
            'misc'=>array('super_admin'),
            'credentials'=>array('super_admin','admin_user','supplier_user','customer_user','signedup_user')
        );

    // profiler
        $config['profiler']=false;

/*
 SOCIAL MEDIA
*/
    // disqus name
        $config['disqus']='';

    // sites to share to, used to build the 'share this' buttons
        $config['social_sites']=array(
            'facebook'=>array(
                    'action'=>'like',
                    'colourscheme'=>'dark',
                    'font'=>'arial',
                    'layout'=>'button_count',
                    'send'=>'true',
                    'show_faces'=>'false',
                    'width'=>450
                ),
            'twitter'=>array(
                    'count'=>0,
                    'hashtag'=>'',
                    'intro'=>'i just had to share this',
                    'large_button'=>1,
                    'text'=>'tweet this',
                    'via'=>'' // must be a twitter handle, @ not required
                ),
            'stumbleupon'=>array()
        );

    // twitter
        $config['twitter_user']='xcitedstatelore';
        $config['twidth']=200;
        $config['theight']=200;
        $config['tback_colour']='#dedede';
        $config['ttext_colour']='#181818';
        $config['tlink_colour']='#0983bd';

/*
 SITE CONFIG - admin email etc.
*/
    if (strpos($_SERVER['HTTP_HOST'],'excitedstatelaboratory')>0)
    {
        $full_domain='links.excitedstatelaboratory.com';

        // backup path
            $config['backup_path']='backups/core.txt';

        // development
            $config['dev']=1;

        // emails for administrators
            $config['site_email']='tonileigh@excitedstatedesign.com';
            $config['from_email']='noreply@'.$full_domain;

        // name in text for head, title etc
            $config['site_name']='Links';

        // urls
            $config['live_domain']='set_to_live'; // we need a live domain too so we can redirect from dev to live
            $config['full_domain']=$full_domain;
    }
    else
    {
        if (strpos($_SERVER['HTTP_HOST'],'ev.')>0)
        {
            $full_domain='dev.shlinks.co.uk';

            // backup path
            $config['backup_path']='../backups/dev.txt';
        }
        elseif (strpos($_SERVER['HTTP_HOST'],'tyle.')>0)
        {
            $full_domain='style.shlinks.co.uk';

            // backup path
            $config['backup_path']='../backups/style.txt';
        }

        // development
            $config['dev']=0;

        // emails for administrators
            $config['site_email']='tonileigh@excitedstatedesign.com';
            $config['from_email']='noreply@'.$full_domain;

        // name in text for head, title etc
            $config['site_name']='Links';

        // urls
            $config['full_domain']=$full_domain; // no dev domain required as in the other case above
    }

/*
 SITE MAP
*/
    $config['site_map_priority']=array(
        'blog'=>0.8,
        'calendar'=>0.8,
        'category'=>0.5,
        'event'=>1.0,
        'node_group'=>0.3,
        'page'=>0.7,
        'product'=>1.0,
        'user'=>0.1
    );

/*
 TEMPLATE VIEWS
*/
    $config['template_views']=array(
        'head'=>array(
            'template/head_open',
            'template/head_close',
            'template/page_open',
            'template/admin_nav',
            'template/header_open',
            'template/header_close',
            'template/content_open',
            'template/node_tabs'
        ),
        'foot'=>array(
            'template/content_close',
            'template/footer_open',
            'template/footer_close',
            'template/map',
            'template/page_close'
        )
    );

/*
  VIDEO
*/
    $config['video_config']=array(
        'w'=>600,
        'h'=>338,
        'scroll'=>'none',
        'poster'=>'/img/poster.png'
    );

    // used to dynamically generate the width and height of the video player
        $config['controlbar_height']=31;

/*
    VOTING
*/
    $config['allow_vote_down']=0;

/*
 WARNINGS
*/
    // header warnings
        $config['js_warning']=0;
        $config['live_warning']=0;


/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|	http://example.com/
|
| If this is not set then CodeIgniter will guess the protocol, domain and
| path to your installation.
|
*/
$config['base_url']	= '';

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you've renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config['index_page'] = '';

/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of 'AUTO' works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| 'AUTO'			Default - auto detects
| 'PATH_INFO'		Uses the PATH_INFO
| 'QUERY_STRING'	Uses the QUERY_STRING
| 'REQUEST_URI'		Uses the REQUEST_URI
| 'ORIG_PATH_INFO'	Uses the ORIG_PATH_INFO
|
*/
$config['uri_protocol']	= 'AUTO';

/*
|--------------------------------------------------------------------------
| URL suffix
|--------------------------------------------------------------------------
|
| This option allows you to add a suffix to all URLs generated by CodeIgniter.
| For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/urls.html
*/

$config['url_suffix'] = '';

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config['language']	= 'english';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
*/
$config['charset'] = 'UTF-8';

/*
|--------------------------------------------------------------------------
| Enable/Disable System Hooks
|--------------------------------------------------------------------------
|
| If you would like to use the 'hooks' feature you must enable it by
| setting this variable to TRUE (boolean).  See the user guide for details.
|
*/
$config['enable_hooks'] = FALSE;


/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/core_classes.html
| http://codeigniter.com/user_guide/general/creating_libraries.html
|
*/
$config['subclass_prefix'] = 'MY_';


/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
|
| This lets you specify with a regular expression which characters are permitted
| within your URLs.  When someone tries to submit a URL with disallowed
| characters they will get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\/+-';


/*
|--------------------------------------------------------------------------
| Enable Query Strings
|--------------------------------------------------------------------------
|
| By default CodeIgniter uses search-engine friendly segment based URLs:
| example.com/who/what/where/
|
| By default CodeIgniter enables access to the $_GET array.  If for some
| reason you would like to disable it, set 'allow_get_array' to FALSE.
|
| You can optionally enable standard query string based URLs:
| example.com?who=me&what=something&where=here
|
| Options are: TRUE or FALSE (boolean)
|
| The other items let you set the query string 'words' that will
| invoke your controllers and its functions:
| example.com/index.php?c=controller&m=function
|
| Please note that some of the helpers won't work as expected when
| this feature is enabled, since CodeIgniter is designed primarily to
| use segment based URLs.
|
*/
$config['allow_get_array']		= TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger']	= 'c';
$config['function_trigger']		= 'm';
$config['directory_trigger']	= 'd'; // experimental not currently in use

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| If you have enabled error logging, you can set an error threshold to
| determine what gets logged. Threshold options are:
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|	0 = Disables logging, Error logging TURNED OFF
|	1 = Error Messages (including PHP errors)
|	2 = Debug Messages
|	3 = Informational Messages
|	4 = All Messages
|
| For a live site you'll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config['log_threshold'] = 3;

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ folder. Use a full server path with trailing slash.
|
*/
$config['log_path'] = '';

/*
|--------------------------------------------------------------------------
| Date Format for Logs
|--------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config['log_date_format'] = 'Y-m-d H:i:s';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| system/cache/ folder.  Use a full server path with trailing slash.
|
*/
$config['cache_path'] = '';

/*
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
|
| If you use the Encryption class or the Session class you
| MUST set an encryption key.  See the user guide for info.
|
*/
$config['encryption_key'] = '~NQlXTb3y7XmU^_uFXhAn(7ii[nw2y%u';

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
|
| 'sess_cookie_name'		= the name you want for the cookie
| 'sess_expiration'			= the number of SECONDS you want the session to last.
|   by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
| 'sess_expire_on_close'	= Whether to cause the session to expire automatically
|   when the browser window is closed
| 'sess_encrypt_cookie'		= Whether to encrypt the cookie
| 'sess_use_database'		= Whether to save the session data to a database
| 'sess_table_name'			= The name of the session database table
| 'sess_match_ip'			= Whether to match the user's IP address when reading the session data
| 'sess_match_useragent'	= Whether to match the User Agent when reading the session data
| 'sess_time_to_update'		= how many seconds between CI refreshing Session Information
|
*/
$config['sess_cookie_name']		= 'cisession';
$config['sess_expiration']		= 60*60*24;
$config['sess_expire_on_close']	= FALSE;
$config['sess_encrypt_cookie']	= TRUE;
$config['sess_use_database']	= TRUE;
$config['sess_table_name']		= 'ci_sessions';
$config['sess_match_ip']		= FALSE;
$config['sess_match_useragent']	= TRUE;
$config['sess_time_to_update']	= 300;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
|
| 'cookie_prefix' = Set a prefix if you need to avoid collisions
| 'cookie_domain' = Set to .your-domain.com for site-wide cookies
| 'cookie_path'   =  Typically will be a forward slash
| 'cookie_secure' =  Cookies will only be set if a secure HTTPS connection exists.
|
*/
$config['cookie_prefix']	= "";
$config['cookie_domain']	= "";
$config['cookie_path']		= "/";
$config['cookie_secure']	= FALSE;

/*
|--------------------------------------------------------------------------
| Global XSS Filtering
|--------------------------------------------------------------------------
|
| Determines whether the XSS filter is always active when GET, POST or
| COOKIE data is encountered
|
*/
$config['global_xss_filtering'] = TRUE;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| 'csrf_token_name' = The token name
| 'csrf_cookie_name' = The cookie name
| 'csrf_expire' = The number in seconds the token should expire.
*/
$config['csrf_protection'] = false;
$config['csrf_token_name'] = 'csrf_test_name';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;

/*
|--------------------------------------------------------------------------
| Output Compression
|--------------------------------------------------------------------------
|
| Enables Gzip output compression for faster page loads.  When enabled,
| the output class will test whether your server supports Gzip.
| Even if it does, however, not all browsers support compression
| so enable only if you are reasonably sure your visitors can handle it.
|
| VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
| means you are prematurely outputting something to your browser. It could
| even be a line of whitespace at the end of one of your scripts.  For
| compression to work, nothing can be sent before the output buffer is called
| by the output class.  Do not 'echo' any values with compression enabled.
|
*/
$config['compress_output'] = FALSE;

/*
|--------------------------------------------------------------------------
| Master Time Reference
|--------------------------------------------------------------------------
|
| Options are 'local' or 'gmt'.  This pref tells the system whether to use
| your server's local time as the master 'now' reference, or convert it to
| GMT.  See the 'date helper' page of the user guide for information
| regarding date handling.
|
*/
$config['time_reference'] = 'local';


/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
|
| If your PHP installation does not have short tag support enabled CI
| can rewrite the tags on-the-fly, enabling you to utilize that syntax
| in your view files.  Options are TRUE or FALSE (boolean)
|
*/
$config['rewrite_short_tags'] = FALSE;


/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
|
| If your server is behind a reverse proxy, you must whitelist the proxy IP
| addresses from which CodeIgniter should trust the HTTP_X_FORWARDED_FOR
| header in order to properly identify the visitor's IP address.
| Comma-delimited, e.g. '10.0.1.200,10.0.1.201'
|
*/
$config['proxy_ips'] = '';


/* End of file config.php */
/* Location: ./application/config/config.php */
