<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
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
    class Connection_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_connections() - gets the various arrays of connections, inlcuding blocks
        @param $node - the node whose connections we require
        @return $connections - various connection lists
    */
    function get_connections($node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_connections_start');

        $connections=array(
            'incoming'=>array(),
            'outgoing'=>array(),

            'connections'=>array(),

            'follows'=>array(),
            'followed_by'=>array(),

            'blocked'=>array(),
            'blocked_by'=>array()
        );

        $owns_node=0;
        if(isset($this->user))
        {
            $user=$this->user;
            $owns_node=($node['user_id']==$user['id']) ? 1 : 0;
        }

        if (1==$owns_node or
            'super_admin'==$this->user['user_type'])
        {
            // requests incoming
                $connections['incoming']=$this->get_list($node,'C',1,'in');

            // requests outgoing
                $connections['outgoing']=$this->get_list($node,'C',1,'out');

            // blocked
                $connections['blocked']=$this->get_list($node,'B',1,'out');
                $connections['blocked_by']=$this->get_list($node,'B',1,'in');
        }

        // friends
            $connections['connections']=$this->get_list($node,'C',2,'out');

        // follows
            $connections['follows']=$this->get_list($node,'F',2,'out');
            $connections['followed_by']=$this->get_list($node,'F',2,'in');

        /* BENCHMARK */ $this->benchmark->mark('func_get_connections_end');

        return $connections;
    }

    /* *************************************************************************
        get_list() - gets the contents of the node connection list,
            for either members of a group or friends of a user
        @param array $node - the node which needs a list preparing
        @param char $con_type - the type of connection to search for (see class comment)
        @param char $con_status - the status of the connection type to search for (see class comment)
        @param string $direction - incoming or outgoing, for users the distinction between things they did (outgoing)
            and things done to them (incoming)
        @param Boolean $return_ids - drop out with just the array of ids
        @return array - the list of users
    */
    public function get_list($node,$con_type,$con_status,$direction)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_list_end');

        $connection_list=array();

        // key is an id used to bring the correct ids back for listing
            $key=('in' === $direction) ? "node_id" : "user_id";

        // perform the query
            $query=$this->db->select('*')->from('connection')->where(array($key=>$node['id'],'connection_type'=>$con_type,'status'=>$con_status));
            $res=$query->get();
            $connections=$res->result_array();

        // build an id array, again the key is used to get the correct id
            // key is reversed for setting up the ids
            // if it's outgoing i'm focussing on nodes so i want those ids
            // if they are incoming then its other users so i want them
            $ids=array();
            $key=('in' === $direction) ? "user_id" : "node_id";
            foreach($connections as $c)
            {
                $ids[]=$c[$key];
            }

        // or the full nodes for front stage output
            if (count($connections))
            {
                $query=$this->db->select('*')->from('node')->where_in('id',$ids)->order_by('name');
                $res=$query->get();
                $list=$res->result_array();
            }
            else
            {
                $list=array();
            }

        // get the buttons for each list item
            if (count($list)>0)
            {
                $this->load->model('connection_button_model');
                foreach($list as $l)
                {
                    $connection_list[]=array(
                        'node'=>$l,
                        'buttons'=>$this->connection_button_model->connection_buttons($this->user,$l)
                    );
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_list_end');

        return $connection_list;
    }

    /* *************************************************************************
        get_connection() - gets an individual connection type from the database
        @param $user - the user side of the connection
        @param $node - the node side
        @param $con_type - the type of connection to look for
        @return $connection - then connection details
    */
    function get_connection($user,$node,$con_type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_connection_start');

        $connection=array();

        $query=$this->db->select('*')->from('connection')->where(array('user_id'=>$user['id'],'node_id'=>$node['id'],'connection_type'=>$con_type));
        $res=$query->get();
        $connection=$res->row_array();

        if (0 === count($connection))
        {
            $connection=array(
                'status'=>-1
            );
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_connection_end');

        return $connection;
    }
}
