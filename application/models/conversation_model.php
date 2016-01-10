<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Conversation_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
*/
    class Conversation_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        get_users_conversations() - gets all the conversations a user is involved in
        @param $user - the user
        @return  -
    */
    function get_users_conversations($user)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_users_conversations_start');

        // basic query
            $query=$this->db->select('*')->from('user_conversation')->where(array('user_id'=>$user['id']));
            $res=$query->get();
            $conversations=$res->result_array();

        // augment with users in this conversation
        // also load the latest message
        // all messages are loaded when a conversation is chosen to save weight
            for($x=0;$x<count($conversations);$x++)
            {
                $conversations[$x]['users']=$this->get_conversation_users($conversations[$x]['conversation_id'],true);

                $conversations[$x]['latest_message']=$this->message_model->get_latest_message($user,$conversations[$x]);

                $conversations[$x]['count']=$this->message_model->count_unread($user,$conversations[$x]['conversation_id']);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_users_conversations_end');

        return $conversations;
    }

    /* *************************************************************************
        get_conversation_users() - get the users involved in a conversation
        @param $conversation_id - id of the conversation
        @return $participants - array of ids of the conversation participants
    */
    function get_conversation_users($conversation_id,$full_users=false)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_users_start');

        $participants=array();

        $query=$this->db->select('user_id')->from('user_conversation')->where(array('conversation_id'=>$conversation_id));
       	$res=$query->get();
       	$participants=$res->result_array();

        if (true==$full_users)
        {
            $this->load->model('node_model');

            for($x=0;$x<count($participants);$x++)
            {
                $participants[$x]['details']=$this->node_model->get_node($participants[$x]['user_id']);
            }
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_users_end');

        return $participants;
    }

    /* *************************************************************************
        get_conversation() - gets a conversation, if one exists, between two users
        @param $user1 - the user viewing the node
        @param $user2 - the node being viewed (always a user)
        @return $conversation - an array containing the ocnversation
    */
    function get_conversation($user1,$user2)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_start');

        $conversation=array();

        $conv_query=$this->db->simple_query("select * from user_conversation uc1, user_conversation uc2 where uc1.conversation_id=uc2.conversation_id and uc1.user_id=".$user1['id']." and uc2.user_id=".$user2['id']);

        $conversation=mysql_fetch_array($conv_query);

        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_end');

        return $conversation;
    }

    /* *************************************************************************
        create_conversation() - create a new conversation
        @param $users - an array of users to add to the conversation
        @return  -
    */
    function create_conversation($users,$instigator)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_create_conversation_start');

        $insert_data=array(
            'user_id'=>$instigator['id']
        );
        $this->db->insert('conversation',$insert_data);

        $conversation_id=$this->db->insert_id();

        foreach ($users as $u)
        {
            $insert_data=array(
                'conversation_id'=>$conversation_id,
                'user_id'=>$u['id']
            );
            $this->db->insert('user_conversation',$insert_data);
        }

        /* BENCHMARK */ $this->benchmark->mark('func_create_conversation_end');

        return $conversation_id;
    }

    /* *************************************************************************
        get_conversation_messages() - gets a conversation individually, with message stream
        @param $user - the user who is viewing the conversation, for read state etc.
        @param $conversation - the conversation basic details
        @return  $conversation - the full conversation, with users, messages etc.
    */
    function get_conversation_messages($user,$conversation)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_start');

        $conversation=array();

        $conversation=$this->message_model->get_conversation_messages($user,$conversation);

        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_end');

        return $conversation;
    }
}
