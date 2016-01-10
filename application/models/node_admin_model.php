<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Node_admin_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
    class Node_admin_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        /* BENCHMARK */ $this->benchmark->mark('Node_admin_model_start');

        // models
            $this->load->model('image_model');
            $this->load->model('node_model');
            $this->load->model('rss_model');
            $this->load->model('stream_model');

        // libraries

        // helpers
            $this->load->helper('data');
            $this->load->helper('form');
            $this->load->helper('string');

        // properties

        /* BENCHMARK */ $this->benchmark->mark('Node_admin_model_end');
    }

    /* *************************************************************************
         name_to_url() - converts the name of the item to a unique url, adding suffix '-n' if the resulting url is not unique
         @param string $name - the name from the freshly submitted form
         @param int $id - the id of the currently edited node, we don't want the unique test to fail because it finds itself
         @return string $url - the converted, unique url string
    */
    public function name_to_url($name,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_name_to_url_start');

        // convert, remove all chars apart from alphanumeric and hyphen, and do some other stuff
            $name_to_url=strtolower(str_replace("--","-",str_replace(" ","-",preg_replace("/[^0-9a-z ]+/i","",trim(stripslashes($name))))));

        // look for this url
            $query=$this->db->select('*')->from('node')->where(array('id !='=>$id,'url'=>$name_to_url));
            $res=$query->get();
            $result=$res->result_array();

        // check if its unique, if not we need to add something
            if (count($result))
            {
                // get all with this url
                    $result=$this->node_model->get_nodes(array('name'=>$name),null,'url desc');

                // add the suffix
                    $this->load->helper('string');
                    $name_to_url=increment_string($result[0]['url']);
            }

        // remove a hiphen off the end if the replace stuff has left a trailing hyphen
            if (0===strpos(strrev($name_to_url),'-'))
            {
                $name_to_url=substr($name_to_url,0,-1);
            }

        return $name_to_url;

        /* BENCHMARK */ $this->benchmark->mark('func_name_to_url_end');
    }

    /* *************************************************************************
         node_save() - saves the basic node data into the node table
         @param array $vals - the array of values to save (this is the post array)
         @param string $type - the type of node
         @return int $id - the id of the node just saved
    */
    public function node_save($vals,$type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_node_save_start');

            $this->load->helper('image');

            $user=$this->node_model->get_node($this->user['user_id']);

        // grab the vals for other tables that dont go into node table
            $node_html=(isset($vals['node_html'])) ? $vals['node_html'] : "";

        // id key (we use user_id for user table, node_id for everything else)
            if ($type=='user' ? $id_key='user_id' : $id_key='node_id' );

        // get rid of the form values we don't store
            unset($vals['node_html']);
            unset($vals['admin_tags']);
            unset($vals['submit']);
            if (isset($vals['map_postcode']))
            {
                unset($vals['map_postcode']);
            }
            if (isset($vals['map_search']))
            {
                unset($vals['map_search']);
            }

        // default cat id if this is category
            if ('category'==$type)
            {
                $vals['category_id']=0;
            }

        // show edit button set
            $vals['show_edit']=$this->config->item($type.'_show_edit');

        // show comments button set
            $vals['show_comments']=$this->config->item($type.'_show_comments');

        // in all cases set updated value
            $vals['updated']=time();

        // for the stream
            $str_name=$vals['name'];

        // video type needs video display
            if ('video'==$type or
                (isset($vals['video_src']) && strlen($vals['video_src'])))
            {
                $vals['video']=1;
            }
            else
            {
                $vals['video']=0;
            }

        // update if id is numeric, or this is a create, do some extra stuff and insert
            if (is_numeric($vals['id']))
            {
                // id makes code clearer
                    $id=$vals['id'];

                // get the old node before updating, to update other tables
                    $old_node=$this->node_model->get_node($id,$type);

                // only this user can update their own nodes
                    if ($old_node['user_id']==$this->user['user_id'] or
                        'super_admin'==$this->user['user_type'])
                    {
                        // this is an edit, id is present
                            $specific_vals=$vals;
                            foreach ($vals as $k=>$v)
                            {
                                if ($k!='category_id' && $k!='id' && $k!='latitude' && $k!='longitude' &&
                                    $k!='name' && $k!='short_desc' && $k!='tags' && $k!='updated' && $k!='url' &&
                                    $k!='video' && $k!='video_src')
                                {
                                    unset($vals[$k]);
                                }
                            }

                            // make categories into json
                                if (isset($vals['category_id']))
                                {
                                    if (is_array($vals['category_id']))
                                    {
                                        // for old site use text cat
                                            asort($vals['category_id']);
                                            $vals['category_id']=json_encode($vals['category_id']);
                                    }
                                }
                                else
                                {
                                    $vals['category_id']=501;
                                }

                            unset($vals['id']);
                            $this->node_update($id, $vals);

                        // update the specific values
                            unset($specific_vals['category_id']);
                            unset($specific_vals['id']);
                            unset($specific_vals['latitude']);
                            unset($specific_vals['longitude']);
                            unset($specific_vals['name']);
                            unset($specific_vals['short_desc']);
                            unset($specific_vals['show_comments']);
                            unset($specific_vals['show_edit']);
                            unset($specific_vals['tags']);
                            unset($specific_vals['updated']);
                            unset($specific_vals['url']);
                            unset($specific_vals['video']);
                            unset($specific_vals['video_src']);
                            // don't set the until date in the calendar - we set that after we've done the add cell logic
                                if ('calendar'==$type)
                                {
                                    unset($specific_vals['until_date']);
                                }

                            // convert arrays into json
                                foreach ($specific_vals as $k=>$v)
                                {
                                    if (is_array($v))
                                    {
                                        $specific_vals[$k]=json_encode($v);
                                    }
                                }

                            $specific_vals['node_html']=$node_html;

                            $this->db->where($id_key, $id);
                            $this->db->update($type, $specific_vals);

                        // set up some data for the action record to be made
                            $time=time();
                            $image=$this->image_model->get_images($id,1);

                        // the signed in user updates the current node
                            $vals['id']=$id;
                            $vals['type']=$type;
                            $vals['url']=$old_node['url'];
                            $this->stream_model->store_action(1,$this->user,$vals,$old_node['user_id']);

                        // the signed in user adds video to the current node
                            if (isset($old_node['video_src']) && isset($vals['video_src']))
                            {
                                if ($old_node['video_src']=='' &&
                                    strlen($vals['video_src'])>0)
                                {
                                    $this->stream_model->store_action(8,$this->user,$vals,$old_node['user_id']);
                                }

                                if (strlen($old_node['video_src'])>0 &&
                                    strlen($vals['video_src'])>0 &&
                                    $vals['video_src']!=$old_node['video_src'])
                                {
                                    $this->stream_model->store_action(9,$this->user,$vals,$old_node['user_id']);
                                }
                            }
                    }
            }
            else
            {
                // add the user details to the array
                    $vals['user_id']=$this->user['user_id'];
                    $vals['user_name']=$this->user['display_name'];
                    $vals['user_image']=$this->user['main_image'];
                    $vals['show_tabs']=$this->config->item($type."_show_tabs");

                // get the unique url for the node
                    if (!isset($vals['url']) &&
                        $type!='image') // images have special urls
                    {
                        $vals['url']=$this->name_to_url($vals['name'],$vals['id']);
                    }

                // comments on or off
                    //if (isset($this->config->item($type."_comments")) ? $vals['show_comments']=$this->config->item($type."_comments") : $vals['show_comments']=0 );

                // then create
                    $specific_vals=$vals;
                    foreach ($vals as $k=>$v)
                    {
                        if ($k!='category_id' && $k!='id' && $k!='latitude' && $k!='longitude' && $k!='name' &&
                            $k!='short_desc' && $k!='show_comments' && $k!='show_tabs' &&
                            $k!='tags' && $k!='type' && $k!='updated' && $k!='url' &&
                            $k!='user_id' && $k!='user_image' && $k!='user_name' && $k!='video' && $k!='video_src')
                        {
                            unset($vals[$k]);
                        }
                    }
                    unset($vals['id']);
                    $vals['type']=$type;
                    $this->db->insert('node',$vals);
                    $id=$this->db->insert_id();

                // create a new entry in the 'type' table
                    unset($specific_vals['category_id']);
                    unset($specific_vals['id']);
                    unset($specific_vals['latitude']);
                    unset($specific_vals['longitude']);
                    unset($specific_vals['name']);
                    unset($specific_vals['short_desc']);
                    unset($specific_vals['show_comments']);
                    unset($specific_vals['show_comments']);
                    unset($specific_vals['show_edit']);
                    unset($specific_vals['show_tabs']);
                    unset($specific_vals['tags']);
                    unset($specific_vals['type']);
                    unset($specific_vals['updated']);
                    unset($specific_vals['url']);
                    unset($specific_vals['user_id']);
                    unset($specific_vals['user_image']);
                    unset($specific_vals['user_name']);
                    unset($specific_vals['video']);
                    unset($specific_vals['video_src']);

                    // convert arrays into json
                        foreach ($specific_vals as $k=>$v)
                        {
                            if (is_array($v))
                            {
                                $specific_vals[$k]=json_encode($v);
                            }
                        }


                    $specific_vals[$id_key]=$id;
                    $specific_vals['node_html']=$node_html;

                    $this->db->insert($type,$specific_vals);

                // update the node url if an image
                    if ('image'==$type)
                    {
                        $this->node_update($id,array('url'=>'image/'.$id));
                        $vals['url']='image/'.$id;
                    }

                // set up some data for the action record to be made
                    $time=time();
                    $image=$this->image_model->get_images($id,1);

                // the signed in user creates the node
                    $vals['id']=$id;
                    $this->stream_model->store_action(0,$this->user,$vals,null);

                // the signed in creator of the node is automatically set to follow it
                    if ($type!='image')
                    {
                        $this->load->model('connection_save_model');
                        $this->connection_save_model->update($this->user,$vals,'F',2,true,true);
                    }

                // add the video
                if (isset($old_node['video_src']) && isset($vals['video_src']))
                {
                    if (isset($vals['video_src']) &&
                        strlen($vals['video_src'])>0)
                    {
                        $this->stream_model->store_action(8,$this->user,$vals,null);
                    }
                }
            }

        // pass the id back so we know which node was just edited
            return $id;

        /* BENCHMARK */ $this->benchmark->mark('func_node_save_end');
    }

    /* *************************************************************************
         node_update() - update the node
         @param string / int $node_id - identifier for the node, could be numeric id or string url
         @return
    */
    public function node_update($node_id,$update_array)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_node_update_start');

		// can handle both numeric and string ids
			if (is_numeric($node_id) ? $column='id' : $column='url' );

		// in db
			$this->db->where($column, $node_id);
			$this->db->update('node', $update_array);

        /* BENCHMARK */ $this->benchmark->mark('func_node_update_end');
    }

    /* *************************************************************************
         inplace_save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function inplace_save($id,$type,$vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_inplace_save_start');

		// get this node
			$node=$this->node_model->get_node($id,$type);

		// only do something if the submitted form contains the same id as the url, so people can't use the url to damage other nodes
			if ($vals['node_id']==$id)
			{
				// id key (we use user_id for user table, node_id for everything else)
					if ($type=='user' ? $id_key='user_id' : $id_key='node_id' );

				// save node
					$update_data = array(
						'node_html' => $vals['node_html']
					);
					$this->db->where($id_key, $id);
					$this->db->update($type, $update_data);

				// save updated time
					$update_data = array(
						'updated' => time()
					);

					$this->db->where('id', $id);
					$this->db->update('node', $update_data);
			}

			// set up some data for the action record to be made
				$time=time();
				$user=$this->node_model->get_node($this->user['user_id']);
				$image=$this->image_model->get_images($id,1);

			// signed in user updates the current node
				$this->stream_model->store_action(1,$this->user,$node,$node['user_id']);

        /* BENCHMARK */ $this->benchmark->mark('func_inplace_save_end');

        return $node;
    }

    /* *************************************************************************
         blog_save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function blog_save($vals,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_blog_save_start');

        $this->rss_model->build_rss_file($this->user['user_id']);

        /* BENCHMARK */ $this->benchmark->mark('func_blog_save_end');
    }

    /* *************************************************************************
         calendar_save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function calendar_save($post,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_calendar_save_start');

        // only do the create stuff if it's a create !
            if (!is_numeric($post['id']))
            {
                $this->load->model('events_admin_model');

                // generate a new calendar
                    $this->load->helper('date_helper');
                    $this->load->helper('date_convert_helper');

                    // now, for the start of the calendar
                        $now=get_now();

                    // until date as a number
                        $this->load->config('admin');
                        if ($post['until_date']!='' ? $until=str_replace("-","",$post['until_date']) : $until=$now['year']+$this->config->item('years_ahead')."1231" );

                    // an array of cells
                        $cells=array();

                    // add cells
                        $cells=$this->events_admin_model->add_cells($cells,$now['year'],$until);

                // make the hash value
                    $hash=md5($id.time());

                // save that calendar as json into the calendar table
                    $update_data = array(
                        'until_date'=>$until,
                        'event_json' => json_encode($cells),
                        'validation_hash'=>$hash
                    );

                    $this->db->where('node_id', $id);
                    $this->db->update('calendar', $update_data);
            }
            else
            {
                $this->load->model('events_admin_model');

                // an update so we need to look at the until dates
                    $calendar=$this->node_model->get_node($id,'calendar');
                    $cal_until=str_replace('-','',$calendar['until_date']);
                    $until=str_replace('-','',$post['until_date']);
                    if ($until>$cal_until)
                    {
                        // make start and end
                            $start_year=substr($calendar['until_date'],0,4);

                        // add some extra cells
                            $cells=$this->events_admin_model->add_cells(json_decode($calendar['event_json'],true),$start_year,$until);

                        // update new values
                            $update_data = array(
                                'until_date' =>$until,
                                'event_json' =>json_encode($cells)
                            );

                            $this->db->where('node_id', $id);
                            $this->db->update('calendar', $update_data);
                    }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_calendar_save_end');
    }

    /* *************************************************************************
         product_save() - saves stuff for the product
         @param array $vals - the new product vals
         @param int $id - the product to update
         @param string $action - edit / create
         @return
    */
    public function product_save($vals,$id,$action)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_product_save_start');

        // add the post calc, price and pack of variations (only do this on create)
            if ('created'==$action)
            {
                $insert_data=array(
                    array('node_id'=>$id,'var_type_id'=>$this->user['price_vtype_ref']),
                    array('node_id'=>$id,'var_type_id'=>$this->user['pcalc_vtype_ref']),
                    array('node_id'=>$id,'var_type_id'=>$this->user['pquan_vtype_ref'])
                    );
                $this->db->insert_batch('nvar_type',$insert_data);
            }

        // add the admin tags string to the product table
            if (isset($vals['admin_tags']) ? $atag_string=str_replace(' ','',$vals['admin_tags']) : $atag_string='' );
            if (substr($atag_string,-1)!=';')
            {
                $atag_string.=";";
            }
            $update_data = array(
                'admin_tags' =>$atag_string
            );
            $this->db->where('node_id', $id);
            $this->db->update('product', $update_data);

        // save the admin tags
            if (isset($vals['admin_tags']))
            {
                $atag_array=explode(";",$vals['admin_tags']);
                for ($x=0;$x<count($atag_array);$x++)
                {
                    if ($atag_array[$x]!="")
                    {
                        $query=$this->db->select('*')->from('admin_tag')->where(array('name'=>trim($atag_array[$x])));
                        $res=$query->get();
                        $atag_check=$res->row_array();

                        if (0==count($atag_check))
                        {
                            $insert_data=array(
                                'name'=>trim($atag_array[$x])
                                );
                            $this->db->insert('admin_tag',$insert_data);
                        }
                    }
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_product_save_end');
    }

    /* *************************************************************************
         mass_update() - operates on a set nodes to update values
         @param string $type - the node type for the mass update
         @param array $vals - the values from the form for update
         @return
    */
    public function mass_update($type,$vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_mass_update_start');

        // the url helper is needed
            $this->load->helper('url_helper');

        // get the nodes to check for mass update
            $nodes_array=array(
                'type'=>$type,
                'protected'=>0
            );
            if ($this->user['user_type']!='super_admin')
            {
                $nodes_array['user_id']=$this->user['user_id'];
            }
            $nodes=$this->node_model->get_nodes($nodes_array);

        // various values for the update operation
            $update_nodes=array();
            $gcsv_data='';
            $message_append="";
            $nvar_count=0;
            $undo_array=array();

        // process the order on the form
            if (is_array($this->config->item('static_order')) ? $static=$this->config->item('static_order') : $static=array() );

            if (!in_array($type,$static))
            {
                $update_orders=array();
                $order=0;
                foreach ($vals as $k=>$id)
                {
                    if (strpos($k,'_set_node_order')>0)
                    {
                        $update_orders[]=array('id'=>$id,'node_order'=>$order);
                        $order+=11;
                    }
                }
                if (count($update_orders))
                {
                    $this->db->update_batch('node',$update_orders,'id');
                }
            }

        // a string to build a stock notify email
            $stock_notify='';

        $score_adjust=array();

        // iterate over all nodes
            foreach ($nodes as $node)
            {
                $undo_array[$node['id']]=array();

                // set the node visibility (and add the node to google products if it is a product)
                    if (isset($vals[$node["id"]."visnum"]))
                    {
                        $visible=$vals[$node["id"]."visnum"];

                        // check variation count for products - var count defaults to one so we don't mess with non products
                            $var_count=1;
                            if ('product'==$type)
                            {
                                $nvars=$this->variation_model->get_nvars($node['id']);
                                $var_count=count($nvars);
                            }

                        // force visible to 0 if no variations
                            if (0==$var_count)
                            {
                                if (1==$visible)
                                {
                                    $message_append.="you tried to set ".$node['name']." visible without setting any variations first<br/>";
                                }

                                $visible=0;
                            }

                        // google product list data
                            if ('product'==$type &&
                                1==$visible)
                            {
                                $gcsv_data.=$node["id"].'\t'.$node['name'].'\t'.$node['short_desc'].'\t'.$node['price'].'\tnew\t'.base_url().$node['url'].'\n';
                            }

                        // update
                            $update_nodes[]=array('id'=>$node["id"],'visible'=>$visible);

                        // update the action table for this node
                            $this->stream_model->update_visible($node,$visible);

                        // now do some score calculations to set the scores for visible or not
                        // we only do this if the visibility has changed
                            if ($node['visible']!=$visible)
                            {
                                $score_total=0;

                                // actors first, the actions of the node owning user
                                $query=$this->db->select(
                                    "sum(actor_score) as 'full_score'")->from(
                                    'action')->where(array('actor_id'=>$node['user_id'],'target_id'=>$node['id']));
                                $res=$query->get();
                                $full_score=$res->row_array();

                                $score_total+=$full_score['full_score'];

                                // then targets, action is performed on this node
                                $query=$this->db->select(
                                    "sum(target_owner_score) as 'full_score'")->from(
                                    'action')->where(array('actor_id !='=>$node['user_id'],'target_id'=>$node['id']));
                                $res=$query->get();
                                $full_score=$res->row_array();

                                $score_total+=$full_score['full_score'];

                                // make sure the array element for this user id exists
                                if(!isset($score_adjust[$node['user_id']]))
                                {
                                    $score_adjust[$node['user_id']]=0;
                                }

                                // adjust the score
                                if (0==$visible)
                                {
                                    $score_adjust[$node['user_id']]+=0-$score_total;
                                }
                                else
                                {
                                    $score_adjust[$node['user_id']]+=$score_total;
                                }

                                // now if this is a user having visibility set we need to find all their
                                // actions on other nodes and add their owners into the array too
                                if ('user'==$node['type'])
                                {
                                    $query=$this->db->select('*')->from('action')->where(array('actor_id'=>$node['id']));
                                    $res=$query->get();
                                    $actions=$res->result_array();

                                    foreach ($actions as $a)
                                    {
                                        if ($a['target_owner_score']>0)
                                        {
                                            if(!isset($score_adjust[$a['target_owner_id']]))
                                            {
                                                $score_adjust[$a['target_owner_id']]=0;
                                            }

                                            // adjust the score
                                            if (0==$visible)
                                            {
                                                $score_adjust[$a['target_owner_id']]+=0-$a['target_owner_score'];
                                            }
                                            else
                                            {
                                                $score_adjust[$a['target_owner_id']]+=$a['target_owner_score'];
                                            }
                                        }
                                    }
                                }
                            }
                    }

                // if the node is a product then use the mass adjust values to update the prices
                if ('product'==$node['type'])
                {
                    // stock flag lets notify for this product be set if needed to email
                        $stock_flag=0;

                    // only if the adjust check is set
                        if (isset($vals['adjust_check']))
                        {
                            if ('stock'==$vals['price_stock'] &&
                                'perc'==$vals['perc_pound'])
                            {
                                $message_append.="no mass adjust - percentage increase cannot be applied to stock mass adjust<br/>";
                            }
                            else
                            {
                                // only if it is onscreen
                                    if (isset($vals[$node['id'].'onscreen']) &&
                                        1==$vals[$node['id'].'onscreen'])
                                    {
                                        // get nvars
                                            $nvars=$this->variation_model->get_nvars($node['id']);

                                        // mass adjust values
                                            $mass_val=$vals['ma_value'];

                                        // set all the prices and sale prices for these nvars
                                            foreach ($nvars as $nv)
                                            {
                                                // vals for adjust
                                                    $nvp=$nv['price'];
                                                    $nvsp=$nv['sale_price'];
                                                    $nvst=$nv['stock_level'];

                                                // count actual nvars for output
                                                    $nvar_count++;

                                                // store the values in the undo array
                                                    $undo_array[$node['id']][$nv['nvar_id']]=$nv;

                                                // new prices based on value, plus or minus and precentage or pound based adjustment
                                                    if ('perc'==$vals['perc_pound'])
                                                    {
                                                        if ('price'==$vals['price_stock'])
                                                        {
                                                            $pr_perc_amount=$nvp/100*$mass_val;
                                                            $sp_perc_amount=$nvsp/100*$mass_val;
                                                            if ('plus'==$vals['plus_minus'])
                                                            {
                                                                $new_price=number_format($nvp+$pr_perc_amount,2,'.','');
                                                                $new_stock=$nvst;
                                                            }
                                                            else
                                                            {
                                                                $new_price=number_format($nvp-$pr_perc_amount,2,'.','');
                                                                $new_stock=$nvst;
                                                            }
                                                        }
                                                    }
                                                    else
                                                    {
                                                        if ('plus'==$vals['plus_minus'])
                                                        {
                                                            if ('price'==$vals['price_stock'])
                                                            {
                                                                $new_price=$nvp+$mass_val;
                                                                $new_stock=$nvst;
                                                            }
                                                            else
                                                            {
                                                                $new_price=$nvp;
                                                                $new_stock=$nvst+$mass_val;
                                                            }
                                                        }
                                                        else
                                                        {
                                                            if ('price'==$vals['price_stock'])
                                                            {
                                                                $new_price=$nvp-$mass_val;
                                                                $new_stock=$nvst;
                                                            }
                                                            else
                                                            {
                                                                $new_price=$nvp;

                                                                // no negative stock values
                                                                    if ($nvst-$mass_val>=0)
                                                                    {
                                                                        $new_stock=$nvst-$mass_val;
                                                                    }
                                                                    else
                                                                    {
                                                                        $new_stock=0;
                                                                    }

                                                                // if stock has gone down then we may need to notify if a threshold is breached
                                                                    if ($new_stock<=$nv['stock_threshold'])
                                                                    {
                                                                        $stock_flag=1;
                                                                    }
                                                            }
                                                        }
                                                    }

                                                // new sale price, operation performed on new_price using product values
                                                    $query=$this->db->select('*')->from('product')->where(array('node_id'=>$node['id']));
                                                    $res=$query->get();
                                                    $product=$res->row_array();

                                                    if ('pound'==$product['sale_type'])
                                                    {
                                                        $new_sale_price=$new_price-$product['sale_amount'];
                                                    }
                                                    else
                                                    {
                                                        $new_sale_price=$new_price-($new_price/100*$product['sale_amount']);
                                                    }

                                                // if this is main new prices for node table
                                                    if (1==$nv['main'])
                                                    {
                                                        // undo array old values
                                                            $undo_array[$node['id']]['main_price']=$nvp;
                                                            $undo_array[$node['id']]['main_sale_price']=$nvsp;

                                                        // new values
                                                            $main_price=$new_price;
                                                            $main_sale_price=$new_sale_price;
                                                    }

                                                // save the new nvar prices
                                                    $update_data = array(
                                                        'price' =>$new_price,
                                                        'sale_price' =>$new_sale_price,
                                                        'stock_level'=>$new_stock
                                                    );

                                                    $this->db->where('nvar_id', $nv['nvar_id']);
                                                    $this->db->update('nvar', $update_data);
                                            }

                                        // update this node with the price and sale price we just saved
                                            if (isset($main_price) &&
                                                isset($main_sale_price))
                                            {
                                                $update_data = array(
                                                    'price' =>$main_price,
                                                    'sale_price' =>$main_sale_price
                                                );

                                                $this->db->where('id', $node['id']);
                                                $this->db->update('node', $update_data);
                                            }

                                        // set the product variations json
                                            $this->variation_model->set_vjson($node['id']);
                                    }
                            }
                        }

                    if (1==$stock_flag)
                    {
                        $stock_notify.=$node['name'].": <a href='".$this->config->item('full_domain')."/product/".$node['id']."/variations'>stock list</a><br/>";
                    }
                }
            }

        // stock notification email
            if (strlen($stock_notify)>0)
            {
                $embod="Your recent mass update operation has generated the following stock threshold warnings:<br/><br/>".$stock_notify;
                $this->send_email($this->config->item('site_email'),"system","Stock Level email from <".$this->config->item('from_email').">",$embod);
            }

		// save the undo array - only if a value different from 0 was entered, i.e. something was mass adjusted
            if (isset($vals['ma_value']) &&
                $vals['ma_value']>0)
            {
                $update_data = array(
                    'undo_array' =>json_encode($undo_array)
                );
                $this->db->where('user_id', $this->user['user_id']);
                $this->db->update('mass_undo', $update_data);
            }

		// run the updates
			if (count($update_nodes)>0)
			{
				$this->db->update_batch('node',$update_nodes,'id');
			}

		// blogs, so update rss feed
			if ('blog'==$type)
			{
				$this->rss_model->build_rss_file($this->user['user_id']);
			}

        // update sitemap.xml
            $this->sitemap_model->generate_sitemap();

		// build the google product file if this is products
			if (strlen($gcsv_data)>0)
			{
				$gcsv_data='id\ttitle\tdescription\tprice\tcondition\tlink\n'.$gcsv_data;
				$this->load->helper('file_helper');
				write_file("google/".$this->user['user_id']."product_csv.txt",$gcsv_data,'w');
			}

        // set the link list
			$this->update_linklist();

        // update the users score
            $this->load->model('score_model');
            foreach ($score_adjust as $user_id=>$score)
            {
                $user=$this->node_model->get_node($user_id);
                $this->score_model->update_score($user,$score);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_mass_update_end');

        return $message_append;
    }

    /* *************************************************************************
         undo_mass() - gets the undo array and replaces the mass set values from the last mass set
    */
    public function undo_mass()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_undo_mass_start');

        // retrieve the undo json
            $query=$this->db->select('*')->from('mass_undo');
            $res=$query->get();
            $mass=$res->row_array();

        // get the array
            $mass_array=json_decode($mass['undo_array'],true);

        // count
            $nvar_count=0;

        // iterate replacing all the values and saving
            if (is_array($mass_array))
            {
                foreach ($mass_array as $k=>$v)
                {
                    foreach ($v as $vk=>$vv)
                    {
                        if (is_array($vv))
                        {
                            $nvar_count++;

                            $update_data = array(
                                'price' =>$vv['price'],
                                'sale_price' =>$vv['sale_price'],
                                'stock_level' =>$vv['stock_level']
                            );

                            $this->db->where('nvar_id', $vk);
                        $this->db->update('nvar', $update_data);
                        }
                    }

                    if (isset($v['main_price']) &&
                        isset($v['main_sale_price']))
                    {
                        $update_data = array(
                            'price' =>$v['main_price'],
                            'sale_price' =>$v['main_sale_price']
                        );

                        $this->db->where('id', $k);
                        $this->db->update('node', $update_data);
                    }

                    // restore json
                        $this->variation_model->set_vjson($k);
                }
            }

        return $nvar_count;

        /* BENCHMARK */ $this->benchmark->mark('func_undo_mass_end');
    }

     /* *************************************************************************
          page_save() - saves the details for a page including setting up the nav page entries
          @param array $vals - the form values
          @param numeric $id - the id of the page node
     */
     public function category_save($vals,$id)
     {
         /* BENCHMARK */ $this->benchmark->mark('func_category_save_start');

         /* BENCHMARK */ $this->benchmark->mark('func_category_save_end');
     }

     /* *************************************************************************
          page_save() - saves the details for a page including setting up the nav page entries
          @param array $vals - the form values
          @param numeric $id - the id of the page node
     */
     public function page_save($vals,$id)
     {
         /* BENCHMARK */ $this->benchmark->mark('func_page_save_start');

         /* BENCHMARK */ $this->benchmark->mark('func_page_save_end');
     }

     /* *************************************************************************
          user_save() -
          @param string
          @param numeric
          @param array
          @return
     */
     public function user_save($vals,$id)
     {
         /* BENCHMARK */ $this->benchmark->mark('func_user_save_start');

         /* BENCHMARK */ $this->benchmark->mark('func_user_savee_end');
     }

    /* *************************************************************************
         group_save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function group_save($vals,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_group_save_start');

        /* BENCHMARK */ $this->benchmark->mark('func_group_save_end');
    }

    /* *************************************************************************
         mediaset_save() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function mediaset_save($vals,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_mediaset_save_start');

        /* BENCHMARK */ $this->benchmark->mark('func_mediaset_save_end');
    }

    /* *************************************************************************
         delete_node() - removes all evidence of a node from the system,
            along with anything that is solely associated with it
         @param string $type - the type of node
         @param numeric $is - the id of the node
    */
    public function delete_node($type,$id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_node_start');

        // get the node, check the right user is signed in to delete it
            $node=$this->node_model->get_node($id);

        // delete if correct user
            if ($node['user_id']==$this->user['user_id'] or
                'super_admin'==$this->user['user_type'])
            {

                if ($id!=1)
                {

                    // remove actions
                        $this->db->delete('action',array('actor_id'=>$id));
                        $this->db->delete('action',array('target_id'=>$id));
                        $this->db->delete('action',array('target_owner_id'=>$id));

                    // event delete removes sequence from calendar
                        if ('event'==$type)
                        {
                            $this->load->model('events_admin_model');
                            $event=$this->node_model->get_node($id,'event');
                            $this->events_admin_model->delete_sequence($event);
                        }

                    // calendar delete removes events
                        $this->db->delete('event',array('calendar_id'=>$id));

                    // remove details
                        $key_id=('user'==$type) ? 'user_id' : 'node_id';
                        $this->db->delete($type,array($key_id=>$id));

                    // remove node and images
                        $this->db->delete('node',array('id'=>$id));
                        $this->db->delete('image',array('node_id'=>$id));

                    // remove variations
                        $query=$this->db->select('nvar_id')->from('nvar')->where(array('node_id'=>$id));
                        $res=$query->get();
                        $nvars=$res->result_array();

                        foreach ($nvars as $n)
                        {
                            $this->db->delete('nvar',array('nvar_id'=>$n['nvar_id']));
                            $this->db->delete('nvar_value',array('nvar_id'=>$n['nvar_id']));
                        }

                        $this->db->delete('nvar_type',array('node_id'=>$id));

                }
            }


        /* BENCHMARK */ $this->benchmark->mark('func_delete_node_end');
    }

    /* *************************************************************************
         update_linklist() - updates the tinymce link list file to include all the nodes created by that user
    */
    public function update_linklist()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_update_linklist_start');
        $this->load->helper('url');

		// get nodes
			$nodes=$this->node_model->get_nodes(array('user_id'=>$this->user['user_id'],'removed'=>0,'protected'=>0),null,'type,name');

        // build the link list
			$js_array='var tinyMCELinkList = new Array(';
			foreach ($nodes as $node)
			{
                if (is_array($this->config->item('linklist_nodes')) ? $lltypes=$this->config->item('linklist_nodes') : $lltypes=array() );
                if (in_array($node['type'],$lltypes))
                {
                    if (1==$node['visible'] ? $hmess="" : $hmess=' [HIDDEN]' );
                    //$js_array.="['".$node['type']." - ".str_replace("'","",$node['name'])." ".$hmess."', '".base_url()."".$node['url']."'],\n";
                    $js_array.="['".$node['human_type']." - ".str_replace("'","",$node['name']).$hmess."', '/".$node['url']."'],\n";
                }
			}
			$js_array=substr($js_array,0,-2);
			$js_array.=');';

		// output file
			$js_filepath='user_files/'.$this->user['user_id'].'_link_list.js';
			$this->load->helper('file');
			delete_files($js_filepath);
			write_file($js_filepath,$js_array,'w');

        /* BENCHMARK */ $this->benchmark->mark('func_update_linklist_end');
    }

    /* *************************************************************************
         get_admin_tags() - gets the admin tags into an array keyed by admin tag name
         @return $admin_tags
    */
    public function get_admin_tags()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_admin_tags_start');

        // from the db all admin tags
			$query=$this->db->select('name')->from('admin_tag')->order_by('name');
			$res=$query->get();
			$atags=$res->result_array();

        // array with keys set to admin tags
			$admin_tags=array();
			foreach($atags as $a)
			{
				$admin_tags[]=$a['name'];
			}

        return $admin_tags;

        /* BENCHMARK */ $this->benchmark->mark('func_get_admin_tags_end');
    }
}
