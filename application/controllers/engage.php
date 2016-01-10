<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'controllers/universal.php');
/*
 class Login

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 * engage controller - does various tasks associated with engaging the user, such as hash password etc.
*/
    class Engage extends Universal {

	public function __construct()
	{
		parent::__construct();

		$this->load->helper('email_helper');
		$this->load->library('cart');
		$this->load->library('input');
		$this->load->model('basket_model');
		$this->load->model('engage_model');
		$this->load->model('stream_model');
    }

	/* *************************************************************************
		 logout() - perform a logout
		 @return
	*/
	public function logout()
	{
		// store cart in user table
            $this->basket_model->basket_to_user();

		// log some actions
			$this->engage_model->log_access($this->user['user_id'],'O');
			$this->_log_action($this->input->post('url'),"logout","success");

		// destroy session
			$this->session->sess_destroy();

		// log reload
			$this->_log_action($this->config->item('logout_url'),"logout","success");
			$this->_reload($this->config->item('logout_url'),"successfully logged out","success");
	}

    /* *************************************************************************
		check() - checks an email for validity, is it used or just malformed
		@param $get['email'] - the email address sent in by an ajax call
		exits with an output message to tell the user whether their email passed or not
    */
    public function check_email()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_check_start');

		// get array
			$get=$this->get_input_vals();

		if (1==valid_email($get['email']))
		{
			// get any users with this email address
				$user=$this->engage_model->get_user_on_email($get['email']);

			// message based on whether we found one
				if (count($user)>0)
				{
					$output="<span class='red'>!! email ".$get['email']." is taken</span>";
				}
				else
				{
					$output="<span class='green'>email ".$get['email']." is valid for registration</span>";
				}
		}
		else
		{
            $output="<span class='red'>!! email ".$get['email']." is invalid</span>";
		}
        exit(json_encode($output));

        /* BENCHMARK */ $this->benchmark->mark('func_check_end');
    }


    /* *************************************************************************
     check_credentials() - checks the input from the user to see whether their new credentials are right
     @reloads with the result of this
    */
    public function check_credentials()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_check_start');

		// get input vals
			$post=$this->get_input_vals();

        if ($post['new_password']!='')
        {
            // the where string looks to see if this users credential entry matches one of their ids
            $where="(email='".$post['email']."' or facebook_email='".$post['email']."') and user_id!=".$this->user['user_id'];

            // get the result
            $query=$this->db->select('*')->from('user')->where($where);
            $user=$query->get()->row_array();

            if (count($user)>0)
            {
				$this->_store_post($post);
                $this->_log_action('change-login-credentials',"change credentials, email in use","fail");
                $this->_reload('change-login-credentials',"this email is already in use","fail");
            }
            else
            {
                $user=$this->db->select('*')->from('user')->where(array('user_id'=>$this->user['user_id']))->get()->row_array();
                if (do_hash($post['old_password'])!=$user['password'])
                {
                    $this->_store_post($post);
                    $this->_log_action('change-login-credentials',"change credentials, old pword bad","fail");
                    $this->_reload('change-login-credentials',"the old password does not match","fail");
                }
                else
                {
					$this->engage_model->change_credentials($post['email'],$post['new_password']);

                    $this->_log_action('change-login-credentials',"change credentials","success");
                    $this->_reload('change-login-credentials',"your details were successfully changed","success");
                }
            }
        }
        else
        {
				$this->_store_post($post);
                $this->_log_action('change-login-credentials',"change credentials, no new pword","fail");
                $this->_reload('change-login-credentials',"the new password must be filled","fail");
        }

        /* BENCHMARK */ $this->benchmark->mark('func_check_end');
    }

    /* *************************************************************************
     check_username() - check a user name to see if it has already been taken
     @return
    */
    public function check_username()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_check_start');

		// get array
			$get=$this->get_input_vals();

		// check username
			$username_check=$this->engage_model->check_username($get['user_name']);

		// set the message for the username
			if (count($username_check['check'])>0)
			{
				$output="<span class='red'>!! the username ".$get['user_name']." [".$username_check['uname']."] has already been taken</span>";
			}
			else
			{
				$output="<span class='green'>the username ".$get['user_name']." [".$username_check['uname']."] is available</span>";
			}

        exit(json_encode($output));

        /* BENCHMARK */ $this->benchmark->mark('func_check_end');
    }

	public function check_login()
	{
		// get input vals
			$post=$this->get_input_vals();

		$user=$this->engage_model->check_login($post);

		if (is_array($user))
		{
			// log the sign in
				$this->engage_model->sign_in($user,'I');
				$this->_log_action($post['url'],"login","success");

			// retrieve the basket
				$old_cart=$this->cart->contents();

				// get all the user cart stuff, including any voucher they have applied
					$cart=json_decode($user['basket'],1);
					if (is_array($cart))
					{
						foreach ($cart as $c)
						{
							$this->cart->insert($c);
						}
					}

				// add in the pre login bagged items - these will over-ride those already in there so a user who returns, bags and logs in
				// won't have there amounts altered by login even if they previously bagged the same product
					foreach ($old_cart as $c)
					{
						if ($c['id']!='voucher') // dont double apply vouchers
						{
							$this->cart->insert($c);
						}
					}

				// recalculate the postage for the new basket
					$this->basket_model->do_postage();

				// apply the voucher to the basket too so the retrieved totals are correct
					$this->voucher_model->apply_voucher();

			// redirect depends on user type
				$redirect_signin=$this->config->item('redirect_signin');

				if (is_array($redirect_signin) &&
					isset($redirect_signin[$user['user_type']]))
				{
					$reload_url=str_replace("%_USERNAME", $user['url'], $redirect_signin[$user['user_type']]);
				}
				else
				{
					$reload_url=$redirect_signin;
				}

			// reload the logged in user
				$this->_reload($reload_url,"login was successful","success");
		}
		else
		{
			// record the fail
				$this->engage_model->log_access(null,'F');

			// log and reload
				$this->_log_action($post['url'],"login","fail");
				$this->_reload($post['url'],"the login details were not correct","fail");
		}
	}

    /*
     check_register() - checks the registration details for correctness
    */
    public function check_register()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_check_start');

		// get input vals
			$post=$this->get_input_vals();

        if ($post['password']!='')
        {
			// see if this user is already registered
				$user=$this->engage_model->check_register($post);

            if (is_array($user))
            {
				// log
					$this->engage_model->sign_in($user,'R');

				// confirm email
					$this->send_email($post['email'],"Signup confirmation from <".$this->config->item('from_email').">","you just registered on the site","you just registered on the site");

                // get the sign in redirect value
					$redirect_signin=$this->config->item('redirect_signin');

					if (is_array($redirect_signin) &&
						isset($redirect_signin[$user['user_type']]))
					{
						$reload_url=$redirect_signin[$user['user_type']];
					}
					else
					{
						$reload_url=$redirect_signin;
					}

				// log reload
					$this->_log_action($post['url'],"register","success");
					$this->_reload($reload_url,"register was successful - you have now been logged into the site","success");
            }
            else
            {
                // record the fail
					$this->engage_model->log_access(null,'F');

				// store the post
					$this->_store_post($post);

				// log reload
					$this->_log_action($post['url'],"register","fail");
					$this->_reload($post['url'],"this email is already in use","fail");
            }
        }
        else
        {
            // record the fail
				$this->engage_model->log_access(null,'F');

			// store the post
				$this->_store_post($post);

			// log reload
				$this->_log_action($post['url'],"register","fail");
				$this->_reload($post['url'],"the password was not filled","fail");
        }

        /* BENCHMARK */ $this->benchmark->mark('func_check_end');
    }
}
