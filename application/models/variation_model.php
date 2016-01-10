<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Variation_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
    class Variation_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         count_used() - counts the number of times a variation value is used
         @param int $id - the var value id
         @return $count
    */
    public function count_used($var_value_id)
    {
        $query=$this->db->select('*')->from('nvar_value')->where(array('var_value_id'=>$var_value_id));
        $res=$query->get();
        $result=$res->result_array();

        return count($result);
    }

    /* *************************************************************************
         get_var_types() - gets all a users vtypes including any vvalues that they
            contain
         @return array $types - this users vtypes
    */
    public function get_var_types()
    {
        // get all the types
            $query=$this->db->select('*')->from('var_type')->where(array('user_id'=>$this->user['user_id']))->order_by('var_type.var_type_id');
            $res=$query->get();
            $types=$res->result_array();

        // get the vvalues for each vtype
            for ($x=0;$x<count($types);$x++)
            {
                // get the vvalues
                    $values=$this->get_var_values($types[$x]['var_type_id']);

                // add to the $types array
                    for ($y=0;$y<count($values);$y++)
                    {
                        $types[$x]['vals']=$values;
                    }
            }

        return $types;
    }

    /* *************************************************************************
         get_var_type() - gets an inidividual vtype
         @param int $vtype_id - the id of the vtype to retrieve
         @return $type - the vtype
    */
    public function get_var_type($vtype_id)
    {
        // get the vtype
            $query=$this->db->select('*')->from('var_type')->where(array('var_type_id'=>$vtype_id));
            $res=$query->get();
            $type=$res->row_array();

        // add to the $type array
            $type['vals']=$this->get_var_values($vtype_id);

        return $type;
    }

    /* *************************************************************************
         get_var_values() - get the vvalues for a vtype
         @param int $vtype_id - the id of the vtype whose vvalues are to be retrieved
         @return query array the vvalues
    */
    public function get_var_values($vtype_id)
    {
        $query=$this->db->select('*')->from('var_value')->where(array('var_type_id'=>$vtype_id))->order_by('var_value');
        $res=$query->get();
        return $res->result_array();
    }

    /* *************************************************************************
         get_nvar_types() - gets the types of variation for this node, defines how this node varies,
            includes the vvalues for each vtype
         @param int $id - the node id
         @return array $types - the var types for this node
    */
    public function get_nvar_types($id)
    {
        // first get the vtypes
            $query=$this->db->select('*')->from('nvar_type')->where(array('node_id'=>$id))->join('var_type','nvar_type.var_type_id=var_type.var_type_id')->order_by('nvar_type.var_type_id');
            $res=$query->get();
            $types=$res->result_array();

        // iterate and get the vvalues, NB the vals are stored slightly differently as this is for a specific
        // output on the variation definiton page
            for ($x=0;$x<count($types);$x++)
            {
                // get the variation details
                    $values=$this->get_var_values($types[$x]['var_type_id']);

                // add to the nvar array
                    for ($y=0;$y<count($values);$y++)
                    {
                        $types[$x]['vals'][$y]['id']=$values[$y]['var_value_id'];
                        $types[$x]['vals'][$y]['name']=$values[$y]['var_value'];
                    }
            }

        return $types;
    }

    /* *************************************************************************
         add_nvar_type() - adds a var type to a node
         @param array $vals - the identification of the node and var type
    */
    public function add_nvar_type($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_nvar_type_start');

        // check first, avoid double submit
            $query=$this->db->select('*')->from('nvar_type')->where(array('node_id'=>$vals['node_id'],'var_type_id'=>$vals['vtype_id']));
            $res=$query->get();
            $result=$res->row_array();

        // add if not found
            if (0==count($result))
            {
                $insert_data=array(
                    'node_id'=>$vals['node_id'],
                    'var_type_id'=>$vals['vtype_id']
                    );
                $this->db->insert('nvar_type',$insert_data);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_add_nvar_type_end');
    }

    /* *************************************************************************
         remove_nvar_type() - removes a var type from a node
         @param array $vals - the identification of the node and var type
    */
    public function remove_nvar_type($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_remove_nvar_type_start');

        $this->db->delete('nvar_type', array('node_id'=>$vals['node_id'],'var_type_id'=>$vals['vtype_id']));

        /* BENCHMARK */ $this->benchmark->mark('func_remove_nvar_type_end');
    }

    /* *************************************************************************
        get_nvars() - gets the variations for a particular node - these are the individual
            variations for a product, each of which is a unique combination of vvalues for
            the vtype set that defines how the node varies
        @param int $id - the node_id whose variations must be retrieved
        @param int $all - whether or not to get all, whether in stock or not
        @return array $nvars - the nodes variations, each one defined by a unique combination
            of node vtype vvalues
    */
    public function get_nvars($id,$all=1)
    {
        // do we restrict to just in stock (stock_level>0) or all variations
            if (1==$all)
            {
                $where=array('node_id'=>$id);
            }
            else
            {
                $where=array('node_id'=>$id,'stock_level>'=>0);
            }

        // get the variations
            $query=$this->db->select('*')->from('nvar')->where($where);
            $res=$query->get();
            $nvars=$res->result_array();

        // get the details of each variation
            for($x=0;$x<count($nvars);$x++)
            {
                // get the vvalues, this time we need to get a specific vvalue set based on the vvalues for this nvar
                // so we look for each node vtype vvalue associated with this nvar
                    $query=$this->db->select('*')->from('nvar_value')->where(array('nvar_id'=>$nvars[$x]['nvar_id']))->join('var_type','nvar_value.var_type_id=var_type.var_type_id')->join('var_value','nvar_value.var_value_id=var_value.var_value_id','left outer')->order_by('var_type.var_type_id');
                    $res=$query->get();
                    $values=$res->result_array();

                // values key, used for front end retrival of lists
                    $key="";

                // each nvar has its own value array which defines its vvalues for the vtypes that define the node
                // this set of values is one for each vtype associated with this node
                    foreach ($values as $value)
                    {
                        $key.=$value['var_type_id']."_".$value['var_value_id']."-";

                        if (is_numeric($value['undefined_value']))
                        {
                            $nvars[$x]['vals'][str_replace(" ","_",$value['var_type_name'])]=$value['undefined_value'];
                        }
                        else
                        {
                            $nvars[$x]['vals'][str_replace(" ","_",$value['var_type_name'])]=$value['var_value'];
                        }
                    }

                $key.=$nvars[$x]['stock_level'];

                $nvars[$x]['key']=$key;
            }

        return $nvars;
    }

    /* *************************************************************************
         get_nvar() -
         @param int $nvar_id - the nvar id
         @return array $nvar
    */
    public function get_nvar($nvar_id)
    {
        $query=$this->db->select('*')->from('nvar')->where(array('nvar_id'=>$nvar_id));
        $res=$query->get();
        $nvar=$res->row_array();

        $query=$this->db->select('*')->from('nvar_value')->where(array('nvar_id'=>$nvar['nvar_id']))->join('var_type','nvar_value.var_type_id=var_type.var_type_id')->join('var_value','nvar_value.var_value_id=var_value.var_value_id','left outer');
        $res=$query->get();
        $values=$res->result_array();

        foreach ($values as $value)
        {
            if (is_numeric($value['undefined_value']))
            {
                $nvar['vals'][str_replace(" ","_",$value['var_type_name'])]=$value['undefined_value'];
            }
            else
            {
                $nvar['vals'][str_replace(" ","_",$value['var_type_name'])]=$value['var_value'];
            }
        }

        return $nvar;
    }

    /* *************************************************************************
         add_variations() - add a new set of variations to the product
         @param array $vals - a post array of values
         @return
    */
    public function add_variations($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_variations_start');

        // define vars
            $node_id=$vals['node_id'];
            $nvars=array();
            $duplicate_warning='';

        // iterate over the post array
            foreach($vals as $key=>$value)
            {
                if (strpos($key,"_")>0 &&
                    $key!='node_id')
                {
                    $ksplit=explode("_",$key);
                    $nvars[$ksplit[0]][$ksplit[1]]=$value;
                }
            }

        // empty first
            $return['warning']='';

        // iterate over nvar array
            foreach ($nvars as $nvar)
            {
				// skip if the variation was defined
					if (0==$this->variation_model->nvar_exists($node_id,$nvar))
					{
						$skip_insert=0;
					}
					else
					{
						$skip_insert=1;
						$return['warning']=' - NB some variations were not added as they already existed';
					}

				// only insert if the variation is not a duplicate
					if (0==$skip_insert)
					{
						$insert_data=array(
							'node_id'=>$vals['node_id'],
							'sale_price'=>$nvar[$this->user['price_vtype_ref'].'U'],
							'price'=>$nvar[$this->user['price_vtype_ref'].'U'],
							'post_calc'=>$nvar[$this->user['pcalc_vtype_ref'].'U'],
							'stock_level'=>$nvar['stockU'],
							'stock_threshold'=>$nvar['threshU']
							);

						$this->db->insert('nvar',$insert_data);
						$nvar_id=$this->db->insert_id();
					}

                // remove these, they are used
                    unset($nvar[$this->user['price_vtype_ref'].'U']);
                    unset($nvar[$this->user['pcalc_vtype_ref'].'U']);
                    unset($nvar['stockU']);
                    unset($nvar['threshU']);

				// the variation values
					foreach($nvar as $key=>$value)
					{
						if (strpos($key,'U')>0)
						{
							$vtype_id=str_replace('U','',$key);
							if (0==$skip_insert)
							{
								$insert_data=array(
									'nvar_id'=>$nvar_id,
									'var_type_id'=>$vtype_id,
									'undefined_value'=>$value
									);
								$this->db->insert('nvar_value',$insert_data);
							}
						}
						else
						{
							$val=explode("_",$value);

							if (0==$skip_insert)
							{
								$insert_data=array(
									'nvar_id'=>$nvar_id,
									'var_type_id'=>$key,
									'var_value_id'=>$val[0]
									);
								$this->db->insert('nvar_value',$insert_data);
							}
						}
					}
            }

        $return['node_id']=$node_id;
        return $return;

        /* BENCHMARK */ $this->benchmark->mark('func_add_variations_end');
    }

    /* *************************************************************************
         save_nvars() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save_nvars($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_nvars_start');

        // node
            $node=$this->node_model->get_node($vals['node_id']);

        if (isset($vals['main']) ?  $main_pair=explode('=',$vals['main']) : $main_pair=array() );
        if (isset($main_pair[1]) ? $main_id=$main_pair[1] : $main_id=0 );

        // get the nvars
            $nvars=$this->variation_model->get_nvars($node['id']);

        // dates
            $start_split=explode("-",$vals['sale_start']);
            $end_split=explode("-",$vals['sale_end']);
            $sale_start=substr($start_split[2],0,4).'-'.$start_split[1].'-'.$start_split[0];
            $sale_end=substr($end_split[2],0,4).'-'.$end_split[1].'-'.$end_split[0];

        // iterate over them
            foreach ($nvars as $nvar)
            {
                if (isset($vals[$nvar['nvar_id'].'remove']) &&
                    'on'==$vals[$nvar['nvar_id'].'remove'])
                {
                    $this->db->delete('nvar',array('nvar_id'=>$nvar['nvar_id']));
                }
                else
                {
                    if (isset($vals[$nvar['nvar_id'].'_price']))
                    {
                        if ($nvar['nvar_id']==$main_id)
                        {
                            $main=1;
                            // update price in the node table
                                $update_data = array(
                                    'price' =>$vals[$nvar['nvar_id'].'_price'],
                                    'sale_price' =>$vals[$nvar['nvar_id'].'_sale'],
                                    'sale_start' =>$sale_start,
                                    'sale_end' =>$sale_end
                                );
                                $this->db->where('id', $node['id']);
                                $this->db->update('node', $update_data);
                        }
                        else
                        {
                            $main=0;
                        }
                        $update_data = array(
                            'price'=>$vals[$nvar['nvar_id'].'_price'],
                            'sale_price'=>$vals[$nvar['nvar_id'].'_sale'],
                            'sale_start'=>$sale_start,
                            'sale_end'=>$sale_end,
                            'post_calc'=>$vals[$nvar['nvar_id'].'_postcalc'],
                            'main'=>$main,
                            'stock_level'=>$vals[$nvar['nvar_id'].'_stock'],
                            'stock_threshold'=>$vals[$nvar['nvar_id'].'_thresh']
                        );

                        $this->db->where('nvar_id', $nvar['nvar_id']);
                        $this->db->update('nvar', $update_data);
                    }
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_nvars_end');

        return $node;
    }

    /* *************************************************************************
        nvar_adder() - builds the adder row for creating new variations, with one
            form input for each node vtype, for each way in which this node varies
            refreshed for
        @param array $nvar_types - the variation types to display on the adder
        @return string $add - the row html
    */
    public function nvar_adder($nvar_types)
    {
        $this->load->helper('form');

        $add='';

        // stock and threshold adders, so when variations are created the stock and threshold vals can be set en masse
            $add.="<span id='thresh_adder' class='pvariation_input right'>";
            $add.="<input id='threshvalue_add' class='nvtype_text form_field thresh' type='text' name='thresh_add' value='0' onblur='update_preview_vals(\"thresh\")' onkeyup='check_numeric(\"#threshvalue_add\")' tabindex='4'/>";
            $add.="</span>";
            $add.="<span id='stock_adder' class='pvariation_input right'>";
            $add.="<input id='stockvalue_add' class='nvtype_text form_field stock' type='text' name='stock_add' value='0' onblur='update_preview_vals(\"stock\")' onkeyup='check_numeric(\"#stockvalue_add\")'  tabindex='3'/>";
            $add.="</span>";

        $ti=5;
        foreach ($nvar_types as $nvar_type)
        {
            // variables for ease of read
                $vtype_id=$nvar_type["var_type_id"];
                $vtype_name=$nvar_type["var_type_name"];

            // output each form input, one for each node vtype, each within an update preview js function
            // which will update the preview rows
                $add.="<span class='".$vtype_id."vtype pvariation_input left'>";
                if (!isset($nvar_type['vals']))
                {
                    // set tab index
                        $ti++;

                    // just simple text boxes, price is a different colour to highlight
                        if ('price'==$vtype_name)
                        {
                            $add.="<span id='price_adder'>";
                            $add.="<input id='".$vtype_id."value_add' class='nvtype_text form_field left ' type='text' name='".$vtype_id."' value='0' onblur='update_preview_vals(".$vtype_id.")' onkeyup='check_numeric(\"#".$vtype_id."value_add\")' tabindex='2'/><span id='adder_pound'>&pound;&nbsp;</span>";
                            $add.="</span>";
                        }
                        elseif ('post calc'==$vtype_name)
                        {
                            $add.="<span id='post_calc_adder'>";
                            $add.="<input id='".$vtype_id."value_add' class='nvtype_text form_field left ' type='text' name='".$vtype_id."' value='0' onblur='update_preview_vals(".$vtype_id.")' onkeyup='check_numeric(\"#".$vtype_id."value_add\")' tabindex='1' autofocus/>";
                            $add.="</span>";
                        }
                        else
                        {
                            $add.="<input id='".$vtype_id."value_add' class='nvtype_text form_field left ' type='text' name='".$vtype_id."' value='0' onblur='update_preview_vals(".$vtype_id.")' onkeyup='check_numeric(\"#".$vtype_id."value_add\")' tabindex='".$ti."'/>";
                        }
                }
                else
                {
                    // these are selects with each of the vtypes vvalues output
                        $add.="<select id='".$vtype_id."' name='".$vtype_id."' class='nvtype_select' multiple='multiple' size='8'  onchange='update_preview()'>";
                        for ($y=0;$y<count($nvar_type['vals']);$y++)
                        {
                            // here pack of dealt with differently to enforce the default of 'pack of 1' rather than
                            // all pack quantities being shown in the default adder / preview
                            // also, if the value is not selected in the reloaded selections it is not selected in the
                            // display either
                                if ('pack of'==$vtype_name &&
                                    1!=$nvar_type['vals'][$y]['name'])
                                {
                                    $selected="";
                                }
                                else
                                {
                                    $selected="selected";
                                }

                            // add the option now
                                $add.="<option name='".$nvar_type['vals'][$y]['id']."' ".$selected.">".$nvar_type['vals'][$y]['name']."</option>";
                        }
                        $add.="</select>";
                }
                $add.="</span>";
        }

        return $add;
    }

    /* *************************************************************************
        nvar_add_preview() - refreshs the adder and preview output on node vtype selection
            this is only done when node vtypes are being selected before variations are
            created
        @param array $selections - the details of the current selection - a set of default values
            (remember pquan) or a set of values taken from the adder when selections are made
        @return string $preview - the preview box html
    */
    public function nvar_add_preview($selections)
    {
        // stock and threshold need to be added to the array so they appear in the list on the preview
            $stock=array(
                'var_type_name'=>'stock',
                'var_type_id'=>'stock',
                'val'=>''
            );
            $thresh=array(
                'var_type_name'=>'thresh',
                'var_type_id'=>'thresh',
                'val'=>''
            );

            $selections=array_reverse($selections);

            $selections[]=$stock;
            $selections[]=$thresh;

            $selections=array_reverse($selections);

        $preview_html=$this->preview_rows($selections);

        return $preview_html;
    }

    /* *************************************************************************
        preview_rows() - builds the preview panel for add variations -  NB the rows are
            built by creating a column for each vtype and then iterating through a
            full set of values for that column, determining the number of times to
            output each vvalue per iteration and the number of iterations by the
            position from left to right and number of vvalues for each vtype - this
            has made easier code to write and maintain than trying to generate each
            row row by row
        @param array $sels - the selected variation definitions, includes the vtypes
        @return string $preview - the set of rows for output
    */
    public function preview_rows($sels)
    {
        $preview='';

        // we need the last array index number to stop a count function which is used in each loop
            $last_index=count($sels)-1;

        // full count -  needed to output one text box value for each row
            $full_count=1;
            for ($x=0;$x<count($sels);$x++)
            {
                if (isset($sels[$x]['vals']))
                {
                    $full_count*=count($sels[$x]['vals']);
                }
            }

        // iterate over the vtypes, pushing each one out as a column of carefully ordered
        // vvalues
            for ($c=0;$c<count($sels);$c++)
            {
                $nvar_count=1;

                // count up all the vals from further down the array ($c+1 to $last_index)
                // this bit tells us how many times we need to output each vvalue, so columns
                // on the left this is higher than on the right
                    $val_count=1;
                    for ($x=$c+1;$x<=$last_index;$x++)
                    {
                        if (isset($sels[$x]['vals']))
                        {
                            $val_count*=count($sels[$x]['vals']);
                        }
                    }

                // column floats
                    if (isset($sels[$c]['vals']) ? $class='nvtype_left' : $class='nvtype_right' );

                // now open up the column span, price column is a bit special as elsewhere (bold this time)
                    $preview.="<span id='".str_replace(' ','_',$sels[$c]['var_type_name'])."_preview' class='var_column ".$class."'>";

                // we have vals, so we need to carefully output them depending on their position in the array
                // you are actually going to have to study output and code here because I can't describe this
                // clearly in English - I'd love it if you did, I'd even buy you a beer ;-)
                    if (isset($sels[$c]['vals']))
                    {
                        // the number of time to repeat the sequence, this increases as we move through the array
                            $repeat_count=$full_count/($val_count*count($sels[$c]['vals']));

                        // repeat the sequence x number of times
                            for ($x=0;$x<$repeat_count;$x++)
                            {
                                // each sequence step through all vals in the val array
                                    for ($y=0;$y<count($sels[$c]['vals']);$y++)
                                    {
                                        // for each val in the array output it the val count amount of times
                                            for ($z=0;$z<$val_count;$z++)
                                            {
                                                $val_text=$sels[$c]['var_type_name'].":&nbsp;".$sels[$c]['vals'][$y]['name'];
                                                $preview.=$val_text."<br/>";
                                                $preview.="<input type='hidden' name='".$nvar_count."_".$sels[$c]['var_type_id']."' value='".$sels[$c]['vals'][$y]['id']."_".$val_text."'/>";
                                                $nvar_count++;
                                            }
                                    }
                            }
                    }
                // else we just output a full count of text outputs - we use the full count, a cartesian product of all the selections
                // this is as there is no variation in the vvalue
                    else
                    {
                        for ($y=0;$y<$full_count;$y++)
                        {
                            if (isset($sels[$c]['val']) ? $val=$sels[$c]['val'] : $val='');
                            if ('price'==$sels[$c]['var_type_name'] ? $pound_insert="&pound;" : $pound_insert='' );
                            $preview.=$sels[$c]['var_type_name'].":".$pound_insert."<span class='".$sels[$c]['var_type_id']."U'>".$val."</span><br/>";
                            $preview.="<input class='".$sels[$c]['var_type_id']."U_hid' type='hidden' name='".$nvar_count."_".$sels[$c]['var_type_id']."U' value='".$val."'/>";
                            $nvar_count++;
                        }
                    }
                    $preview.="</span>";
            }

        return $preview;
    }

    /* *************************************************************************
        nvar_exists() - checks to see if the node variations exists - so we don't add
            an nvar with a set vtype vvalue defintions that matches one that already
            exists
        ** NB note how this doesn't use the text values with no vvalues defined **
        @param int $node_id - the id of the node to which the variation belongs
        @param array $nvar - the proposed variation details
        @return Boolean $fail - add this nvar or not
    */
    public function nvar_exists($node_id,$nvar)
    {
        // get the vals from the nvar to add in into an array
            $vals=array();
            foreach($nvar as $key=>$value)
            {
                if (strpos($key,'U')>0)
                {
                }
                else
                {
                    $val=explode("_",$value);

                    $sp_val=explode(":",$val[1]);
                    $vals[str_replace(" ","_",$sp_val[0])]=$sp_val[1];
                }
            }

        // get all nvars which have this node id
            $nvars=$this->get_nvars($node_id);

        // iterate over nvars
            foreach ($nvars as $nvar)
            {
                // we hit a match then return 1=true
                    if (count(array_diff_assoc($vals,$nvar['vals']))==0)
                    {
                        return 1;
                    }
            }

        // no match hit, return 'fail' = 0
            return 0;
    }

    /* *************************************************************************
         strip_pack_quantities() - gets rid of all pack quanitites except for 1, used for a sensible default
         @param array $types - the vtype set
         @return array $types - stripped
    */
    public function strip_pack_quantities($types)
    {
        // strip out the pack of var type vals for default
            for ($x=0;$x<count($types);$x++)
            {
                // only pack of
                    if ('pack of'==$types[$x]['var_type_name'])
                    {
                        // only if vals set
                            if (isset($types[$x]['vals']))
                            {
                                // over vals
                                    for ($y=0;$y<count($types[$x]['vals']);$y++)
                                    {
                                        // get the id of the pack of val (needs to weork this way for market place functionality)
                                            if ($types[$x]['vals'][$y]['name']=="1")
                                            {
                                                $id=$types[$x]['vals'][$y]['id'];
                                            }
                                    }
                            }
                            if (is_numeric($id))
                            {
                                // get rid of all pack of vals
                                    unset($types[$x]['vals']);

                                // set new with the id
                                    $types[$x]['vals'][0]['id']=$id;
                                    $types[$x]['vals'][0]['name']="1";
                                break;
                            }
                    }
            }

        return $types;
    }

    /* *************************************************************************
        set_vjson() - saves the variations as json strings in the product table
            this is for the speed of load, no big table joins on front end
        @param int $node_id - the product to operate on
        @return void
    */
    public function set_vjson($node_id)
    {
        $variations=$this->variation_model->get_nvars($node_id);

        $vars_split=array();
        $vars_details=array();
        $keys=array();

        // set up the variations array to store by key so we can easily reference the price
        // etc when using separate variation selectors
        foreach ($variations as $v)
        {
            // dropdown key
                $key='#';
                foreach ($v['vals'] as $k=>$val)
                {
                    $key.=$val."-";
                }
                $key=substr($key,0,-1);

                $vars_details[$key]=$v;

            // list key
                $keys[]=$v['key'];
        }


        // get the var types for this node
            $nvar_types=$this->get_nvar_types($node_id);

        // loop over each nvar type
            foreach ($nvar_types as $nvt)
            {
                // establish as an array to store these values - key adds underscores to refer to array
                    $var_type_name_key=str_replace(' ','_',$nvt['var_type_name']);
                    $vars_split[$var_type_name_key]=array();

                // now go through each variation to look at the values for this nvar type
                    foreach ($variations as $v)
                    {
                        if (isset($v['vals'][$var_type_name_key]))
                        {
                            $val=$v['vals'][$var_type_name_key];

                            // uniqueness enforced
                                if (!in_array($val,$vars_split[$var_type_name_key]))
                                {
                                    $vars_split[$var_type_name_key][]=$val;
                                }
                        }
                    }
            }

        $vdet=json_encode($vars_details);
        $vsplit=json_encode($vars_split);
        $ks=json_encode($keys);

        $update_data = array(
            'nvar_json'=>$vdet,
            'nvar_json_split'=>$vsplit,
            'nvar_keys'=>$ks
        );

        $this->db->where('node_id', $node_id);
        $this->db->update('product', $update_data);
    }

    /* *************************************************************************
         get_var_values_html() - gets a set of vtype vvalues as an html output with remove
            buttons for output on the variation defintion page
        ** this function is used by the variation type management page, not the
            node specific variations page **
         @param array $vtype - the vtype to output
         @return $var_vals_html - the output html
    */
    public function get_var_values_html($vtype)
    {
        $var_vals_html='';

        // output the vals if there are any set
        // else output no vals and message
            if (isset($vtype['vals']))
            {
                foreach ($vtype['vals'] as $val)
                {
                    // count used
                        $used=$this->count_used($val['var_value_id']);

                    // colour row for active
                        if ($used>0 or
                            ('pack of'==$vtype['var_type_name'] &&
                            1==$val['var_value']))
                        {
                            $row_colour=' light_blue ';
                        }
                        else
                        {
                            $row_colour=' light_grey ';
                        }

                    // ids and stuff for js
                        $var_vals_html.="<div id='".$val['var_value_id']."' class='vtv_row ".$row_colour."'>";
                        $var_vals_html.="<span class='vtv_name'>".$val['var_value']."</span>";

                    // pack of one can't be deleted, effects system and all items must have a positive integer pack of value
                        if ('pack of'==$vtype['var_type_name'] &&
                            1==$val['var_value'])
                        {
                            $var_vals_html.="<span class='vtv_block'>required</span>";
                        }
                        else
                        {
                            // add remove button for all others
                                if (0==$used)
                                {
                                    $var_vals_html.="<span class='vtv_remove' onclick='remove_vvalue(".$val['var_value_id'].")'>remove</span>";
                                }
                                else
                                {
                                    $var_vals_html.="<span class='vtv_block'>used ".$used." times</span>";
                                }
                        }

                    $var_vals_html.="</div>";
                }
            }
            else
            {
                $var_vals_html.="<div class='vtype_val_row'>";
                $var_vals_html.="no values set - you will be asked to create these manually when using this variation on a product <span class='strong'> - it is strongly advised that you create all values for a variation if there is a finite number of values</span>";
                $var_vals_html.="</div>";
            }

        return $var_vals_html;
    }

    /* *************************************************************************
         save_var_type() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function save_var_type($post)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_var_type_start');

        // first check if a vtype with the same name already exists
            $query=$this->db->select('*')->from('var_type')->where(array('user_id'=>$this->user['user_id'],'var_type_name'=>$post['vtype']));
            $res=$query->get();
            $result=$res->row_array();

        // if not then add that variation type and reload
        // if vtype name is empty also fail this test
        // else reload with fail message
            if (0==count($result) &&
                strlen($post['vtype'])>0)
            {
                $insert_data=array(
                    'var_type_name'=>$post['vtype'],
                    'user_id'=>$this->user['user_id']
                    );
                $this->db->insert('var_type',$insert_data);

                $message['text']='variation type added';
                $message['pass']='success';
            }
            else
            {
                if (strlen($post['vtype'])>0)
                {
                    $message['text']="the variation type already exists";
                }
                else
                {
                    $message['text']="please enter a variation name";
                }

                $message['pass']='fail';
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_var_type_end');

        return $message;
    }

    /* *************************************************************************
         save_var_value() - adds a new var value, only if it is not already there
         @param array $vals - the value itself and the var type to add it to, got from the ajax request
         @return
    */
    public function save_var_value($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_var_value_start');

        // check for the value in the db
            $query=$this->db->select('*')->from('var_value')->where(array('var_type_id'=>$vals['var_type_id'],'var_value'=>$vals['var_value']));
            $res=$query->get();
            $result=$res->row_array();

        // only add it if it does not already exist
            if (0==count($result))
            {
                $insert_data=array(
                    'var_type_id'=>$vals['var_type_id'],
                    'var_value'=>$vals['var_value']
                    );
                $this->db->insert('var_value',$insert_data);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_var_value_end');
    }

    /* *************************************************************************
         remove_var_value() - removes a var value
         @param int $vvid - the var value to remove
         @return
    */
    public function remove_var_value($vvid)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_remove_var_value_start');

        $this->db->delete('var_value',array('var_value_id'=>$vvid));

        /* BENCHMARK */ $this->benchmark->mark('func_remove_var_value_end');
    }

    /* *************************************************************************
         variation_selector() - gets a select box with the node variations in it
         @param array $nvars - the nodes variations
         @param int $selected - the id of the currently selected variation
         @return string $html - the select box output
    */
    public function variation_selector($node,$selected)
    {
        // decode the json for this product
            $vdets=json_decode($node['nvar_json'],true);
            $vsplit=json_decode($node['nvar_json_split'],true);

        ksort($vdets);
        $one_val_only=array();
        /*dev_dump($vdets);
        dev_dump($vsplit);*/

        // get rid of the values that are not multiples
            foreach ($vsplit as $key=>$value)
            {
                if (count($value)<1)
                {
                    unset ($vsplit[$key]);
                }
                else
                {
                    if (1==count($value))
                    {
                        $one_val_only[]=$key;
                    }
                    sort($vsplit[$key]);
                }
            }

        //dev_dump($vsplit);
        $ordered_keys=$this->concat($vsplit);
        //dev_dump($ordered_keys);

        if (count($vdets)>1)
        {
            $html="<select id='nvar_selector' class='nvar_selector' name='nvar_id' onchange='update_panel_text()' tabindex='10' autofocus>";

            //dev_dump($nvars);

            $c=1;
            $count=count($ordered_keys);
            $last_break=0;
            foreach ($ordered_keys as $k)
            {
                if (isset($vdets['#'.$k]))
                {
                    $nvar=$vdets['#'.$k];
                    // selected
                        if ($selected==$nvar['nvar_id'] ? $sel=" selected='selected' " : $sel=" " );

                    // stock
                        $stock=$this->set_stock_output($nvar);

                    // price
                        $price=$this->set_price_output($nvar);

                    $html.="<option value='".$nvar['nvar_id']."' ".$sel." ".$stock['disabled'].">";
                    foreach ($nvar['vals'] as $k=>$v)
                    {
                        if (!in_array($k,$one_val_only))
                        {
                            $html.=str_replace('_',' ',$k)." ".addslashes(str_replace('_',' ',$v))."; ";
                        }
                    }
                    //$stock['append']."&nbsp;".
                    $html.=$price;
                    $html.="</option>";
                    $last_break=0;
                }
                else
                {
                    // break
                    if ($c<$count &&
                        0==$last_break)
                    {
                        $html.="<option value='0'  disabled='disabled'> ------------------------------- </option>";
                    }
                    $last_break=1;
                }
                $c++;
            }

            $html.="</select>";
        }
        else
        {
            // no selector, only one variation
            if (1==count($vdets))
            {
                foreach ($vdets as $v)
                {
                    $html="<input id='nvar_selector' type='hidden' name='nvar_id' value='".$v['nvar_id']."'/>";
                    break;
                }
            }
        }

        return $html;
    }

    public function concat(array $array)
    {
        $current = array_shift($array);
        if(count($array) > 0)
        {
            $results = array();
            $temp = $this->concat($array);
            foreach($current as $word)
            {
                foreach($temp as $value)
                {
                    $results[] =  $word . '-' . $value;
                }
                $results[]='break';
            }
            return $results;
        }
        else
        {
           return $current;
        }
    }

    /* *************************************************************************
        format_add_panel() - creates the output in text for the add panel, changed by selection from drop down
            defaults to the main variation on load
        @param array $nvar - the nvar to format
        @return string $html - the html for the add panel text
    */
    public function format_add_panel($nvar)
    {
        $html="<span id='main_add_text'>";
        // stock
            $stock=$this->set_stock_output($nvar);

        // price
            $price=$this->set_price_output($nvar,1);

        foreach ($nvar['vals'] as $k=>$v)
        {
            $html.=str_replace('_',' ',$k)." ".str_replace('_',' ',$v)." ";
        }
        $html.=$price.$stock['append'];
        $html.="</span>";

        return $html;
    }
    /* *************************************************************************
        set_stock_output() - formats some vals for this nvar
        @param array $nvar - the nvar
        @return array vals set for this stock level
    */
    public function set_stock_output($nvar)
    {
        $stock=array();
        if ($nvar['stock_level']>0)
        {
            $stock['disabled']=" ";
            $stock['append']=" - ".$nvar['stock_level']." in stock";
        }
        else
        {
            $stock['disabled']=" disabled='disabled' ";
            $stock['append']=" - out of stock";
        }
        return $stock;
    }
    /* *************************************************************************
        set_price_output() - formats some vals for this nvar
        @param array $nvar - the nvar
        @param int $main - extra formatting if this is an add panel text main output
        @return array vals set for this price
    */
    public function set_price_output($nvar,$main=0)
    {
        $now=date('Y-m-d',time());

        if ($nvar['sale_start']<=$now &&
            $nvar['sale_end']>=$now)
        {
            if (1==$main)
            {
                $price="<span class='price_was'>was ".format_price($nvar['price'])."</span>&nbsp;<span class='sale_on'>NOW ".format_price($nvar['sale_price'])." !!</span>&nbsp;";
            }
            else
            {
                $price="was ".format_price($nvar['price'])." NOW ".format_price($nvar['sale_price']);
            }
        }
        else
        {
            $price=format_price($nvar['price']);
        }
        return $price;
    }
}
