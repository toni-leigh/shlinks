<script type='text/javascript'>
    var main_id=<?php echo $main_var['nvar_id']; ?>;
</script>

<?php
    if (count($nvars)>0)
    {
        $var_list_class='panel_close';
        $var_list_func='close_height';
        $var_list_state='';
        $other_panels_class='panel_open';
        $other_panels_func='open_height';
        $other_panels_state='panel_closed';
    }
    else
    {
        $var_list_class='panel_open';
        $var_list_func='open_height';
        $var_list_state='panel_closed';
        $other_panels_class='panel_close';
        $other_panels_func='close_height';
        $other_panels_state='';
    }
?>

<div class='admin_left'>
    <div class='panel'>
        <h2>
            <span id='newvar_heading' class='ad_heading_text noselect' onclick='<?php echo $other_panels_func; ?>("newvar")'>How Does '<?php echo $edit_node['name']; ?>' Vary ?</span>
            <span id='newvar_show' class='sprite <?php echo $other_panels_class; ?> noselect' onclick='<?php echo $other_panels_func; ?>("newvar")'></span>
        </h2>
        <div id='newvar_panel' class='<?php echo $other_panels_state; ?> panel_details'>
            <form id='nvtype_adder' method='post' action='/node_admin/set_nvars'>
            <?php
                // disable them all if there are variations set
                    if (count($nvars)>0 ? $disabled=" disabled='disabled' " : $disabled="");
                
                // out put the different variation types for this product
                    foreach ($var_types as $vtype)
                    {
                        // var for loop
                            $vtype_id=$vtype["var_type_id"];
                            $vtype_name=$vtype["var_type_name"];
                            
                        // check if they are in the node variation types
                            $checked="";
                            foreach ($nvar_types as $nvtype)
                            {
                                if ($vtype['var_type_id']==$nvtype['var_type_id'])
                                {
                                    $checked=" checked='checked' ";
                                    break;
                                }
                            }
                            
                            $extra_class=" nvar_".str_replace(' ','_',$vtype_name);
                            
                        // the differences between permanent and variable variation types are set here
                            if (!in_array($vtype_name,array('post calc','price')))
                            {
                                // selected or not
                                    $selected=" nvtype_unselected ";
                                    $hidden_sel=0;
                                    for ($x=0;$x<count($nvar_types);$x++)
                                    {
                                        if ($nvar_types[$x]['var_type_id']==$vtype['var_type_id'])
                                        {
                                            $selected=" nvtype_selected ";
                                            $hidden_sel=1;
                                        }
                                    }
                                    
                                // switch off if variations set
                                    if (count($nvars)>0)
                                    {
                                        $js="";
                                        $master_class="nvar_output";
                                    }
                                    else
                                    {
                                        $js="onclick='update_adder(".$vtype_id.",".$node_id.")'";
                                        $master_class="nvtype_selector";
                                    }
                                    
                                // output
                                    if (1==$hidden_sel || 0==count($nvars))
                                    {
                                        ?>         
                                            <div id='<?php echo $vtype_id; ?>_panel' class='<?php echo $master_class; ?> <?php echo $selected; ?> nvtype_hover' <?php echo $js; ?>>
                                                <input id='<?php echo $vtype_id; ?>' type='hidden' name='<?php echo $vtype_id; ?>' value='<?php echo $hidden_sel; ?>'/><?php echo $vtype_name; ?>
                                            </div>                      
                                        <?php
                                    }
                            }
                    }
                    /* if (0==count($nvars))
                    {
                        ?>
                            <span class='full_width'>
                                <input id='nvar_type_submit' class='submit' type='submit' name='submit' value='next step ...'/>
                            </span>
                        <?php
                    } */
            ?>
            </form>
        </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='curr_vars_heading' class='ad_heading_text noselect' onclick='<?php echo $other_panels_func; ?>("curr_vars")'>Current '<?php echo $edit_node['name']; ?>' Variations</span>
            <span id='curr_vars_show' class='sprite <?php echo $var_list_class; ?> noselect' onclick='<?php echo $var_list_func; ?>("curr_vars")'></span>
        </h2>
        <div id='curr_vars_panel' class='<?php echo $var_list_state; ?> panel_details'>
        <?php
        if ('pound'==$product['sale_type'])
        {
            $pound_checked=" checked='checked' ";
            $symbol='&pound;';
        }
        else
        {
            $pound_checked="";
            $symbol='%';
        }
        if ('perc'==$product['sale_type'] ? $perc_checked=" checked='checked' " : $perc_checked="" );
        if (count($nvars)) // only display the variation panel if there are variations set
        {              
            // sale date values
                $start_split=explode("-",$nvars[0]['sale_start']);
                $end_split=explode("-",$nvars[0]['sale_end']);
                $start=$start_split[2].'-'.$start_split[1].'-'.$start_split[0];
                $end=$end_split[2].'-'.$end_split[1].'-'.$end_split[0];
            
            echo form_open("node_admin/save_variations",array('id'=>'variation_editor'),array('node_id'=>$node_id));            
            ?>
            <div id='nvar_filter'>
                <span id='filter_text'>filter variations (letters and numbers required to work, i.e. 'size 12' rather than '12')</span>
                <input id='filter' class='form_field' type='text' value='' onkeyup='filter_variations()'/>
            </div>
            <div class='nvar_row'>
                <div id='sale_settings'>
                    <input id='sale_applied' type='hidden' name='sale_applied' value='0'/>
                    <span id='master_sale_price'>
                        <span id='sale_type_heading' class='pound left'><?php echo $symbol; ?></span>
                        <input id='master_sale' class='form_field sale_field' name='master_sale' type='text' value='<?php echo $product['sale_amount']; ?>' tabindex='1' onkeyup='check_numeric("#master_sale")' onchange='set_changes()' />
                    </span>
                    <span id='sale_dates'>
                        <input id='sale_start' class='form_field' name='sale_start' type='text' value='<?php echo $start; ?>' tabindex='2' onchange='set_changes()'/>
                        <script>
                            $(document).ready(function() {
                                $("#sale_start").datepicker({dateFormat: 'dd-mm-yy'});
                            });
                        </script>
                        <input id='sale_end' class='form_field' name='sale_end' type='text' value='<?php echo $end; ?>' tabindex='3'/>
                        <script>
                            $(document).ready(function() {
                                $("#sale_end").datepicker({dateFormat: 'dd-mm-yy'});
                            });
                        </script>
                    </span>
                    <span id='master_sale_type'>
                        <input id='sale_type_pound' type='radio' name='sale_type' value='pound' <?php echo $pound_checked; ?> onclick='set_saletype_head("&pound;")'/><label for='sale_type_pound' class='sale_type_text'>&pound;</label>
                        <input id='sale_type_perc' type='radio' name='sale_type' value='perc' <?php echo $perc_checked; ?> onclick='set_saletype_head("%")'/><label for='sale_type_perc' class='sale_type_text'>%</label>
                    </span>
                    <span id='apply_sale' onclick='apply_sale()'>
                        apply discount
                    </span>
                </div>
                <div id='stock_setting'>
                    <span id='add_stock_button' onclick='add_stock()'>+</span>
                    <input id='set_stock' class='form_field stock' name='set_stock' type='text' value='0' tabindex='4' onchange='set_changes()'/>
                </div>
            </div>
            <?php
            $c=0;
            foreach ($nvars as $nvar)
            {
                // counter
                    $c++;
                    
                // vars for ease of read
                    $vid=$nvar['nvar_id'];
                    $price=str_replace(',','',number_format($nvar['price'],2));
                    $sale_price=str_replace(',','',number_format($nvar['sale_price'],2));
                    $post_calc=$nvar['post_calc'];
                    $stock=$nvar['stock_level'];
                    $thresh=$nvar['stock_threshold'];
                
                // set classes and marks
                    if ($stock>$thresh ? $classes=" instock_row " : $classes=" outstock_row " );
                    if (1==$nvar['main'])
                    {
                        $checked=" checked ";
                        $remove="&nbsp;";
                    }
                    else
                    {
                        $checked="";
                        $remove="<input id='".$vid."=remove' class='".$vid."remove remove_checkbox' name='".$vid."remove' type='checkbox' onclick='mark_row(".$vid.")' tabindex='999999' onchange='set_changes()'/>";
                    }
                    
                // variation description string
                    $nvar_string="pack of ".$nvar['vals']['pack_of'];
                    $filter_key="";
                    if (count($nvar['vals'])>1)
                    {
                        $nvar_string.=": ";
                        while (list($key,$value) = each($nvar['vals']))
                        {
                            $filter_key.=$key."_".$value."_";
                            if ('pack_of'!=$key)
                            {
                                $nvar_string.="<span class='nvar_string_val'>".str_replace("_"," ",$key)." ".$value."; </span>";
                            }
                        }
                    }
                    
                // output the row html
                    ?>
                        <div id='<?php echo $filter_key; ?>' class='filter_row'>
                            <div id='<?php echo $vid; ?>_nvar' class='nvar_row <?php echo $classes; ?>'>
                                <span class='advar_sale saved_nvar_value'>
                                    <span class='pound left'>&pound;</span>
                                    <input id='<?php echo $vid; ?>_sale' class='form_field sale_field' name='<?php echo $vid; ?>_sale' type='text' value='<?php echo $sale_price; ?>' tabindex='<?php echo (30000+$c); ?>' onkeyup='check_numeric("#<?php echo $vid; ?>_sale")' onchange='set_changes()'/>
                                </span>
                                <span class='advar_price saved_nvar_value'>
                                    <span class='pound left'>&pound;</span>
                                    <input id='<?php echo $vid; ?>_price' class='form_field price_field' name='<?php echo $vid; ?>_price' type='text' value='<?php echo $price; ?>' tabindex='<?php echo (20000+$c); ?>' onkeyup='check_numeric("#<?php echo $vid; ?>_price")' onchange='set_changes()'/>
                                </span>
                                <span class='nvar_postcalc saved_nvar_value'>
                                    <input id='<?php echo $vid; ?>_postcalc' class='form_field' name='<?php echo $vid; ?>_postcalc' type='text' value='<?php echo $post_calc; ?>' tabindex='<?php echo (40000+$c); ?>' onchange='set_changes()'/>
                                </span>
                                <span class='nvar_string'><?php echo $nvar_string; ?></span>
                                <span class='nvar_main'>                            
                                    <input id='<?php echo $vid; ?>=main' class='<?php echo $vid; ?>main main_radio' name='main' value='main=<?php echo $vid; ?>' type='radio' <?php echo $checked; ?> onclick='mark_main(<?php echo $vid; ?>)' onchange='set_changes()'/>
                                </span>
                                <span class='nvar_stockmain'>
                                    <span class='vinstock'>
                                        <input id='<?php echo $vid; ?>_stock' class='form_field stock' name='<?php echo $vid; ?>_stock' type='text' value='<?php echo $stock; ?>' onblur='mark_main(<?php echo $vid; ?>)' tabindex='<?php echo (10+$c); ?>' onkeyup='check_numeric("#<?php echo $vid; ?>_stock")' onchange='set_changes()'/>
                                        <input id='<?php echo $vid; ?>_thresh' class='form_field thresh' name='<?php echo $vid; ?>_thresh' type='text' value='<?php echo $thresh; ?>' onblur='mark_main(<?php echo $vid; ?>)' tabindex='<?php echo (10000+$c); ?>' onkeyup='check_numeric("#<?php echo $vid; ?>_thresh")' onchange='set_changes()'/>
                                    </span>
                                </span>
                                <div class='nvar_remove'>
                                    <?php echo $remove; ?>
                                </div>
                            </div>
                        </div>
                    <?php
            }
            ?>
                    <input id='save_variations_button' class='submit button right' type='submit' name='submit' value='save edited variations' onclick='unset_changes()'/>
                </form>
                <script language='JavaScript'>
                    var changes_made=0;
                    window.onbeforeunload = confirmExit;
                    function confirmExit()
                    {
                        if (1==changes_made)
                        {
                            return "You have made changes on the 'edit variations' panel. Click 'stay on this page' to return to the page and save the changes. Or click 'leave this page' to navigate away and lose the changes";
                        }
                    }
                </script>
            <?php
        }
        else
        {
            ?>
                <span class='empty_admin_panel'>there are no variations set yet for this product</span>
            <?php
        }
    ?>
    </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='add_vars_heading' class='ad_heading_text noselect' onclick='<?php echo $other_panels_func; ?>("add_vars")'>Combinations to Add New Sets of Variations</span>
            <span id='add_vars_show' class='sprite <?php echo $other_panels_class; ?> noselect' onclick='<?php echo $other_panels_func; ?>("add_vars")'></span>
        </h2>
        <div id='add_vars_panel' class='<?php echo $other_panels_state; ?> panel_details'>
        <?php
            // if nvars then repeat the var type output
           /* if (count($nvars)>0)
            {              
                // out put the different variation types for this product
                    foreach ($var_types as $vtype)
                    {
                        // var for loop
                            $vtype_id=$vtype["var_type_id"];
                            $vtype_name=$vtype["var_type_name"];                                
            
                            if ('price'==$vtype_name)
                            {
                                $extra_class=' advar_price ';
                            }
                            if ('post calc'==$vtype_name)
                            {
                                $extra_class=' nvar_postcalc ';
                            }
                            
                        // output - check if they are in the node variation types
                            $checked="";
                            foreach ($nvar_types as $nvtype)
                            {
                                if ($vtype['var_type_id']==$nvtype['var_type_id'])
                                {
                                    ?>         
                                        <div id='<?php echo $vtype_id; ?>_panel' class='nvar_selector nvar_selected left permcheck <?php echo $extra_class; ?>'>
                                            <input id='<?php echo $vtype_id; ?>' type='hidden' name='<?php echo $vtype_id; ?>' value='0'/><?php echo $vtype_name; ?>
                                        </div>                      
                                    <?php
                                    break;
                                }
                            }
                    }
            } */
        ?>
        <form id='nvar_adder_form'>
            <span id='nv_adder'>
                <?php echo $adder; ?>
            </span>
        </form>
    </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='previews_vars_heading' class='ad_heading_text noselect' onclick='<?php echo $other_panels_func; ?>("previews_vars")'>Preview Shows What You Are About to Add</span>
            <span id='previews_vars_show' class='sprite <?php echo $other_panels_class; ?> noselect' onclick='<?php echo $other_panels_func; ?>("previews_vars")'></span>
        </h2>
        <div id='previews_vars_panel' class='<?php echo $other_panels_state; ?> panel_details'>
            <?php
                // output the variation adder row for setting the new variation values
                //echo form_open("node_admin/add_variations",array('id'=>'nvar_save_form'),array('node_id'=>$node_id,'onsubmit'=>'check_zeros()'));
                echo form_open("node_admin/add_variations",array('id'=>'nvar_save_form','onsubmit'=>'return check_zeros()'),array('node_id'=>$node_id));
            ?>
                <span class='full_width'>
                    <input id='addvar_submit_top' class='submit' type='submit' name='submit' value='add these variations'/>
                </span>
                <div id='nv_preview'>   
                    <?php echo $preview; ?>     
                </div>
                <span class='full_width'>
                    <input id='addvar_submit' class='submit' type='submit' name='submit' value='add these variations'/>
                </span>
            </form>
        </div>
    </div>
</div>
<div class='admin_instructions'>
    <p>
        choose the ways in which this product varies using the buttons at the top, in answer to the question
        <strong>'how does this product vary?'</strong>
    </p>
    <p>
        once you have set these click 'finished' you will be able to create all the variations of this product
    </p>
    <p>
        NB - to create just one variation then select none of the checks and click finished
    </p>
    <p>
        <strong>you must complete this choice before creating variations</strong>
    </p>    
    <p>
        you can edit variations here
    </p>
    <p>
        you can set stock levels and a threshold below which a warning is set <span class='purple bold'>[stock]</span>
        <span class='orange bold'>[threshold]</span>
    </p>
    <p>
        you should also pick a main variation, the main variation is used on display panels and initial product
        load, much like the main image
    </p>
    <p>
        now use the selection boxes to create your variations. you can select multiple options using Control +
        left click
    </p>
    <p>
        the variations will automatically combine, so if you select 'green', 'blue'; and '2ft', '5ft'; you will
        generate the variations 'green 2ft', 'green 5ft', 'blue 2ft' and 'blue 5ft'
    </p>
    <p>
        finally click 'add' to add all the variations in the list to the product, you can then make any
        individual tweaks if you need to
    </p>
</div>
                
                
                
                
                
                