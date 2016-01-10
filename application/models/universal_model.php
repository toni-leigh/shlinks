<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 class Universal_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
    class Universal_model extends CI_Model {

	public function __construct()
	{
        parent::__construct();

        /* BENCHMARK */ $this->benchmark->mark('universal_model_constr_start');

		$this->output->enable_profiler($this->config->item('profiler'));

		$this->load->database();

        /* BENCHMARK */ $this->benchmark->mark('universal_model_constr_end');
	}

	/* *************************************************************************
		 send_email() -
		 @param string
		 @param numeric
		 @param array
		 @return
	*/
	public function send_email($to,$from,$subject,$body,$send='LIVE')
	{
		/* BENCHMARK */ $this->benchmark->mark('func_send_email_start');

        //add content to db
			$insert_ID=0;
			if ($send!='BACKUP')
			{
				$insert_email=mysql_query("insert into email_sent (email_address,email_subject,email_body,ip_address) values ('".addslashes($to)."','".addslashes($subject)."','".addslashes($body)."','".$_SERVER["REMOTE_ADDR"]."')") or die(mysql_error());
				$insert_ID=mysql_insert_id();
			}

        //only actually send if live
            if (in_array($send,array("LIVE","BACKUP")))
            {
                //send the email
                $headers="MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\n";
                $headers.="From: ".$this->config->item('site_name')." ".$from." \r\n";
                if (mail($to,$subject,$body,$headers))
                {
                    //echo "Success";
                    $update=mysql_query("update email_sent set email_sent=1 where email_id=".$insert_ID);
                }
                else
                {
                    //echo "Fail";
                    $update=mysql_query("update email_sent set email_sent=0 where email_id=".$insert_ID);
                }
            }

		/* BENCHMARK */ $this->benchmark->mark('func_send_email_end');
	}

	/* *************************************************************************
		 get_image() - gets the thumbnail from the node or the default image
		 @param array $node
		 @param numeric $resize
		 @return an image src
	*/
	public function get_image($node,$resize)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_get_image_start');

		if (strlen($node['image'])>0)
		{
			return str_replace('t300','t'.$resize,$node['image']);
		}
		else
		{
			return '/img/default_image.png';
		}

		/* BENCHMARK */ $this->benchmark->mark('func_get_image_end');
	}

	/* *************************************************************************
		 get_image() - gets the thumbnail from the node or the default image
		 @param array $node
		 @param numeric $resize
		 @return an image src
	*/
	public function get_author($node)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_get_image_start');

		if (isset($node['author']) &&
			strlen($node['author'])>0)
		{
			return $node['author'];
		}
		else
		{
			return $this->config->item('default_blog_author');
		}

		/* BENCHMARK */ $this->benchmark->mark('func_get_image_end');
	}
}
