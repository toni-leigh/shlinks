<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'controllers/universal.php');
/*
 class Contact

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
 * contact controller
 *   - allows for a contact to be submitted through the site
*/
    class Contact extends Universal {

	public function __construct()
	{
		parent::__construct();
    }

    public function send_contact()
    {
		// get the post
			$post=$this->get_input_vals();

        // we need these to make this work
			$this->load->helper('url');
			$this->load->helper('email');
			$this->load->library('form_validation');
			$this->load->model('node_model');

		// get the contact us page (id=11)
			$contact_page=$this->node_model->get_node(7,'page');

        // set rules
			$this->form_validation->set_rules('message', 'Message', 'required');

        // validate and display according
			if ($this->form_validation->run() == FALSE or
				strlen($post['phone_number'])>0)
			{
				// fail
					$this->_store_post($post);
					$this->_log_action("contact","contact","fail - not filled");
					if (strlen($post['phone_number'])>0)
					{
						$this->_log_action("contact","contact","fail - bot filled on field");
					}
					$this->_reload($contact_page['url'],"please fill in your name and message - contact details are optional but required if you want us to get back in touch","fail");
			}
			else
			{
				// email body
					$em_bod="This is a contact submitted through your website contact form:<br/><br/>";
					$em_bod.="The contact came from:<br/><br/>";
					$em_bod.="<strong>".$post['contact_name']."</strong><br/><br/>";
					if (isset($post['contact_name']) &&
						strlen($post['contact_name'])>0)
					{
						$em_bod.="The contact came from:<br/><br/>";
						$em_bod.="<strong>".$post['contact_name']."</strong><br/><br/>";
					}

					if (isset($post['contact_phone']) &&
						strlen($post['contact_phone'])>0)
					{
						$em_bod.="Phone number:<br/><br/>";
						$em_bod.="<strong><a href='tel:".$post['contact_phone']."'>".$post['contact_phone']."</a></strong><br/><br/>";
					}

					if (isset($post['contact_email']) &&
						strlen($post['contact_email'])>0)
					{
						$em_bod.="Email Address:<br/><br/>";
						$em_bod.="<strong><a href='mailto:".$post['contact_email']."'>".$post['contact_email']."</a></strong><br/><br/>";
					}
					$em_bod.="Their message is:<br/><br/>";
					$em_bod.="<strong>".$post['message']."</strong><br/><br/>";
					$em_bod.="NB automated email, please do not reply, use the email address in the message above";

				// send the email
					$emails=$this->config->item('site_email');

					if (is_array($emails))
					{
						foreach ($emails as $email)
						{
							$this->send_email($email,"Contact notification email from <".$this->config->item('from_email').">","Contact through your website",$em_bod);
						}
					}
					else
					{
						$this->send_email($emails,"Contact notification email from <".$this->config->item('from_email').">","Contact through your website",$em_bod);
					}


				// record the contact
					$insert_data=array(
						'contact_name'=>$post['contact_name'],
						'contact_phone'=>$post['contact_phone'],
						'contact_email'=>$post['contact_email'],
						'message'=>$post['message']
					);
					$this->db->insert('contact',$insert_data);

				// success
					$this->_log_action("contact","contact","success");
					$this->_reload($contact_page['url'],"Your contact was sent successfully.","success");
			}
    }

    public function save_responded()
    {
    	$post=$this->get_input_vals();

    	foreach ($post as $key=>$value)
    	{
    		if (0===strpos($key, "contact"))
    		{
    			$id=str_replace("contact", "", $key);

    			$update_data = array(
    			    'responded'=>$value
    			);

    			$this->db->where('contact_id',$id);
    			$this->db->update('contact',$update_data);
    		}
    	}

		// success
			$this->_log_action("responded saved","responded saved","success");
			$this->_reload("contact-list","Responded or not updated.","success");

    }
}
