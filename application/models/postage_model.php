<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Postage_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Postage_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         delete_postages() - remove all the postages ready for new ones to be saved
            the postage function just wholesale deletes the replaces all from the table
         @return
    */
    public function delete_postages($user_id=1)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_postages_start');

        $this->db->delete('postage_charges',array('user_id'=>$user_id));

        /* BENCHMARK */ $this->benchmark->mark('func_delete_postages_end');
    }

    /* *************************************************************************
         save_threshold() - saves the threshold value over which free postage is offered
         @param int $thresh - the new threshold
         @param int $user_id - the user to update
    */
    public function save_threshold($thresh,$user_id=1)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_threshold_start');

        // check for numericness
            if (is_numeric($thresh))
            {
                // and update is numeric
                    $update_data = array(
                        'postage_threshold' =>$thresh
                    );

                    $this->db->where('user_id', $user_id);
                    $this->db->update('user', $update_data);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_threshold_end');
    }

    /* *************************************************************************
         save_postages() - save the postages from the form
         @param array $post - the form post values
         @param int $user_id - the user id for the postages to apply to
    */
    public function save_postages($post,$user_id=1)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_postages_start');

        // count
            $c=1;

        // get postage classes for this user
            $pclasses=$this->postage_model->get_postage_classes($user_id);


        // go through all the postage classes updating
            while (isset($post[$c.'min_value']))
            {
                // set the max weight / itemcount value
                    if ('MAX'==$post[$c.'max_value'] ? $max=-1 : $max=$post[$c.'max_value'] );

                // build the common insert data (bracket range, user id)
                    $insert_data=array(
                        'min_value'=>$post[$c.'min_value'],
                        'max_value'=>$max,
                        'user_id'=>$user_id
                        );

                // keyed by postage class name, value is from the form, numeric
                    foreach ($pclasses as $pclass)
                    {
                        $insert_data[$pclass['pclass_name']]=$post[$c.$pclass['pclass_name']];
                    }

                $this->db->insert('postage_charges',$insert_data);
                $c++;
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_postages_end');
    }


    /* *************************************************************************
     get_postages () - retrieves all a users postage calculation values
     @param int $user_id - the user id to get the postages for
     @return query array the postage calculation values
    */
    public function get_postages($user_id)
    {
        return $this->db->select('*')->from('postage_charges')->where(array('user_id'=>$user_id))->order_by('postage_charge_id')->get()->result_array();
    }

    /* *************************************************************************
         pcharge_table() - builds an html table of pcharges for output, not edit, useful on a postage charges page
         @param int $user_id - retrieve postage charges per user, default 10 super admin for single merchat site
         @return $html the table as html
    */
    public function pcharge_table($user_id=1)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_pcharge_table_start');

        // gets the postage costs
            $charges=$this->postage_model->get_postages($user_id);

        // gets the postage classes
            $classes=$this->postage_model->get_postage_classes($user_id);

        // open html
            $html="<div id='pcharge_table'>";

        // heading row
            $html.="<div id='pcharge_heading_row' class='pcharge_row'>";
            $html.="<div class='pcharge_cell value_cell'>&nbsp;";
            $html.="</div>";

        // the different classes
            foreach ($classes as $cl)
            {
                $html.="<div class='pcharge_cell'>";
                $html.=$cl['pclass_heading'];
                $html.="</div>";
            }
            $html.="</div>";

        // now each bracket in a row with each class output
            foreach ($charges as $c)
            {
                $html.="<div class='pcharge_row'>";
                $html.="<div class='pcharge_cell value_cell'>";
                if (-1==$c['max_value'] ? $max='MAX' : $max=$c['max_value'].'gr' );
                $html.=$c['min_value'].'gr - '.$max;
                $html.="</div>";
                foreach ($classes as $cl)
                {
                    $html.="<div class='pcharge_cell'>";
                    $html.=format_price($c[$cl['pclass_name']]);
                    $html.="</div>";
                }
                $html.="</div>";
            }

        // close
            $html.="</div>";

        /* BENCHMARK */ $this->benchmark->mark('func_pcharge_table_end');

        return $html;
    }


    /* *************************************************************************
     bracket_updater () - outputs a form for updating the postage brackets
     @param string $calc_type - wieght or itemcount
     @param array $brackets - the current brackets
     @return
    */
    public function bracket_updater($calc_type,$brackets)
    {
        // get the classes
            $postage_classes=$this->get_postage_classes($this->user['user_id']);

        // count brackets
            $bracket_count=count($brackets);

        // open
            $bu='';

        if ($bracket_count==0)
        {
            // no brackets set yet
                $bu.="<span class='full_screen_width bracket_message'>[ you have no postage charges set for this type, it will not be available when creating products ]</span>";
        }
        else
        {
            // count each bracket
                $counter=1;

            // output each bracket
                foreach ($brackets as $bracket)
                {
                    // some extra vracket values
                        $bracket["postage_calc_type"]=$calc_type;
                        $bracket['counter']=$counter;

                    // mark the last bracket
                        if ($counter==$bracket_count)
                        {
                            $bracket["last_bracket"]=1;
                        }

                    // the actual row
                        $bu.="<div id='".$counter."bracket_row' class='bracket_row left'>";
                        $bu.=$this->bracket_row($bracket,$postage_classes);
                        $bu.="</div>";

                    // last max value for working out the new bracket row
                        $last_max_value=$bracket["max_value"];

                    $counter++;
                }
        }

        // row for new bracket if needed
            $bu.="<div id='new_".$calc_type."' class='bracket_row left'>";

        //
        if ($last_max_value==-1)
        {
            $next_min_value="";
            $next_max_value="";
        }
        else
        {
            $next_min_value=$last_max_value+1;
            $next_max_value="MAX";
        }
        //$bu.=$this->bracket_row(array("postage_charge_id"=>"new_".$calc_type."_","min_value"=>$next_min_value,"max_value"=>$next_max_value),$postage_classes,$last_bracket_id);
        $bu.="<span id='new_brackets'>&nbsp;</span>";
        $bu.="</div>";
        $bu.="<input id='postage_bracket_submit' class='submit' type='submit' name='submit' value='save postages' onclick='unset_changes()'/>";
        return $bu;
    }

    /* *************************************************************************
     bracket_row () - output a single form bracket row
     @param int $bracket - the bracket details
     @param array $postage_classes - the classes to iterate over a ref the bracket
     @return
    */
    public function bracket_row($bracket,$postage_classes)
    {
        // set max value
            if ($bracket["max_value"]==-1 ? $max_value="MAX" : $max_value=$bracket["max_value"] );

        // min and max value fields for this bracket row
            $br="";
            $br.="<input id='".$bracket['counter']."min_value' class='".$bracket['counter']." min_value form_field left' type='text' name='".$bracket['counter']."min_value' value='".$bracket["min_value"]."' onkeyup='pcheck_numeric(\"".$bracket['counter']."min_value\",\"#e77d0a\")' onchange='set_changes()'/>";
            $br.="<span class='".$bracket['counter']." bracket_dash'>&nbsp;&mdash;&nbsp;</span>";
            $br.="<input id='".$bracket['counter']."max_value' class='".$bracket['counter']." max_value form_field left' type='text' name='".$bracket['counter']."max_value' value='".$max_value."' onkeyup='pcheck_numeric(\"".$bracket['counter']."max_value\",\"#e77d0a\")' onblur='add_postage(".$bracket['counter'].")' onchange='set_changes()'/>";

        // postage classes for this bracket row
            foreach ($postage_classes as $pclass)
            {
                if (isset($bracket[$pclass['pclass_name']]) ? $val=$bracket[$pclass['pclass_name']] : $val=0 );
                $br.="<span class='".$bracket['counter']." pound_label'>&pound;</span>";
                $br.="<input id='".$bracket['counter'].$pclass['pclass_name']."' class='".$bracket['counter']." class_cost form_field left' type='text' name='".$bracket['counter'].$pclass['pclass_name']."' value='".number_format($val,2)."' onkeyup='pcheck_numeric(\"".$bracket['counter'].$pclass['pclass_name']."\",\"#9948f2\")' onchange='set_changes()'/>";
            }

        return $br;
    }

    /* *************************************************************************
        get_postage_classes() - retrieves all a users postage classes - ie UK 1st, or
            international 2nd etc.
        @param int $user_id - the user id to retrieve about
        @return query array the postage classes
    */
    public function get_postage_classes($user_id)
    {
        $query=$this->db->select('*')->from('postage_class')->where(array('user_id'=>$user_id))->order_by('display_order');
        $res=$query->get();
        return $res->result_array();
    }
}
