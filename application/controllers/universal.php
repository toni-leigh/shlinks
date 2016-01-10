<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 class Universal

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 * sits inbetween the CI classes and our own controllers allowin global fuctionality to be added to all our controllers
*/
    class Universal extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

		/* BENCHMARK */ $this->benchmark->mark('universal_constr_start');

        $this->output->enable_profiler($this->config->item('profiler'));

		$this->load->database();

        // NB REPEATED IN UNIVERSAL MODEL
			if (!isset($this->user))
			{
				$this->db->select('*');
				$this->db->from('node');
				$this->db->where(array('id' => $this->session->userdata('user_id')));
				$this->db->join('user', "node.id = user.user_id");
				$this->user=$this->db->get()->row_array();

				if (count($this->user))
				{
					// images
						$query=$this->db
							->select('*')
							->from('image')
							->where(array('owning_node_id'=>$this->user['user_id'],'image.removed'=>0))
							->join('node','image.node_id = node.id')
							->order_by('main desc');
						$res=$query->get();
						$img=$res->row_array();
						if (count($img)>0)
						{
							$this->user['main_image']="/user_img/".$img['user_id']."/".$img['image_filename']."s"."700".$img['image_ext'];
							$this->user['main_thumb']="/user_img/".$img['user_id']."/".$img['image_filename']."t"."100".$img['image_ext'];
						}
						else
						{
							$this->user['main_image']=$this->config->item('default_image');
							$this->user['main_thumb']=$this->config->item('default_image');
						}
				}
				else
				{
					// initialise anonymous
					$this->user['user_id']=null;
					$this->user['user_name']='';
					$this->user['user_type']='anon_user';
					$this->user['display_name']='';
					$this->user['stream_id']=0;
					$this->user['blocked']=array();
					$this->user['blocked_by']=array();
					$this->user['all_friends']=array();
					$this->user['all_friends_by']=array();
					$this->user['set_two']=array();
					$this->user['set_two_by']=array();
					$this->user['set_three']=array();
					$this->user['set_three_by']=array();
					$this->user['short_desc']='';
					$this->user['node_html']='';
					$this->user['main_image']=$this->config->item('default_image');
					$this->user['main_thumb']=$this->config->item('default_image');
				}
			}
        // END OF REPEATED IN UNIVERSAL MODEL

		$this->load->model('universal_model');

		/* BENCHMARK */ $this->benchmark->mark('universal_constr_end');
    }

    /* *************************************************************************
         send_email() - sends an email, can be sent by any controller really
         @param string
         @param numeric
         @param array
         @return
    */
    protected function send_email($to,$from,$subject,$body,$send='LIVE')
    {
        /* BENCHMARK */ $this->benchmark->mark('func_send_email_start');

		$this->universal_model->send_email($to,$from,$subject,$body,$send);

        /* BENCHMARK */ $this->benchmark->mark('func_send_email_end');
    }

	/* *************************************************************************
		 get_input_vals() - create an array containing all post and get values - useful for controller calls
			as will allow for the same values to be found whether the are in ajax gets or form posts
		 @return a concatenated value array
	*/
	public function get_input_vals()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_get_input_vals_start');

		// retrieve
			if (is_array($this->input->get()) ? $get=$this->input->get() : $get=array() );
			if (is_array($this->input->post()) ? $post=$this->input->post() : $post=array() );

		// merge
			$merged=array_merge($get,$post);

		// unset the submit button from the array
			if (isset($merged['submit']))
			{
				unset($merged['submit']);
			}

		return $merged;

		/* BENCHMARK */ $this->benchmark->mark('func_get_input_vals_end');
	}

    /*
         store_post() - stores the post array contents in the session to be retreived on page reload
         @param array $post - the post array
         @return
    */
    protected function _store_post($post)
    {
        /* BENCHMARK */ $this->benchmark->mark('func__store_post_start');

        $this->_clear_post();
        $this->session->set_userdata(array('post'=>$post));

        /* BENCHMARK */ $this->benchmark->mark('func__store_post_end');
    }

    /*
         _clear_post() - clears the session post array
         @param string
         @param numeric
         @param array
         @return
    */
    protected function _clear_post()
    {
        /* BENCHMARK */ $this->benchmark->mark('func__clear_post_start');

        $this->session->unset_userdata('post');

        /* BENCHMARK */ $this->benchmark->mark('func__clear_post_end');
    }

    /*
		_reload() - reloads after a user action has been completed
		@param string $url - the url to reload
		@param string $action - the action attempted, for the log
		@param string $message - the user feedback message
		@param string $success - a success value, 'success' or 'fail'
    */
    protected function _reload($url,$message,$success)
    {
		$this->session->set_userdata("message","<span class='".$success." message'>".$message."</span>");
		header('location:/'.$url);
		exit();
    }

    /*
		@param string $url - the url to reload
		@param string $action - the action attempted, for the log
		@param string $message - the user feedback message
		@param string $success - a success value, 'success' or 'fail'
    */
    protected function _log_action($url,$action,$success)
    {
        $log_message="{ user_id:".$this->user['user_id']." , user_name:".$this->user['user_name']." , action:".$action." , outcome:".$success." , url:".$url." } , ";
        log_message('error', $log_message);
    }
}
