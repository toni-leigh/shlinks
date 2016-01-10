<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Message

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Message extends Universal {

    public function __construct()
    {
        parent::__construct();

        // models
			$this->load->model('conversation_model');
			$this->load->model('message_model');
    }

    /* *************************************************************************
        save() - saves a message
        @reload  -
    */
    function save()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        $message=$this->get_input_vals();

        // create conversation or get
        	if (0==$message['cid'] &&
                is_numeric($message['uid']))
        	{
                $this->load->model('node_model');

                $other_user=$this->node_model->get_node($message['uid']);

        		// create new conversation
                    $users=array(
                        0=>$this->user,
                        1=>$other_user
                    );
        			$message['cid']=$this->conversation_model->create_conversation($users,$this->user);
        	}

        // save the message
        	$participants=$this->conversation_model->get_conversation_users($message['cid']);

        	$this->message_model->save_message($this->user,$message['message'],$message['cid'],$participants);

        // get a new message panel for appending to the message stream
            $data['m']=$this->message_model->get_latest_message($this->user,array('conversation_id'=>$message['cid']));
            $data['user']=$this->user;
        	$message_panel=$this->load->view("template/node/message",$data,true);

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');

        // success
        	exit(json_encode($message_panel));
    }

    /* *************************************************************************
        mark_read() - marks a message as read via js
        @reload  -
    */
    function mark_read()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_mark_read_start');

        $read=$this->get_input_vals();

        $this->message_model->save_read($read['uid'],$read['mid'],1);

        /* BENCHMARK */ $this->benchmark->mark('func_mark_read_end');

        // success
            exit();
    }

    /* *************************************************************************
        load_conversation() - loads a conversation in response to js click
        @reload  -
    */
    function load_conversation()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_load_conversation_start');

        $conversation=$this->get_input_vals();

        $data['messages']=$this->message_model->get_conversation_messages($this->user,array('conversation_id'=>$conversation['cid']));
        $data['user']=$this->user;

        $message_html=$this->load->view("template/node/message_stream",$data,true);

        /* BENCHMARK */ $this->benchmark->mark('func_load_conversation_end');

        // success
            exit(json_encode($message_html));
    }
}
