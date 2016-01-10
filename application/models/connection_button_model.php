<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/connection_model.php');
/*
 class

 * @package     Template
 * @subpackage  Template Libraries
 * @category    Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
 * following is like subscribing, can apply to any node
 * befriending is like a serious connection, between users or request to join groups, invloves acceptance, rejection etc.
 * voting is in a sparate model and is like liking
 *
 * connection type codes ...
 *
 * B - blocked
 * C - connected (accept or rekect type)
 * F - follows (free type, any node)
 *
 * connection status codes ...
 *
 * 0 - rejected / blocked
 * 1 - pending (unaccepted requests) / unblocked
 * 2 - connected (follows, accepted requests)
 * 3-99 - levels of privacy
 *
*/
    class Connection_button_model extends Connection_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        connection_buttons() - get's a set of connection buttons relating to the
            signed in user and the node she is looking at
        @param $node - the node being viewed at this point (mostly another user or group though
            following can also be done to any node type)
        @param $user - the signed in user who is viewing the node
        @param $third_party_node - allows the user to disconnect a third party node from her own
            node that she may be viewing, i.e. stop someone following or kick someone out of a group
            this is only defined on followed by and member lists, and the request to join group logic
        @return $buttons - the array of generated buttons
    */
    function connection_buttons($user,$node,$third_party_node=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_connection_buttons_start');

        // instantiate button array, for code clarity more than anything, all buttons are never used
            $buttons=array(
                'accept'=>'', // become friends with or accept join request (adds to clist)
                'reject'=>'', // reject a connection or join request (removes pending)
                'remove'=>'', // break a connection, leave a group or unfriend (removes from clist)
                'request'=>'', // make a request to become friends or join a group (creates pending)
                'undo_request'=>'', // undo a sent request (removes pending)

                'block'=>'', // stop user from viewing you site wide (adds to blocked and blocked by)
                'unblock'=>'', // undoes a block (removes from blocked and blocked by)

                'follow'=>'', // connect without confirmation process, with user or groupnode (adds to follows and followed by)
                'unfollow'=>'', // undoes a follow (removes from follows and followed by)
                'unfollowed_by'=>'', // undoes a followed by, so a user can stop being followed

                //'dev_route'=>'' // shows the route through the if statement
            );

        if (isset($this->user['user_id']))
        {
            // if third party node is set then it has to over-ride the user
            // this is because the user is looking at a list of third party nodes (users) that are associated with her
            // node and may want to break some of the connections
            // such a button never appears on a direct user / node relation
                if (is_array($third_party_node))
                {
                    $user=$third_party_node;
                }

            // only if the node is not the signed in user
                if ($node['id']!=$user['id'])
                {
                    // only do these for users and groups
                        if (in_array($node['type'], array('groupnode','user')))
                        {
                            //$buttons['dev_route'].="1|u".$user['id'].";n".$node['id']."|";
                            // get C type connection
                                $outgoing=$this->get_connection($user,$node,'C');
                                $incoming=$this->get_connection($node,$user,'C');

                                //$buttons['dev_route'].=$outgoing['status']."|";
                                //$buttons['dev_route'].=$incoming['status']."|";

                            // build request related buttons
                                if ($outgoing['status']<1 && // user has not connected with this node
                                    $incoming['status']<1)
                                {
                                    //$buttons['dev_route'].="2|";
                                    // request
                                        $buttons['request']=$this->connection_button('request',$user,$node);
                                }
                                else // user has connected with this node
                                {
                                    //$buttons['dev_route'].="3|";
                                    if (2 == $outgoing['status'] &&
                                        2 == $incoming['status'])
                                    {
                                        //$buttons['dev_route'].="4|";
                                        // disconnect
                                            $buttons['remove']=$this->connection_button('remove',$user,$node);
                                    }
                                    else
                                    {
                                        //$buttons['dev_route'].="5(".$outgoing['status'].")|";
                                        if (1 == $outgoing['status']) // user has issued a connection request to this node
                                        {
                                            //$buttons['dev_route'].="6|";
                                            // undo request
                                                $buttons['undo_request']=$this->connection_button('undo_request',$user,$node);
                                        }
                                        else if (1 == $incoming['status']) // connection request received from this node or
                                        {
                                            //$buttons['dev_route'].="7|";
                                            // reject request
                                                $buttons['reject']=$this->connection_button('reject',$user,$node);

                                            // accept request
                                                $buttons['accept']=$this->connection_button('accept',$user,$node);
                                        }
                                    }

                                }

                            // build block buttons
                                $connection=$this->get_connection($user,$node,'B');
                                if ($connection['status']>0)
                                {
                                    //$buttons['dev_route'].="8|";
                                    // unblock
                                        $buttons['unblock']=$this->connection_button('unblock',$user,$node);
                                }
                                else
                                {
                                    //$buttons['dev_route'].="9|";
                                    // block
                                        $buttons['block']=$this->connection_button('block',$user,$node);
                                }
                        }

                    // build follow buttons
                        $connection=$this->get_connection($user,$node,'F');
                        if ($connection['status']>0) // user has followed this node
                        {
                            //$buttons['dev_route'].="10|";
                            // unfollow
                                $buttons['unfollow']=$this->connection_button('unfollow',$user,$node);
                        }
                        else // user is not following this node
                        {
                            //$buttons['dev_route'].="11|";
                            // follow
                                $buttons['follow']=$this->connection_button('follow',$user,$node);
                        }

                        $connection=$this->get_connection($node,$user,'F');
                        if ($connection['status']>0) // user is followed by this node
                        {
                            //$buttons['dev_route'].="12|";
                            // unfollow
                                $buttons['unfollowed_by']=$this->connection_button('unfollowed_by',$user,$node);
                        }
                }
        }

        /* BENCHMARK */ $this->benchmark->mark('func_connection_buttons_end');

        return $buttons;
    }

    /* *************************************************************************
         connection_button() - builds a connection button, this may send a request,
            decline a request, leave or block
         @param string $jsfunc - the js function that is called when this button is
            clicked (also used to add a class to the button)
         @param int $jsfunc_id1 - the first node id to put into the js function
         @param int $jsfunc_id2 - the second node id to put into the js function
         @param string $button_id - the id of the button (for jquery and css)
         @param string $button_text - the text to display on the button
         @param string $name - names are sometimes passed to the js function for feedback
         @param string $type - the type of node being interacted with
         @return $cb - the button
    */
    public function connection_button($button_type,$user,$node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_connection_button_start');

        // set the button id for jquery
            $js_id="js-".$button_type."-".$user['id']."-".$node['id'];

        // get the config array for button text
            $button_text=$this->config->item('connection_button_text');

        if (isset($button_text[$node['type']][$button_type]) &&
            $button_text[$node['type']][$button_type]!=null)
        {
            $text=str_replace("%_NAME", $node['name'], $button_text[$node['type']][$button_type]);

            // build button
                $cb="";
                $cb.="<button id='".$js_id."' class='".$button_type." js_connect action button'>";
                $cb.=$text;
                $cb.="</button>";

            return $cb;
        }

        return "";

        /* BENCHMARK */ $this->benchmark->mark('func_connection_button_end');
    }
}
