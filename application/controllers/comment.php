<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Comment

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Comment extends Universal {

    public function __construct()
    {
        parent::__construct();

        // models
			$this->load->model('conversation_model');
			$this->load->model('comment_model');
    }

    /* *************************************************************************
        save() - saves a comment
        @reload  -
    */
    function save()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        $comment_vals=$this->get_input_vals();

        // save the comment
        	$comment=$this->comment_model->save_comment($comment_vals['node_id'],$comment_vals['comment']);

        // get a new comment panel for appending to the comment stream
            $data['comm']=$this->user;
            $data['comm']['comment']=$comment['comment'];
            $data['comm']['comment_time']='just now';
            $data['comm']['comment_id']=$comment['comment_id'];
        	$comment_panel=$this->load->view("template/node/comment",$data,true);

        // save the action
            $this->load->model('stream_model');
            $this->load->model('node_model');
            $target=$this->node_model->get_node($comment_vals['node_id']);
            $this->stream_model->store_action(3,$this->user,$target,$target['user_id']);

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');

        // success
        	exit(json_encode($comment_panel));
    }
}
