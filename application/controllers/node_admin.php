<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Node_admin

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
    class Node_admin extends Node {

    public function __construct()
    {
        parent::__construct();

        /* BENCHMARK */ $this->benchmark->mark('node_admin_start');

        // models
            $this->load->model('node_model');
            $this->load->model('node_admin_model');
            $this->load->model('rss_model');
			$this->load->model('sitemap_model');
            $this->load->model('project_data_array_model');
            $this->load->model('variation_model');

        // libraries
            $this->load->library('form_validation');
            $this->load->library('input');

        // helpers
            $this->load->helper('data');

        // admin config
			$this->config->load('admin');

        // properties

        /* BENCHMARK */ $this->benchmark->mark('node_admin_end');
    }

	/* *************************************************************************
		 edit() - saves the contents of a node form, either creating a new node or editing a current one
		 @param string $type - the type of node that will be edited / created, required for addition of data to the specific table
		 @param int $id - the id of the node to edit, if null this is a create operation and a new node will be created
		 @return
	*/
	public function edit($type,$id=null,$cat=null)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_edit_start');

        // get the current node to be edited, or initialise a default for create pages
            if (null==$id)
			{
				$this->data['edit_node']=array('id'=>'');
			}
			else
			{
				if (in_array($type,$this->config->item('add_with_category')) &&
					$cat!=null)
				{
					// initialise a node with the $id as the categoey
					// this is only ever a create and allows for an 'add new' link to load a form with a category populated (UX)
					// the type is the categorised type and this is only done if the link includes the category id - the id is ignored
						$this->data['edit_node']=array('id'=>'','category_id'=>$cat);
				}
				else
				{
					$this->data['edit_node']=$this->node_model->get_node($id,$type);
				}
			}
            $this->data['type']=$type;

		// admin tags
			$query=$this->db->select('*')->from('admin_tag')->order_by('name');
			$res=$query->get();
			$this->data['admin_tags']=$res->result_array();

        // put a tiny mince in the js
            $this->data['admin_js']="<script type='text/javascript' src='/js/tinymce/tiny_mce.js'></script>";
            $this->data['admin_js'].="<script type='text/javascript'>";
            // apply the tiny mince
            $this->data['admin_js'].="tinyMCE.init({";
			if (is_numeric($this->config->item('admin_text_width')) ? $width=$this->config->item('admin_text_width') : $width=660 );
            $this->data['admin_js'].="width: '".$width."',";
            $this->data['admin_js'].="mode : 'textareas',";
            $this->data['admin_js'].="plugins: 'autolink,lists,spellchecker,advimage,advlink,inlinepopups,contextmenu,paste,directionality,nonbreaking',";
            $this->data['admin_js'].="theme : 'advanced',";
            $this->data['admin_js'].="convert_urls : false,";
            $this->data['admin_js'].="body_class : 'tiny_mince_body',";
            $this->data['admin_js'].="content_css : '/style/tiny_mce.css',";
            $this->data['admin_js'].="theme_advanced_buttons1 : 'bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,spellchecker,bullist,numlist,|,undo,redo,|,link,unlink',";
            $this->data['admin_js'].="theme_advanced_buttons2 : '',";
            $this->data['admin_js'].="theme_advanced_buttons3 : '',";
            $this->data['admin_js'].="theme_advanced_toolbar_location : 'top',";
            $this->data['admin_js'].="theme_advanced_toolbar_align : 'left',";
            $this->data['admin_js'].="theme_advanced_statusbar_location : 'bottom',";
            $this->data['admin_js'].="theme_advanced_resizing : true,";
            $this->data['admin_js'].="external_link_list_url : '/user_files/".$this->user['user_id']."_link_list.js',";
            $this->data['admin_js'].="external_image_list_url : '/user_files/".$this->user['user_id']."_image_list.js',";
            $this->data['admin_js'].="elements : 'node_html',";
            $this->data['admin_js'].="setup: function(ed) {";
		    $this->data['admin_js'].="ed.onKeyUp.add(function(ed, e) {";
		    $this->data['admin_js'].="saveForm._save_tinymce_value(ed);";
		    $this->data['admin_js'].="});";
			/*$this->data['admin_js'].="ed.onInit.add(function(ed, evt) {";

			$this->data['admin_js'].="var dom = ed.dom;";
			$this->data['admin_js'].="var doc = ed.getDoc();";

			$this->data['admin_js'].="tinymce.dom.Event.add(doc, 'blur', function(e) {";
			$this->data['admin_js'].="alert('blur!!!');";
			$this->data['admin_js'].="});";
			$this->data['admin_js'].="});";*/
		    $this->data['admin_js'].="}";
            $this->data['admin_js'].="});";
            $this->data['admin_js'].="</script>";

		// human readable type
			$this->data['human_type']=$this->node_model->get_human_type($type);

		// node types
			$query=$this->db->select('distinct(type)')->from('node')->order_by('type');
			$res=$query->get();
			$this->data['node_types']=$res->result_array();

        $this->display_node('create_'.$type);

		/* BENCHMARK */ $this->benchmark->mark('func_edit_end');
	}

    /* *************************************************************************
         save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save($type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        // set rules
			$nreqs=$this->config->item('node_admin_required');
			if (isset($nreqs[$type]) &&
				is_array($nreqs[$type]))
			{
				$this->form_validation->set_rules($nreqs[$type]);
			}
			elseif (isset($nreqs['default']) &&
				is_array($nreqs['default']))
			{
				$this->form_validation->set_rules($nreqs['default']);
			}
			else
			{
				$this->form_validation->set_rules('name', 'Name', 'required');
				$this->form_validation->set_rules('tags', 'Tags', 'required');
				$this->form_validation->set_rules('short_desc', 'Short Description', 'required');
				$this->form_validation->set_rules('node_html', 'Short Description', 'required');
			}

        // get post array and id
			$post=$this->get_input_vals();
			if (is_numeric($post['id']) ? $action='editted' : $action='created' );
			if (is_numeric($post['id']) ? $url=$type.'/'.$post['id'].'/edit' : $url=$type.'/create' );

        // if the rules are passed
		if ($this->form_validation->run() == true)
        {
            $this->_log_action("node ".$type." saved","node ".$type." saved","node ".$type." saved");

            $id=$this->node_admin_model->node_save($post,$type);

            // switch over type to get the individual node type save function
            switch ($type)
            {
                case 'blog':
                    $this->node_admin_model->blog_save($post,$id);
                    break;
                case 'calendar':
                    $this->node_admin_model->calendar_save($post,$id);
                    break;
                case 'category':
                    $this->node_admin_model->category_save($post,$id);
                    break;
                case 'mediaset':
                    $this->node_admin_model->mediaset_save($post,$id);
                    break;
                case 'groupnode':
                    $this->node_admin_model->group_save($post,$id);
                    break;
                case 'page':
                    $this->node_admin_model->page_save($post,$id);
                    break;
                case 'product':
                    $this->node_admin_model->product_save($post,$id,$action);
                    break;
                case 'user':
                    $this->node_admin_model->user_save($post,$id);
                    break;
            }

			// do project specific stuff for each node :-)
				$id=$this->project_data_array_model->save_specific($post,$id,$type);

			// step on in the creation process or back to list if its an edit
				if (is_numeric($post['id']))
				{
					$create=0;
					$url=$type.'/list';
				}
				else
				{
					$create=1;
					if ('product'==$type)
					{
						$url=$type.'/'.$id.'/variations';
					}
					elseif ('calendar'==$type)
					{
						$url=$type.'/'.$id.'/events';
					}
					else
					{
						$url=$type.'/'.$id.'/images';
					}
				}

			$this->session->set_userdata(array('admin_last_page'=>''));

            // set the link list
            $this->node_admin_model->update_linklist();

            // reload with success message
            $this->_clear_post();
			$this->_log_action($url,"save","success - node ".$id." ".$action);
            $this->_reload($url,"the $type ".$post['name']." was $action",'success');
        }
        else
        {
            $this->_log_action("node ".$type." save fail","node ".$type." save fail","node ".$type." save fail");

            // reload with fail message
            $this->_store_post($post);
			$this->_log_action($url,"save","fail - $type not $action");
            $this->_reload($url,"please check your form for errors - all fields must be filled",'fail');
        }

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');
    }

	/* *************************************************************************
		 delete() - deletes a node
		 @param string
		 @param numeric
		 @param array
		 @return
	*/
	public function delete($type,$id)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_delete_start');

		$node=$this->node_model->get_node($id);

		$this->node_admin_model->delete_node($type,$id);

		$this->_log_action("delete","delete","success - ".$type." ".$node['name']." deleted");
		$this->_reload($type."/list","success - ".$type." ".$node['name']." deleted",'success');

		/* BENCHMARK */ $this->benchmark->mark('func_delete_end');
	}

	/* *************************************************************************
		 list_nodes() - function that lists all nodes of a certain type
		 @param string $type - type of nodes to list, will show all if null
		 @return
	*/
	public function list_nodes($type=null)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_list_nodes_start');

		// set a session variable here that stores that the last page was the list
			$this->session->set_userdata(array('admin_last_page'=>'list'));

        if (null==$type)
        {
            $type='';
        }
        $this->data['type']=$type;

		// super admin is omnispective
			if ('super_admin'==$this->user['user_type'])
			{
				$clauses=array('type'=>$type,'protected'=>0);
			}
			else
			{
				$clauses=array('type'=>$type,'node.user_id'=>$this->user['user_id'],'protected'=>0);
			}

        $nodes=$this->node_model->get_nodes($clauses,'joined');

		// if this is an events list then get the calendar for each event
			if ('event'==$type)
			{
				$nc=count($nodes);
				for ($x=0;$x<$nc;$x++)
				{
					$nodes[$x]['calendar']=$this->node_model->get_node($nodes[$x]['calendar_id'],'calendar');
				}
			}

        $this->data['nodes']=$nodes;

        $single_image=1;
        for ($x=0;$x<count($nodes);$x++)
        {
            // images for this node, for node output
                $this->data['nodes'][$x]['image']=$this->image_model->get_images($nodes[$x]['id'],$single_image);
        }

		// filter values for select
			$this->data['admin_tags']=$this->node_admin_model->get_admin_tags();

		// human readable type
			$this->data['human_type']=$this->node_model->get_human_type($type);

        // load view
            $this->display_node('list_'.$type);

		/* BENCHMARK */ $this->benchmark->mark('func_list_nodes_end');
	}

	/* *************************************************************************
		 set_all() - sets node values on mass from the submission of a list nodes page
			no type or id parameters required, set all has a form to look at and lists don't display info from anywhere other than 'node'
		 @return
	*/
	public function set_all($type)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_set_all_start');

		// get the post array
			$vals=$this->get_input_vals();

		// perform the mass update
			$message=$this->node_admin_model->mass_update($type,$vals);

		// human type
			$human_type=$this->node_model->get_human_type($type);

		// log, reload
			$this->_log_action("node admin set all","node admin set all".$message,"all ".$human_type."s set");
			$this->_reload($type.'/list',"the $human_type nodes were set<br/>".$message,'success');

		/* BENCHMARK */ $this->benchmark->mark('func_set_all_end');
	}
	/* *************************************************************************
		 undo_mass() - calls a node admin model function to undo mass array
	*/
	public function undo_mass()
	{
		/* BENCHMARK */ $this->benchmark->mark('func_undo_mass_start');

		// undo
			$nvar_count=$this->node_admin_model->undo_mass();

		/* BENCHMARK */ $this->benchmark->mark('func_undo_mass_end');

        // log, reload
			$this->_log_action("undo mass adjust","undo mass adjust","undo mass adjust");
			$this->_reload('product/list',"undo mass adjust: ".$nvar_count." variations were reverted",'success');
	}
	/* *************************************************************************
		 events() -
		 @param string
		 @param numeric
		 @param array
		 @return
	*/
	public function events($type,$id)
	{
		/* BENCHMARK */ $this->benchmark->mark('func_events_start');

		// for events we need the event model
			$this->load->model('events_admin_model');

		// the calendar data
			$this->data['admin_calendar']=$this->node_model->get_node($id,'calendar');

		// the calendar output
			$this->data['month_slider']=$this->events_admin_model->admin_calendar($this->data['admin_calendar']);

		// new event form
			$this->data['event_form']=$this->events_admin_model->event_form($id);

        $this->display_node('calendar_events');

		/* BENCHMARK */ $this->benchmark->mark('func_events_end');
	}

    /* *************************************************************************
         variations() - displays the variation stuff for this node (is currently just used for products)
         @param string
         @param numeric
         @param array
         @return
    */
    public function variations($type,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_variations_start');
            if (($id==null) ? $this->data['edit_node']=array('id'=>'') : $this->data['edit_node']=$this->node_model->get_node($id,$type));

		// we need the variation model for this
			$this->load->model('variation_model');

        // this node id
            $this->data['node_id']=$id;

		// the produc
			$this->data['product']=$this->node_model->get_node($id,'product');

        // get the current variations for this node
            $this->data['nvars']=$this->variation_model->get_nvars($id);

        // all var types (for new variation sets)
            $this->data['var_types']=$this->variation_model->get_var_types();

        // the variation types that apply to this product (how does this product vary)
            $this->data['nvar_types']=$this->variation_model->get_nvar_types($id);

        // adder / preview
            $id="";
            $this->data['adder']=$this->variation_model->nvar_adder($this->data['nvar_types']);
            $this->data['nvar_types']=$this->variation_model->strip_pack_quantities($this->data['nvar_types']);
            $this->data['preview']=$this->variation_model->nvar_add_preview($this->data['nvar_types']);

        // the main variation for this product
            if (isset($this->data['nvars'][0]))
            {
                $this->data['main_var']=$this->data['nvars'][0];
            }
            else
            {
                $this->data['main_var']=array('nvar_id'=>0);
            }

        $this->display_node('node_variations');

        /* BENCHMARK */ $this->benchmark->mark('func_variations_end');
    }

    /* *************************************************************************
         add_variations() - takes imput from the form and saves the node variations
         @return
    */
    public function add_variations()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_variations_start');

		// get the posted vals
			$vals=$this->get_input_vals();

		// add the variations
			$returned=$this->variation_model->add_variations($vals);

		// save the json
			$this->variation_model->set_vjson($returned['node_id']);

		// log, reload
			$this->_log_action("node admin add variations","node admin add variations","all variations set for ".$returned['node_id']);
			$this->_reload('product/'.$returned['node_id'].'/variations',"the new variations were added".$returned['warning'],'success');

        /* BENCHMARK */ $this->benchmark->mark('func_add_variations_end');
    }
    /* *************************************************************************
         save_variations() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save_variations()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_variations_start');

		// get the posted vals
			$vals=$this->get_input_vals();

		// save the variations
			$node=$this->variation_model->save_nvars($vals);

		// save the master sale values in the product table
			if (isset($vals['sale_applied']) &&
				1==$vals['sale_applied'])
			{
				if (is_numeric($vals['master_sale']) ? $master_sale=$vals['master_sale'] : $master_sale=0 );
				$update_data = array(
					'sale_amount' =>$master_sale,
					'sale_type' => $vals['sale_type']
				);

				$this->db->where('node_id', $node['id']);
				$this->db->update('product', $update_data);
			}

		// update the json in the product table
			$this->variation_model->set_vjson($node['id']);

		// step on in the creation process or back to list if its an edit
			if ('list'==$this->session->userdata('admin_last_page'))
			{
				$url=$node['type'].'/list';
			}
			else
			{
				$url=$node['id'].'/images';
			}
			$this->session->set_userdata(array('admin_last_page'=>''));

		$this->_log_action("node admin save variations","node admin save variations","all variations saved for ".$node['id']);

        $this->_reload($url,"the variations were saved",'success');

        /* BENCHMARK */ $this->benchmark->mark('func_save_variations_end');
    }
}
