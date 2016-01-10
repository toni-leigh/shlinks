<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Admin_data_array_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Admin_data_array_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

		// helpers
			$this->load->helper('form');
			$this->load->helper('html');
			$this->load->helper('image');
			$this->load->helper('string');

		$this->load->model('share_model');
    }

	/* *************************************************************************
		 admin_page_data() - gets some data for administration pages
		 @param array $data - the data array into which we will add new elements
		 @param array $user - the signed in user
		 @return
	*/
	public function admin_page_data($data,$user,$node)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_admin_page_data_start');

		/* SPECIFIC */
			// all images
				if ('all-images'==$node['url'])
				{
					$this->load->model('image_model');
					$this->load->model('image_upload_model');

					$data['all_images']=$this->image_model->all_images();

					$data['image_panels']=$this->image_upload_model->image_panels($data['all_images']);
				}

			// contacts
				$data['contact_list']=array();
				if ('contact-list'==$node['url'])
				{
					$query=$this->db->select('*')->from('contact')->where(array('responded'=>1))->order_by('contact_time desc');
					$res=$query->get();
					$data['contact_list']['responded']=$res->result_array();

					$query=$this->db->select('*')->from('contact')->where(array('responded'=>0))->order_by('contact_time desc');
					$res=$query->get();
					$data['contact_list']['unresponded']=$res->result_array();
				}

			// manual
				if ('_man'==$node['url'])
				{
					$data['default_nodes']=$this->node_model->get_nodes(array('id <='=>1000),null,'id');
					$data['head_views']=$this->node_model->get_template_views('head');
					$data['footer_views']=$this->node_model->get_template_views('foot');
				}

			// miscellaneous
				if ('miscellaneous-admin'==$node['url'])
				{
					// feeds, if super admin then get all blog feeds, else just get this users
						$bfl='';
						$gfl='';
						$sm='';

						if ('super_admin'==$user['user_type'])
						{
							// get all the blog feeds and google product lists as links
								$users=$this->node_model->get_nodes(array('type'=>'user'));

							// look at each, creating a link if the user has created a blog
								foreach ($users as $u)
								{
									$file=$_SERVER['DOCUMENT_ROOT']."/rss/".$u['id']."blog.xml";
									if (file_exists($file))
									{
										$bfl.="<a class='admisc_link' href='/rss/".$u['id']."blog.xml'>".$u['name']." blog feed</a>";
									}

									$file=$_SERVER['DOCUMENT_ROOT']."/google/".$u['id']."product_csv.txt";
									if (file_exists($file))
									{
										$gfl.="<a class='admisc_link' href='/google/".$u['id']."product_csv.txt'>".$u['name']." google feed</a>";
									}
								}

							// get the site map
								$sm="<a class='admisc_link' href='/sitemap.xml'>sitemap</a>";
						}
						else
						{
							// just get this users blog feed
								$file=$_SERVER['DOCUMENT_ROOT']."/rss/".$user['id']."blog.xml";
								if (file_exists($file))
								{
									$bfl.="<a class='admisc_link' href='/rss/".$user['id']."blog.xml'>my blog feed</a>";
								}

							// just get this users google feed
								$file=$_SERVER['DOCUMENT_ROOT']."/google/".$user['id']."product_csv.txt";
								if (file_exists($file))
								{
									$bfl.="<a class='admisc_link' href='/google/".$u['id']."product_csv.txt'>my google feed</a>";
								}

						}

						$data['blog_feed_links']=$bfl;
						$data['google_product_links']=$gfl;
						$data['sitemap_link']=$sm;
				}

			// newsletter signups
				if ('newsletter-signups'==$node['url'])
				{
					$query=$this->db->select('*')->from('newsletter')->order_by('sign_up_time desc');
					$res=$query->get();
					$data['signups']=$res->result_array();
				}

			// voucher type drop down
				if ('voucher-definition'==$node['url'])
				{
					// get the voucher types
						$query=$this->db->select('*')->from('voucher_type')->order_by('voucher_type_name');
						$res=$query->get();
						$vts=$res->result_array();

						$vts_by_id=array();

					// get the vouchers themselves
						$query=$this->db->select('*')->from('voucher')->order_by('voucher_id');
						$res=$query->get();

					// format some description values for the voucher types - more human readable
						for ($x=0;$x<count($vts);$x++)
						{
							$d='';

							if ('percentage'==$vts[$x]['adjust_type'] &&
								100==$vts[$x]['adjust_value'])
							{
								$d.='FREE '.$vts[$x]['adjust_focus'];
							}
							else
							{
								if ('pound'==$vts[$x]['adjust_type'] ? $adjust='&pound;'.$vts[$x]['adjust_value'] : $adjust=$vts[$x]['adjust_value'].'%' );
								$d.=$adjust.' off '.$vts[$x]['adjust_focus'];
							}
							if ($vts[$x]['threshold']>0)
							{
								$d.=' over &pound;'.$vts[$x]['threshold'];
							}

							$vts[$x]['details']=$d;

							$vts_by_id[$vts[$x]['voucher_type_id']]=$vts[$x];
						}

					// add to data
						$data['voucher_types']=$vts;
						$data['voucher_types_by_id']=$vts_by_id;
						$data['vouchers']=$res->result_array();
				}

		/* BENCHMARK */ $this->benchmark->mark('func_admin_page_data_end');

		return $data;
	}
}
