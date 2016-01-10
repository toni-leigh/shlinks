<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/conversation_model.php');
/*
 class Message_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
*/
    class Message_model extends Conversation_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
        save_message() - saves a message to the database
        @param $user - array that is the user
        @param $message - the actual text of the message
        @param $conversation - the conversation to which this message belongs
    */
    function save_message($user,$message,$conversation_id,$participants)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_message_start');

        $this->load->helper('data_helper');

        $message=convert_urls($message);

    	// save message
	        $insert_data=array(
	    		'conversation_id'=>$conversation_id,
	    		'message'=>"<p>".str_replace("<br />","</p><p>",nl2br($message))."</p>",
	    		'user_id'=>$user['id'],
	    		'user_name'=>$user['name'],
	    		'user_url'=>$user['url'],
	    		'user_image'=>$user['image']
	    	);
	        $this->db->insert('message',$insert_data);

	    // insert id for read table
        	$new_message_id=$this->db->insert_id();

        // new unread message for each participant
        	foreach ($participants as $p)
        	{
                $message_read=($p['user_id']==$user['user_id']) ? 1 : 0;

                $this->new_unread($p,$new_message_id,$conversation_id,$message_read);
        	}

        /* BENCHMARK */ $this->benchmark->mark('func_save_message_end');
    }

    /* *************************************************************************
        new_unread() - marks a message as unread by a user, as we delete when marking something
            read we can just insert to mark something as unread
        @param $user - the user who has an unread message to look at
        @param $message_id - the id of the unread message
        @param $conversation_id - the conversation id to which the message belongs
    */
    function new_unread($user,$message_id,$conversation_id,$message_read)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_new_unread_start');

        $insert_data=array(
            'conversation_id'=>$conversation_id,
            'user_id'=>$user['user_id'],
            'message_id'=>$message_id,
            'message_read'=>$message_read
        );
        $this->db->insert('message_read',$insert_data);

        /* BENCHMARK */ $this->benchmark->mark('func_new_unread_end');
    }

    /* *************************************************************************
        save_read() - mark an existing message as read by this user
        @param $user_id - the user that just read the message
        @param $message_id - the message they just read
    */
    function save_read($user_id,$message_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_read_start');

        $update_data = array(
            'message_read'=>1
        );

        $this->db->where(array('user_id'=>$user_id,'message_id'=>$message_id));
        $this->db->update('message_read', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_save_read_end');
    }

    /* *************************************************************************
        count_unread() - counts the unread messages for this user
        @param $user - the user whose messages to count
        @param $conversation_id - can restrict to a conversation if needed
        @return $count - an array from the query containing the count
    */
    function count_unread($user,$conversation_id=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_count_unread_start');

        $count=array();

        if (isset($user['user_id']))
        {

            if (null==$conversation_id)
            {
                $query=$this->db
                    ->select(
                        'count(distinct conversation_id) as "unread"'
                    )
                    ->from(
                        'message_read'
                    )
                    ->where(
                        array(
                            'message_read'=>0,
                            'user_id'=>$user['id']
                        )
                    );
                $res=$query->get();
                $count=$res->row_array();
            }
            else
            {
                $query=$this->db
                    ->select(
                        'count(message_id) as "unread"'
                    )
                    ->from(
                        'message_read'
                    )
                    ->where(
                        array(
                            'message_read'=>0,
                            'user_id'=>$user['id'],
                            'conversation_id'=>$conversation_id
                        )
                    );
                $res=$query->get();
                $count=$res->row_array();
            }

        }

        /* BENCHMARK */ $this->benchmark->mark('func_count_unread_end');

        return $count;
    }

    /* *************************************************************************
        get_conversation_messages() - gets the messages for this conversation
        @param $user - the user who is viewing the conversation
        @param $conversation - the conversation details
        @return $messages - array containing the messages
    */
    function get_conversation_messages($user,$conversation)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_messages_start');

        $messages=array();

        $query=$this->db
            ->select(
                'message.user_id,
                 message.message_id,
                 message.user_name,
                 message.user_image,
                 message.user_url,
                 message.message,
                 message.message_time,
                 message_read.message_read'
            )
            ->from(
                'message'
            )
            ->where(
                array(
                    'message.conversation_id'=>$conversation['conversation_id'],
                    'message_read.user_id'=>$user['id']
                )
            )
            ->join(
                'message_read',
                'message_read.message_id=message.message_id'
            )
            ->order_by(
                'message_time asc'
            );

        $res=$query->get();
        $messages=$res->result_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_conversation_messages_end');

        return $messages;
    }

    /* *************************************************************************
        get_latest_message() - gets the latest message in a conversation
        @param $conversation - the conversation whose latest message should be got
        @return $message - the message details
    */
    function get_latest_message($user,$conversation)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_latest_message_start');

        $message=array();

        $query=$this->db
            ->select(
                'message.user_id,
                 message.message_id,
                 message.user_name,
                 message.user_image,
                 message.user_url,
                 message.message,
                 message.message_time,
                 message_read.message_read'
            )
            ->from(
                'message'
            )
            ->where(
                array(
                    'message.conversation_id'=>$conversation['conversation_id'],
                    'message_read.user_id'=>$user['id']
                )
            )
            ->join(
                'message_read',
                'message_read.message_id=message.message_id'
            )
            ->order_by(
                'message_time desc'
            )
            ->limit(
                1
            );

        $res=$query->get();
        $message=$res->row_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_latest_message_end');

        return $message;
    }

    /* *************************************************************************
        message_form() - creates a form for sending a message to a conversation
        @param $conversation - the current conversation
        @param $user - the other user for the message for if this is a form that instigates a conversation
            on a user node page - NOT the signed in user
        @return $form_html - the html of the form
    */
    function message_form($conversation,$user=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_message_form_start');

        $form_html="";

        $form_html.="<form method='post' action='/message/save'>";
        if ($user!=null)
        {
            $form_html.="<input class='js_user_id' type='hidden' name='user_id' value='".$user['id']."'/>";
        }
        $form_html.="<input class='js_conversation_id' type='hidden' name='conversation_id' value='".$conversation['conversation_id']."'/>";
        $form_html.="<textarea id='js_commentfield' class='js_message message_field' name='message'></textarea>";
        $form_html.="<input class='js_message_submit submit' type='submit' name='submit' value='send message'/>";
        $form_html.="</form>";

        /* BENCHMARK */ $this->benchmark->mark('func_message_form_end');

        return $form_html;
    }

    /* *************************************************************************
        set_image_in_messages() - sets the image in the message table for all this users
            messages
        @param $node_id - the node id of the user
        @param $img - the array containing the image data
    */
    function set_image_in_messages($node_id,$img)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_messages_start');

        $image_path=thumbnail_url($img,'300');

        // actor first
            $update_data = array(
                'user_image'=>$image_path
            );

            $this->db->where('user_id', $node_id);
            $this->db->update('message', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_set_image_in_messages_end');

    }
}
