<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

/*
 ROUTES are used primarily when we want to redirect a URL value at a controller / method fashion in the typical CI way
 this happens when forms are submitted by the user
*/
// payment gateway routes
    $route['order/initialise/paypal']='order/initialise/paypal';
    $route['order/initialise/worldpay']='order/initialise/worldpay';
    $route['order/initialise/sagepay']='order/initialise/sagepay';

// redirect to controllers to avoid category catch below
    // core controllers
        $route['basket/([a-z_]+)']='basket/$1';
        $route['connection/([a-z_]+)']='connection/$1';
        $route['contact/([a-z_]+)']='contact/$1';
        $route['database_backup/([a-z_]+)']='database_backup/$1';
        $route['engage/([a-z_]+)']='engage/$1';
        
        $route['event/sequence/([0-9]+)']='events/edit/$1';
        $route['events/delete_single/([0-9]+)/([0-9]+)/([0-9]+)']='events/delete_single/$1/$2/$3';
        $route['events/delete_sequence/([0-9]+)']='events/delete_sequence/$1';
        $route['events/([a-z_]+)']='events/$1';
        $route['image/([0-9]+)']='image_display/display_image/$1';
        
        $route['newsletter/([a-z_]+)']='newsletter/$1';
        $route['node/([a-z_]+)']='node/$1';
        $route['node_admin/([a-z_]+)']='node_admin/$1';
        $route['order/([a-z_]+)']='order/$1';
        $route['postage/([a-z_]+)']='postage/$1';
        
        $route['search/([a-z_]+)']='search/$1';
        $route['variation/([a-z_]+)']='variation/$1';
        $route['voting/([a-z_]+)']='voting/$1';
        $route['voucher/([a-z_]+)']='voucher/$1';
        
    // application controllers
        $route['article_link/edit/([0-9]+)']='article_link/edit/$1';
        $route['article_link/([a-z_]+)']='article_link/$1';

// node management
    $route['([a-z]+)/([0-9]+)/edit']='node_admin/edit/$1/$2'; // edit this specific node $2 of type $1
    $route['([a-z]+)/([0-9]+)/images/set']='image_upload/set/$2'; // set the mains and deletes for node $1
    $route['([a-z]+)/([0-9]+)/images/upload']='image_upload/upload/$2'; //  upload an image for node $1
    $route['([a-z]+)/([0-9]+)/images/thumbnail']='image_upload/thumbnail/$2'; // create a thumbnail from the current image for node $1
    $route['([a-z]+)/([0-9]+)/images']='image_upload/images/$2'; // image upload for node $1
    
    $route['([a-z]+)/([0-9]+)/variations']='node_admin/variations/$1/$2'; // create variations this specific node $2 of type $1
    $route['([a-z]+)/([0-9]+)/events']='node_admin/events/$1/$2'; // create events this specific node $2 of type $1
    $route['([a-z]+)/([0-9]+)/delete']='node_admin/delete/$1/$2'; // delete a node of type $1
    $route['([a-z]+)/save']='node_admin/save/$1'; // create a new node of type $1
    $route['([a-z]+)/([0-9]+)/inplace_save']='node/inplace_save/$1/$2';
    
    $route['([a-z]+)/create']='node_admin/edit/$1'; // create a new node of type $1
    $route['([a-z]+)/list']='node_admin/list_nodes/$1'; // edit all nodes of type $1
    $route['([a-z]+)/set']='node_admin/set_all/$1'; // edit all nodes

// redirecting some admin pages to controllers
    $route['postage-calculation-definition']='postage/show_postages';
    $route['variation-types-definition']='variation/variation_types';

// search
    $route['search-results/(:any)']='search/search_nodes/$1';

// node panels
    $route['([a-z0-9-_]+)/stream']='node/display_node/$1/stream';
    $route['([a-z0-9-_]+)/details']='node/display_node/$1/details';
    $route['([a-z0-9-_]+)/images']='node/display_node/$1/images';
    $route['([a-z0-9-_]+)/text']='node/display_node/$1/text';
    $route['([a-z0-9-_]+)/members']='node/display_node/$1/members';
    
    $route['([a-z0-9-_]+)/messages']='node/display_node/$1/messages';
    $route['([a-z0-9-_]+)/friends']='node/display_node/$1/friends';
    $route['([a-z0-9-_]+)/privacy']='node/display_node/$1/privacy';

// basic nodes
    $route['([a-z0-9-_]+)/([a-z0-9-_]+)/([a-z0-9-_]+)']='node/display_node/$1/$2/$3';
    $route['([a-z0-9-_]+)/([a-z0-9-_]+)']='node/display_node/$1/$2';
    $route['([a-z0-9-_]+)']='node/display_node/$1';

// catch all, revert to home
    $route['default_controller']='node/display_node';

/* End of file routes.php */
/* Location: ./application/config/routes.php */