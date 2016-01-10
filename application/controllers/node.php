<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	require_once (APPPATH.'controllers/universal.php');
/*
 class Node

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
 * node controller
 *   - responds to the user action of requesting a particular url
 *   - loads a node from the db
 *   - calls on both template data models to load up the bits for this node that populate the frame and specific page elements
 *   - deals with an in place save of node html
 *   - stops viewing of nodes that don't exist; the user isn't allowed to view; or are hidden by the node owner
 *   - loads the views that make up the node, using the template views and node specific views
*/
    class Node extends Universal {

	public function __construct()
	{
		parent::__construct();

		/* BENCHMARK */ $this->benchmark->mark('node_constr_start');

		// models
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadda_start');
			$this->load->model('data_array_model');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadad_end');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloaden_start');
			$this->load->model('engage_model');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloaden_end');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadnl_start');
			$this->load->model('newsletter_model');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadnl_end');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadnode_start');
			$this->load->model('node_model');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadnode_end');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadpda_start');
			$this->load->model('project_data_array_model');
		/* BENCHMARK */ $this->benchmark->mark('node_modelloadpda_end');

		// properties
			$this->data=array();
			$this->node=array();
			$this->data['user']=$this->user;

		/* BENCHMARK */ $this->benchmark->mark('node_constr_end');
	}

	/* *************************************************************************
	 display_node() - controls the display of the node, based on what type it is
	 @param String $id - the URL value to id the node, defaults to home page
	 @param string $url_extra - sometimes a url comes with an extra bit, ie, a panel
		this can also be the node id if the id retrieves a category for category navigation
	 return void
	*/
	public function display_node($id=6,$url_extra='',$params=array()) // $id=1 ensures the home page is returned regardless of its name when the url is empty
	{
		/* BENCHMARK */ $this->benchmark->mark('func_view_start');

		// get the node and add it to the data array too
			// hook into the project data array model to set the node
				if (1==method_exists($this->project_data_array_model,'get_node'))
				{
					$this->node=$this->project_data_array_model->get_node($id,$this->user);
				}
				else
				{
					$this->node=$this->node_model->get_node($id);
				}

		// add the extra params to the data for use elsewhere
			$this->data['url_extra']=$url_extra;
			$this->data['params']=$params;

		// get the node details too
			if (is_array($this->node) &&
				isset($this->node['id']))
			{
				$this->data['node_details']=$this->node_model->get_node_details($this->node);

				//and in the node for convenience
					$this->node=array_merge($this->node,$this->data['node_details']);
			}
			else
			{
				$this->page_missing('404',$id,$url_extra);
				exit();
			}

		// count variations, to hide product if there are no variations
			if ('product'==$this->node['type'])
			{
				$variations=json_decode($this->data['node_details']['nvar_json'],true);
				$vcount=count($variations);
			}

		// get the node panel data, i.e. stream for the stream page
			if ($url_extra=='' &&
				!isset($this->data['node_list'])) // default
			{
				$url_extra='details';

				$tabs=$this->config->item('tabs');

				if (isset($tabs[$this->node['type']][0]))
				{
					$url_extra=$tabs[$this->node['type']][0];
				}
			}

			// set it, unless this is a calendar
				if ('calendar'==$this->node['type'])
				{
					$this->data['current_tab']='details';
				}
				else
				{
					$this->data['current_tab']=$url_extra;
				}

		// format the type for human reading - done here so it can be used throughout the data array model
			// basic nodes
			$this->node['human_type']=$this->node_model->get_human_type($this->node['type']);

		// does the signed in user own this node ?
			if ($this->user['user_id']==$this->node['user_id'] or // signed in user owns this node
				$this->user['user_id']==$this->node['id'] or // signed in user is this node
				'super_admin'==$this->user['user_type']) // call the police ? I OWN the police !
			{
				$this->data['owns_node']=1;
			}
			else
			{
				$this->data['owns_node']=0;
			}

		// is this an admin page ? then set a data value to tell us this
			if (isset($this->data['node_details']['admin_page']) &&
				1==$this->data['node_details']['admin_page'])
			{
				$this->data['admin_page']=1;
			}
			else
			{
				$this->data['admin_page']=0;
			}

		// get user type for page show validation
			$utype=$this->user['user_type'];
			if (!$utype) $utype='anon_user';

			if (count($this->node)>0)
			{
				if (1==$this->node[$utype]) // don't show if user not authorised
				{
					// still show invisibles
						if ($this->node['user_id']==$this->user['user_id'] or
							$utype=='super_admin' or
							$utype=='admin_user')
						{
							$show_invisible=1;
						}
						else
						{
							$show_invisible=0;
						}

					if ($this->node['visible']==1 or
						$show_invisible==1)
					{
						if (isset($this->user['subscribed']) ? $subscribed=$this->user['subscribed'] : $subscribed=0 );
						if (0==$subscribed &&
							1==$this->node['restricted'])
						{
							// log fail message
								$this->_log_action($id,"load page","fail - view restricted content");

							// node not found
								echo 'the user is trying to view restricted content without being subscribed';
						}
						else
						{
							if ('product'==$this->node['type'] &&
								0==$vcount)
							{
								// log fail message
									$this->_log_action($id,"product variations","fail - the product was viewed with no variations");

								// node not found
									echo 'please set some variations for this product';
							}
							else
							{
								$this->data['node']=$this->node;
								$this->_set_template_data($this->data['current_tab']);

								/* BENCHMARK */ $this->benchmark->mark('valid_view_start');

								// open the page template
									$open_views=$this->node_model->get_template_views('head');
									foreach ($open_views as $ov)
									{
										$this->load->view($ov['view'],$this->data);
									}

								// get the page - lists handled differently
									if (is_array($this->data['node_list']) &&
										count($this->data['node_list']) &&
										'page'==$this->data['node']['type'])
									{
										$this->load->view("list",$this->data);
									}
									else
									{
										$node_views=explode(",",$this->data['node_details']['node_views']);

										foreach ($node_views as $nv)
										{
											$this->load->view(str_replace('%_PANEL',"node_element/".$this->node['type']."/".$this->data['current_tab'],$nv),$this->data);
										}
									}

								// close the page template
									$close_views=$this->node_model->get_template_views('foot');
									foreach ($close_views as $cv)
									{
										$this->load->view($cv['view'],$this->data);
									}

								/* BENCHMARK */ $this->benchmark->mark('valid_view_end');
							}
						}
					}
					else
					{
						// log fail message
							$this->_log_action($id,"load page","fail - the node is visible=0 in the db");

						// node not found
							$this->page_missing('hidden',$id,$this->data['current_tab'],$this->node);
					}
				}
				else
				{
					// log fail message
						$this->_log_action($this->node['url'],"load page","fail - the user is not authorised to view the page");

					// not authorised to view
						$this->page_missing('unauthorised',$id,$this->data['current_tab'],$this->node);
				}
			}
			else
			{
				// log fail message
					$this->_log_action($id,"load page","fail - there is no db entry for this url");

				// node not found
					$this->page_missing('no db entry',$id,$this->data['current_tab']);
			}

		/* BENCHMARK */ $this->benchmark->mark('func_view_end');
    }

	/* *************************************************************************
		 page_missing() - displayed for a missing page, a 404, unauthorised, hidden etc.

		 @return
	*/
	public function page_missing($fail_type,$id,$url_extra,$node=null)
	{
		$node_404=$this->node_model->get_node(404,'page');

		if (1==$this->config->item('dev_404'))
		{
			// unset session message so we don't get a lagged 404
				$this->session->set_userdata('message','');

			dev_dump('fail type:');
			dev_dump($fail_type);
			dev_dump('id into function:');
			dev_dump($id);
			dev_dump('url extra:');
			dev_dump($this->data['current_tab']);
			dev_dump('retrieved node:');
			dev_dump($node);
		}

		$this->_reload($node_404['url'],'page not found - '.$fail_type,'fail');
	}

	/* *************************************************************************
	 _set_template_data() - sets up the template data, mostly info for headers and footers
	 return void
	*/
	private function _set_template_data($panel)
	{
		/* BENCHMARK */ $this->benchmark->mark('func__set_template_data_start');

		$this->data=$this->data_array_model->frame_data($this->data,$this->user,$this->node);
		$this->data=$this->data_array_model->node_details_data($panel,$this->data,$this->user,$this->node);
		$this->data=$this->data_array_model->page_specific_data($this->data,$this->user,$this->node);
		if (1==$this->data['admin_page'])
		{
			$this->config->load('admin');
			$this->load->model('admin_data_array_model');
			$this->data=$this->admin_data_array_model->admin_page_data($this->data,$this->user,$this->node);
		}
		$this->data=$this->project_data_array_model->project_specific_template_data($panel,$this->data,$this->user);

		// finally if this is the manual, store the entire array as an array in itself, to be iterated over in manual
			if (100==$this->node['id'])
			{
				$this->data['man_data_array']=$this->data;
			}

		/* BENCHMARK */ $this->benchmark->mark('func__set_template_data_end');
	}

	/* *************************************************************************
		 inplace_save() - saves the node from the in place editor
		 @param string $type - the type of node, this function points at the the individual tables
		 @param int $id - the node id, this is never a create, as it is in place editing
		 @return void
	*/
	public function inplace_save($type,$id)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_inplace_save_start');

			$this->load->model('node_admin_model');
			$this->load->model('sitemap_model');

		// save the new node values
			$vals=$this->get_input_vals();
			$node=$this->node_admin_model->inplace_save($id,$type,$vals);

        // update sitemap.xml
            $this->sitemap_model->generate_sitemap();

		// homepage we just use empty rtaher than the actual url to avoid two urls pointing at the same place
			if (1==$node['id'] ? $reload="" : $reload=$node['url'] );

		/* BENCHMARK */ $this->benchmark->mark('func_inplace_save_end');

		// log, reload
			$this->_log_action($reload,"The ".ucfirst($type)." has been successfully saved","success");
			$this->_reload($reload,"The ".ucfirst($type)." has been successfully saved","success");
	}
}
