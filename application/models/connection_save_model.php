<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/connection_model.php');
/*
 class

 * @package     Template
 * @subpackage  Template Libraries
 * @category    Template Libraries
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
    class Connection_save_model extends Connection_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         add_connection_action() - creates a connection between two nodes, streams are also updated
         @param $user - the user who is performing the updated status
         @param $node - the focus of the updated status
         @param $con_type - the type of connection
         @param $status - new status of the connection,
             0 = undone
             1 = requested
             2 = accepted (automatic for follows)
    */
    public function add_connection_action($user,$node,$con_type,$status)
    {
        // get the action codes
            switch ($con_type)
            {
                case 'C':
                    if ('user'==$node['type'])
                    {
                        $action_type=4;
                    }
                    else
                    {
                        $action_type=5;
                    }
                    break;
                case 'F':
                    $action_type=10;
                    break;
            }

        // either store and action or remove if it's undone, don;t do anything for blocks
            if ($con_type!='B')
            {
                $this->load->model('stream_model');

                $target_owner_id=('C'==$con_type && $node['type']!='groupnode') ? null : $node['user_id'];

                if (2==$status)
                {
                    $this->stream_model->store_action($action_type,$user,$node,$target_owner_id);
                    if ('user'==$node['type'] &&
                        $action_type!=10)
                    {
                        // an extra one for befriending as this is mutual, but NOT if it's a follow !
                        $this->stream_model->store_action($action_type,$node,$user,$target_owner_id);
                    }
                }
                elseif (0==$status)
                {
                    $this->stream_model->undo_action($action_type,$user,$node,$target_owner_id);
                }
            }
    }

    /* *************************************************************************
         update() - updates the status of a connection
         @param $user - the user who is performing the updated status
         @param $node - the focus of the updated status
         @param $con_type - the type of connection
         @param $new_status - new status of the connection
         @param $add - will add if the connection isn't found
         @param $skip_action - drops the action savem only used for the second connection action
    */
    public function update($user,$node,$con_type,$new_status,$add=false,$skip_action=false)
    {
        // get the current connection record
            $connection=$this->get_connection($user,$node,$con_type);

        // do we add or update ?
            if (-1  ===  $connection['status'])
            {
                if (true === $add)
                {
                    $insert_data = array(
                        'user_id'=>$user['id'],
                        'node_id'=>$node['id'],
                        'connection_type'=>$con_type,
                        'status'=>$new_status
                    );

                    $this->db->insert('connection', $insert_data);
                }
            }
            else
            {
                $update_data = array(
                    'status'=>$new_status
                );

                $this->db->where(array('user_id'=>$user['id'],'node_id'=>$node['id'],'connection_type'=>$con_type));
                $this->db->update('connection', $update_data);
            }

        // update the stream actions
            if (false === $skip_action)
            {
                $this->add_connection_action($user,$node,$con_type,$new_status);
            }
    }
}
