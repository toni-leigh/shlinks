<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Postage

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Postage extends Node {

    public function __construct()
    {
        parent::__construct();

        // models
            $this->load->model('postage_model');

        // libraries
            $this->load->library('input');
    }

    /* *************************************************************************
     show_postages() - shows all the postage calculation values for this user
     loads a view instead of returning
    */
    public function show_postages()
    {
        // loads a form
        $this->load->helper('form');

        $this->data['postages']=$this->postage_model->get_postages($this->user['user_id']);
        $this->data['postage_threshold']=$this->user['postage_threshold'];
        $this->data['bracket_updater']=$this->postage_model->bracket_updater($this->user['postage_calc_type'],$this->data['postages']);
        $this->data['calc_type']=$this->user['postage_calc_type'];
        $this->data['classes']=$this->postage_model->get_postage_classes($this->user['user_id']);

        $this->display_node('postage-calculation-definition');
    }
    /* *************************************************************************
         save_calc_vals() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save_calc_vals()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_calc_vals_start');

        // get post
            $post=$this->get_input_vals();

					$user_id=1;

        // save postage threshold
			if (isset($post['p_thresh']))
			{
				$this->postage_model->save_threshold($post['p_thresh'],$user_id);
			}
			else
			{
				// first remove all the postages - default super admin id
					$this->postage_model->delete_postages($user_id);

				// save the actual postages
					$this->postage_model->save_postages($post,$user_id);
			}

        // log, reload
            $this->_log_action("postages updated","postages updated for ".$this->user['user_id'],"postages updated for ".$this->user['user_id']);
            $this->_reload('postage-calculation-definition',"the postage values were saved",'success');

        /* BENCHMARK */ $this->benchmark->mark('func_save_calc_vals_end');
    }

    /* *************************************************************************
         ajax_postage_classes() - gets a new bracket row as response to an ajax request
         @return $html the new bracket row
    */
    public function ajax_postage_classes()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_ajax_postage_classes_start');

		// get array
			$get=$this->get_input_vals();

        // get the postage classes
            $classes=$this->postage_model->get_postage_classes($this->user['user_id']);

        // open html
            $html='';

        // get the values of the bracket to append
            $new_bracket_id=$get['new_bracket_id'];
            $new_min_value=$get['new_min_value'];
            $max_val=$new_min_value-1;

        // form fields for the max and minimum pcalc value
            $html="<div id='".$new_bracket_id."bracket_row' class='bracket_row left'>";
            $html.="<input id='".$new_bracket_id."min_value' class='".$new_bracket_id." min_value form_field left' type='text' name='".$new_bracket_id."min_value' value='".$new_min_value."' onkeyup='pcheck_numeric(\"".$new_bracket_id."min_value\",\"#e77d0a\")' onchange='set_changes()'/>";
            $html.="<span class='".$new_bracket_id." bracket_dash'>&nbsp;&mdash;&nbsp;</span>";
            $html.="<input id='".$new_bracket_id."max_value' class='".$new_bracket_id." max_value form_field left' type='text' name='".$new_bracket_id."max_value' value='MAX' onkeyup='pcheck_numeric(\"".$new_bracket_id."max_value\",\"#e77d0a\")' onblur='add_postage(".$new_bracket_id.")' onchange='set_changes()'/>";

        // fields for the actual costs for each class
            foreach ($classes as $class)
            {
                $html.="<span class='".$new_bracket_id." pound_label'>&pound;</span>";
                $html.="<input id='".$new_bracket_id.$class['pclass_name']."' class='".$new_bracket_id." class_cost form_field left' type='text' name='".$new_bracket_id.$class['pclass_name']."' value='0' onkeyup='pcheck_numeric(\"".$new_bracket_id.$class['pclass_name']."\",\"#9948f2\")' onchange='set_changes()'/>";
            }

        // close html
            $html.="</div>";

        $return_html[0]=$html;
        $return_html[1]=$max_val;

        exit(json_encode($return_html));

        /* BENCHMARK */ $this->benchmark->mark('func_ajax_postage_classes_end');
    }
}
