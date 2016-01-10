<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'models/universal_model.php');
/*
 class Engage_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 * stores the functions associated with user engagement, the process through which a user identifies themselves to the site, through login or registration
*/

class Engage_model extends Universal_model {

	public function __construct()
	{
		parent::__construct();

		$this->load->model('node_model');
		$this->load->model('node_admin_model');
		$this->load->model('stream_model');

		$this->load->helper('form');
		$this->load->helper('security');
		$this->load->helper('string');
		$this->load->helper('url');
		$this->load->library('input');
    }

	/* *************************************************************************
		 get_user() - gets the full user details using get_node()
		 @param array $user - might just be a single value with the user_id in it as 'node_id'
		 @return the full user array
	*/
	public function get_user($user)
	{
		return $this->node_model->get_node($user['user_id'],'user');
	}

	/* *************************************************************************
		 get_user_on_email() - uses the email as a key for the user retrieval
		 @return the user (or lack of one)
	*/
	public function get_user_on_email($email)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_get_user_on_email_start');

		// the where string looks to see if this users credential entry matches one of their ids
			$where="(email='".$email."' or facebook_email='".$email."')";

		// get the result
			$query=$this->db->select('*')->from('user')->where($where);
			$user_details=$query->get()->row_array();

		// now get the entire user
			if (count($user)>0)
			{
				$user=$this->get_user($user_details['user_id']);
			}
			else
			{
				$user=array();
			}

		return $user;

