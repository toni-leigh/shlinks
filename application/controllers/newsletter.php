<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'controllers/universal.php');
/*
 class Newsletter

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
 * newsletter controller - saves a newsletter sign-up
*/
    class Newsletter extends Universal {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('newsletter_model');
		$this->load->helper('email');
    }

	/* *************************************************************************
		 csv() - creates a csv of the newsletter table
		 @return
	*/
	public function csv()
	{
        /* BENCHMARK */ $this->benchmark->mark('func_csv_start');

		$this->load->helper('download_helper');

		$csv=$this->newsletter_model->newsletter_csv();

		force_download('signups.csv',$csv);

        /* BENCHMARK */ $this->benchmark->mark('func_csv_end');
	}

	public function signup()
	{
		// get the post
			$post=$this->get_input_vals();

		$email=$post['newsletter_email'];
		if (valid_email($email) &&
			0==strlen($post['phone_number']))
		{
			$this->newsletter_model->save_email($email);

			$this->_log_action($post['url'],'newsletter signup','success');

			// send email
				$this->send_email($email,"Sign up service from <".$this->config->item('from_email').">","Thank you for signing up","Thank you for signing up to our newsletter");


			// log, reload
				$this->_log_action($post['url'],"you successfully signed up to the email newsletter: ".$email."","success");
				$this->_reload($post['url'],"you successfully signed up to the email newsletter: ".$email."","success");
		}
		else
		{
			$this->_store_post($post);
			if ($email=='')
			{
				$this->_log_action($post['url'],'newsletter signup','fail - empty');
			}
			else
			{
				$this->_log_action($post['url'],'newsletter signup','fail - bad email');
			}

			// log, reload
				$this->_log_action($post['url'],"the email address was invalid","fail");
				$this->_reload($post['url'],"the email address was invalid","fail");
		}
	}
}
