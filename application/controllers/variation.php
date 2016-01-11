<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Ajax_variations

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
*/
    class Variation extends Node {

    public function __construct()
    {
        parent::__construct();

		$this->load->database();

        $this->load->library('input');
        $this->load->model('basket_model');
        $this->load->model('variation_model');
    }

    /* *************************************************************************
         add_vtype() - add a vtype to the node - the $get variables are a new
            composite key for this table
         $get['node_id'] - the id of the node to add a vtype to
         $get['vtype_id'] - the id of the vtype to add to the node
    */
    public function add_vtype()
    {
		// get array
			$get=$this->get_input_vals();

		// add
			$this->variation_model->add_nvar_type($get);

        exit();
    }

    /* *************************************************************************
         remove_vtype() - remove vtype from the node, the $get variables form the
            composite key for the vtype record
         $get['node_id'] - the id of the node to remove a vtype from
         $get['vtype_id'] - the id of the vtype to remove
    */
    public function remove_vtype()
    {
		// get array
			$get=$this->get_input_vals();

		// remove
			$this->variation_model->remove_nvar_type($get);
    }

    /* *************************************************************************
         adder() - update the adder row and preview called when a variation type is
            set by clicking the buttons at the top of the variation page - this is only
            accessible when no variations are defined - this is the adder default form
         $get['node_id'] - the id of the node to retrieve the vtypes of
         @exit [0] - the adder row html
         @exit [1] - the preview panel html
    */
    public function adder()
    {
		// get array
			$get=$this->get_input_vals();

        // get this nodes variation types
            $nvar_types=$this->variation_model->get_nvar_types($get['node_id']);

        // build an adder form
            $adder=$this->variation_model->nvar_adder($nvar_types);

        // strips the pack quantites apart from 1 from the default
            $nvar_types=$this->variation_model->strip_pack_quantities($nvar_types);

        // builds the initial preview row for these selections
            $preview=$this->variation_model->nvar_add_preview($nvar_types,array());

        $html[]=$adder;
        $html[]=$preview;

        exit(json_encode($html));
    }

    /* *************************************************************************
        preview() - initialises an array of variation type values for the preview
            output
        $get['inputs'] - the current state of selections on the adder
        @exit - the preview row html
    */
    public function preview()
    {
		// get array
			$get=$this->get_input_vals();

        // this is the current set of inputs from js serialise, we explode them into
        // an array of pairs of values vtype_id => vvalue_id
            $sel_array=explode("&",$get['inputs']);

        // get all the vtype_ids - iterating over this will generate our columns
            $where_in=array();
            for ($x=0;$x<count($sel_array);$x++)
            {
                $pair=explode("=",$sel_array[$x]);
                if (!in_array($pair[0],$where_in))
                {
                    $where_in[]=$pair[0];
                }
            }

        // get all the var type details using these ids
            $query=$this->db->select('*')->from('var_type')->where_in('var_type_id',$where_in);
            $res=$query->get();
            $vtypes=$res->result_array();

        // sort the var types on var type id, this will order them correctly so the columns
        // match right down the screen
            usort($vtypes, array($this,'var_sort'));

        // loop over this query to get the correct values
            for ($x=0;$x<count($vtypes);$x++)
            {
                // go through the selection array retrieving all the selected values for the
                // current vtype column - on default this will be all (apart from pack quantity)
                // otherwise it will be a reflection of the contents of the adder, meaning
                // preview will only output the selected values
                    $where_in=array();
                    for ($y=0;$y<count($sel_array);$y++)
                    {
                        // get the values for each variation type
                            $pair=explode("=",$sel_array[$y]);
                            if ($pair[0]==$vtypes[$x]['var_type_id'])
                            {
                                $where_in[]=str_replace("+"," ",$pair[1]);
                            }

                        // we also get the stock related values now too, for use on those columns which are appended at the end
                            if ('thresh_add'==$pair[0])
                            {
                                $thresh_val=$pair[1];
                            }
                            if ('stock_add'==$pair[0])
                            {
                                $stock_val=$pair[1];
                            }
                    }

                // get all the values for this vtype column based on selections $where_in defined immediately above
                    $query=$this->db->select('*')->from('var_value')->where('var_type_id',$vtypes[$x]['var_type_id'])->where_in('var_value',$where_in);
                    $res=$query->get();
                    $values=$res->result_array();

                // if there are values in this array then we need to save them as part of
                // a vaue array in the vtype array
                // else the val is a single text value rather than an array of selections
                    if (count($values)>0)
                    {
                        for ($y=0;$y<count($values);$y++)
                        {
                            $vtypes[$x]['vals'][$y]['id']=$values[$y]['var_value_id'];
                            $vtypes[$x]['vals'][$y]['name']=$values[$y]['var_value'];
                        }
                    }
                    else
                    {
                        for ($y=0;$y<count($sel_array);$y++)
                        {
                            $pair=explode("=",$sel_array[$y]);
                            if ($pair[0]==$vtypes[$x]['var_type_id'])
                            {
                               $vtypes[$x]['val']=$pair[1];
                            }
                        }
                    }
            }

        // finish with the thresh and stock values in the same format
            $stock=array(
                'var_type_name'=>'stock',
                'var_type_id'=>'stock',
                'val'=>$stock_val
            );
            $thresh=array(
                'var_type_name'=>'thresh',
                'var_type_id'=>'thresh',
                'val'=>$thresh_val
            );

            $vtypes=array_reverse($vtypes);

            $vtypes[]=$stock;
            $vtypes[]=$thresh;

            $vtypes=array_reverse($vtypes);

        // now create the preview using these values
            $preview=$this->variation_model->preview_rows($vtypes);

        exit(json_encode($preview));
    }

    // a sort function called by $this->preview()
        function var_sort($a, $b)
        {
            return $a['var_type_id'] - $b['var_type_id'];
        }

    /*
        THE FOLLOWING FUNCTIONS ARE USED JUST BY THE VARIATION MANAGEMENT PAGE
    */

    /* *************************************************************************
        variation_types() - gets the vtypes for this user and saves them in data
            for the view -
        ** this function is used by the variation type management page, not the
            node specific variations page **
        loads a view
    */
    public function variation_types()
    {
        $this->data['var_types']=$this->variation_model->get_var_types();

        for ($x=0;$x<count($this->data['var_types']);$x++)
        {
            $this->data['var_types'][$x]['html']=$this->variation_model->get_var_values_html($this->data['var_types'][$x]);
        }

        $this->display_node('variation-types-definition');
    }

    /* *************************************************************************
        add_type() - adds a new variation type
        post('variation_type') - the name of the new variation
        ** this function is used by the variation type management page, not the
            node specific variations page **
        reloads with a success or fail message
    */
    public function add_type()
    {
		// get the post
			$post=$this->get_input_vals();

		// save
			$message=$this->variation_model->save_var_type($post);

		// log, reload
			$this->_log_action($message['text'],$message['text'],$message['pass']);
			$this->_reload('variation-types-definition',$message['text'],$message['pass']);
    }

    /* *************************************************************************
        save_var_value() - adds a new vvalue to vtype
        $get['var_value'] - the new vvalue id from the form input
        $get['var_type_id'] - the id for this vtype so that the values can be
            retrieved for reload
        ** this function is used by the variation type management page, not the
            node specific variations page **
        @exit - the html for the variation type admin panel with the new vvalue
            added - just the html for the individual vtype panel
    */
    public function save_var_value()
    {
		// get array
			$get=$this->get_input_vals();

		// save the var value
			$this->variation_model->save_var_value($get);

		// get the full var type with values
			$vtype=$this->variation_model->get_var_type($get['var_type_id']);

        exit(json_encode($this->variation_model->get_var_values_html($vtype)));
    }

    /* *************************************************************************
        remove_var_value() - removes a vvalue from the variation definition
        $get['var_value_id'] - the vvalue id to remove, no need for the vtype id
        ** this function is used by the variation type management page, not the
            node specific variations page **
        @exit void
    */
    public function remove_var_value()
    {
		// get array
			$get=$this->get_input_vals();

		// remove var value
			$this->variation_model->remove_var_value($get['var_value_id']);

        exit();
    }

    /*
     THE FOLLOWING ARE USED BY THE FRONT END PRODUCT DISPLAY
    */

    /* *************************************************************************
        ajax_get_adder() -
        $get['nvar_id']
        @exit $html
    */
    public function ajax_get_panel_text()
    {
		// get array
			$get=$this->get_input_vals();

        $nvar=$this->variation_model->get_nvar($get['nvar_id']);
        exit(json_encode($this->variation_model->format_add_panel($nvar)));
    }

    /* *************************************************************************
         ajax_check_stock() -
         @return
    */
    public function ajax_check_stock()
    {
		// get array
			$get=$this->get_input_vals();

        $nvar=$this->variation_model->get_nvar($get['nvar_id']);
        $update_row=$this->basket_model->check_for_row($nvar);
        $html='';

        // if there are none in the basket our calculation and output is different
            if (null==$update_row)
            {
                if ($nvar['stock_level']<$get['add_quantity'])
                {
                    $stock_val=$nvar['stock_level'];
                    $basket_append="";
                }
            }
            else
            {
                $total_qty=$update_row['qty']+$get['add_quantity'];
                if ($nvar['stock_level']<$total_qty)
                {
                    $stock_val=$nvar['stock_level']-$update_row['qty'];
                    $basket_append=" [you have some in your basket]";
                }
            }

        // if a stock val is set then we need to output
            if (isset($stock_val))
            {
                if ($stock_val>0)
                {
                    $html="not enough stock - clicking 'add to basket' will only add ".$stock_val." items".$basket_append;
                }
                else
                {
                    $html="not enough stock - clicking 'add to basket' not add any items".$basket_append;
                }
            }

        exit(json_encode($html));
    }
}