		/* BENCHMARK */ $this->benchmark->mark('func_get_user_on_email_end');
	}

	/* *************************************************************************
		 profile_complete() - checks this user to see if their profile is complete
		 @param array $user
		 @return whether or not their profile is complete
	*/
	public function profile_complete($user)
	{
		// if the main areas of their profile are incomplete then we can display a message with this result
			if (''==$user['display_name'] or
				''==$user['short_desc'] or
				''==$user['node_html'])
			{
				return 0;
			}
			else
			{
				return 1;
			}
	}

	/* *************************************************************************
		 check_username() - sees if a username is taken
		 @param string $user_name - the username to check
		 @return the result count
	*/
	public function check_username($user_name)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_check_username_start');

		// make this user name a URL for comparison
			$change_to_url=strtolower(str_replace("--","-",str_replace(" ","-",preg_replace("/[^0-9a-z ]+/i","",trim(stripslashes($user_name))))));

        // look for this url
            $result=$this->node_model->get_node($change_to_url);

		// array to return
			$return=array();
			$return['check']=$result;
			$return['uname']=$change_to_url;

		return $return;

		/* BENCHMARK */ $this->benchmark->mark('func_check_username_end');
	}

	/* *************************************************************************
		 sign_in() -
		 @param array $user - the user to sign in
		 @param char $action - saves the action of logging in
	*/
	public function sign_in($user,$action)
	{
		 // remove the potentially massive value from the cookie
			unset($user['node_html']);

		// set the user as signed in in the session
			$this->session->set_userdata($user);

		// log the access
			$this->log_access($user['user_id'],$action);
	}
	/* *************************************************************************
		 logout_form() - create a logout form, just a single button
		 @param string $url - the url to reload to keep the user on the same page they clicked when they logged out
		 @return html logout button
	*/
	public function logout_form($url)
	{
		// open the form
			$attr=array(
				'name'=>'login_form',
				'id'=>'login_form',
				'class'=>'form'
			);
			$hidden=array('url'=>$url);
			$logout_html=form_open('engage/logout',$attr,$hidden);

		// submit button
			$attr=array(
				'name'=>'submit',
				'id'=>'logout_submit',
				'class'=>'submit'
			);
			$logout_html.=form_submit($attr,'logout');
			$logout_html.=form_close();

		return $logout_html;
	}

	/* *************************************************************************
		 log_access() - simple function logs an access in a simple table in the db
		 @param int $user_id - the user to log
		 @param char $action - the users action, in, out, password fail etc.
	*/
	public function log_access($user_id,$action)
	{
		// record the access - we take an IP address too
			$insert_data=array(
				'user_id'=>$user_id,
				'ip_address'=>$this->input->ip_address(),
				'access_type'=>$action
			);
			$this->db->insert('access',$insert_data);
	}

	public function check_login($cr)
	{
		// one way hash on the password, same as when it was set
		$password_hash=do_hash($cr['password']);

		// the where string looks to see if this users credential entry matches one of their ids
		$where="(email=".$this->db->escape($cr['email'])." or facebook_email=".$this->db->escape($cr['email'])." or user_name=".$this->db->escape($cr['email']).") and password='".$password_hash."'";

		// get the result
        $query=$this->db->select('*')->from('user')->where($where);
        $user=$query->get()->row_array();

		// return 0 or user array, did we find a user or not
		if (count($user)>0)
		{
			$full_user=$this->engage_model->get_user($user,'user');
			return $full_user;
		}
		else
		{
			return 0;
		}
	}

	public function login_form()
	{
		// open the form
		$attr=array(
			'name'=>'login_form',
			'class'=>'form'
		);
		$hidden=array('url'=>uri_string());
		$login_html=form_open('engage/check_login',$attr,$hidden);

		// email field
		$attr=array(
			'id'=>'email',
			'name'=>'email',
			'class'=>'form_field',
			'autofocus'=>'autofocus'
		);
		$login_html.="<label for='email'>Email:</label>";
		$login_html.=form_input($attr,''); // no set_value on email field, for security

		// password field
		$attr=array(
			'id'=>'password',
			'name'=>'password',
			'class'=>'form_field'
		);
		$login_html.="<label for='password'>Password:</label>";
		$login_html.=form_password($attr,''); // no set_value on password field, for security

		// submit button
		$attr=array(
			'name'=>'submit',
			'class'=>'submit'
		);
		$login_html.=form_submit($attr,'login');
		$login_html.=form_close();
		return $login_html;
	}

	/* *************************************************************************
		 change_credentials() - change the users login crednetials
		 @param string $email - email to save
		 @param string $password - password to save
		 @return
	*/
	public function change_credentials($email,$password)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_change_credentials_start');

		$update_data = array(
			'email' => $email,
			'password' => do_hash($password)
		);

		$this->db->where('user_id', $this->user['user_id']);
		$this->db->update('user', $update_data);

		/* BENCHMARK */ $this->benchmark->mark('func_change_credentials_end');
	}

	public function check_register($cr)
	{

		// the where string looks to see if this users credential entry matches one of their ids
		$where="(email=".$this->db->escape($cr['email'])." or facebook_email=".$this->db->escape($cr['email']).") or (user_name=".$this->db->escape($cr['user_name']).")";

		// get the result
        $query=$this->db->select('*')->from('user')->where($where);
        $user=$query->get()->row_array();

		// return 0 or user array, did we find a user or not
		if (count($user)>0)
		{
			return 0;
		}
		else
		{
			// one way hash on the password, same as when it was set
				$password_hash=do_hash($cr['password']);

			// create a username too
				$user_name=$this->node_admin_model->name_to_url($cr['user_name'],0);

			// create a new node
				$insert_data=array(
					'type'=>'user',
					'name'=>$cr['user_name'],
					'url'=>$user_name,
					'visible'=>1,
					'user_name'=>$cr['user_name']
					);
				$this->db->insert('node',$insert_data);
				$user_id=$this->db->insert_id();

				$update_data = array(
					'user_id' =>$user_id,
				);
				$this->db->where('id', $user_id);
				$this->db->update('node', $update_data);

			// now add to the user table
				$insert_data=array(
					'user_id'=>$user_id,
					'user_name'=>$user_name,
					'email'=>$cr['email'],
					'password'=>$password_hash,
					'display_name'=>$cr['user_name']
					);
				$this->db->insert('user',$insert_data);

			// get the user and sign in
				$user=$this->engage_model->get_user(array('user_id'=>$user_id),'user');

			return $user;
		}
	}
}
