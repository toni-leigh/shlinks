<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Connection

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
    class Connection extends Universal {

    public function __construct()
    {
        parent::__construct();

        /* BENCHMARK */ $this->benchmark->mark('Connection_start');

        // models
		$this->load->model('connection_save_model');
		$this->load->model('connection_button_model');
		$this->load->model('node_model');

        // properties
			$get=$this->get_input_vals();
        	$this->node=$this->node_model->get_node($get['node_id']);
        	$this->node=$this->node_model->get_node($this->node['id'],$this->node['type']); // the viewed node (in most cases another user)
        	$this->user=$this->node_model->get_node($get['user_id'],'user'); // the user viewing the node

        	$this->add=true;

        /* BENCHMARK */ $this->benchmark->mark('Connection_end');
    }

	/*
		accept() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function accept()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_accept_start');

		// add to clists
			$this->connection_save_model->update($this->user,$this->node,'C',2,$this->add);
			$skip_action=true;
			$this->connection_save_model->update($this->node,$this->user,'C',2,$this->add,$skip_action);

		// return remove button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('remove',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_accept_end');

		exit(json_encode($new_button));
	}
	/*
		reject() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function reject()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_reject_start');

		// remove from pending
			$this->connection_save_model->update($this->node,$this->user,'C',0);

		// return request button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('request',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_reject_end');

		exit(json_encode($new_button));
	}
	/*
		remove() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function remove()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_remove_start');

		// add to clists
			$this->connection_save_model->update($this->user,$this->node,'C',0);
			$this->connection_save_model->update($this->node,$this->user,'C',0);

		// return request button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('request',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_remove_end');

		exit(json_encode($new_button));
	}
	/*
		request() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function request()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_request_start');

		// add a pending record to store the requested connection
			$this->connection_save_model->update($this->user,$this->node,'C',1,$this->add);
			$this->connection_save_model->update($this->user,$this->node,'B',0);

		// return undo_request button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('undo_request',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_request_end');

		exit(json_encode($new_button));
	}
	/*
		undo_request() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function undo_request()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_undo_request_start');

		// remove from pending
			$this->connection_save_model->update($this->user,$this->node,'C',0);

		// return request button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('request',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_undo_request_end');

		exit(json_encode($new_button));
	}
	/*
		block() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function block()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_block_start');

		// add to blocked and blocked_by
			$this->connection_save_model->update($this->user,$this->node,'B',1,$this->add);
			$this->connection_save_model->update($this->user,$this->node,'C',0);
			$this->connection_save_model->update($this->node,$this->user,'C',0);

		// return unblock button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('unblock',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_block_end');

		exit(json_encode($new_button));
	}
	/*
		unblock() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function unblock()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_unblock_start');

		// remove from blocked and blocked_by
			$this->connection_save_model->update($this->user,$this->node,'B',0);

		// return block button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('block',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_unblock_end');

		exit(json_encode($new_button));
	}
	/*
		follow() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function follow()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_follow_start');

		// add to follows and followed_by
			$this->connection_save_model->update($this->user,$this->node,'F',2,$this->add);

		// return unfollow button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('unfollow',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_follow_end');

		exit(json_encode($new_button));
	}
	/*
		unfollow() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function unfollow()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_unfollow_start');

		// remove from follows and followed_by
			$this->connection_save_model->update($this->user,$this->node,'F',0);

		// return follow button as the opposite of this
			$new_button=$this->connection_button_model->connection_button('follow',$this->user,$this->node);

		/* BENCHMARK */ $this->benchmark->mark('func_unfollow_end');

		exit(json_encode($new_button));
	}
	/*
		unfollowed_by() - function corresponds directly to a button call of the same name
			function retrieves the $_GET array and uses the signed in user details
			to trigger the action involving the node
	*/
	public function unfollowed_by()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_unfollowed_by_start');

		// remove from follows and followed_by
			$this->connection_save_model->update($this->node,$this->user,'F',0);

		// return nothing as there is nothing to undo here
			$new_button="";

		/* BENCHMARK */ $this->benchmark->mark('func_unfollowed_by_end');

		exit(json_encode($new_button));
	}

}
