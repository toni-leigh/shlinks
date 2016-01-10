<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<script type='text/javascript'>
    if (window.focus)
    {
        // to keep the panels open
            var atag_filter_open=0;
            var mass_adjust_open=0;
    }
</script>
<script type='text/javascript' src='/js/ad_node_list.js'>
</script>
<?php
    // get the config array that defines what node types are excluded from various options on the node list
        if (is_array($this->config->item('admin_node_list_options')))
        {
            $options=$this->config->item('admin_node_list_options');
        }
        else
        {
            echo "you will need to set config['admin_node_list_options'] - see default folder / for example and the ";
            echo "current site ad_node_list for configuration.";
            die();
        }
        ?>
        <div class='admin_left'>
        <?php
            if (count($nodes)>0)
            {
                if (in_array($type,$options['filter']) or
                    in_array($type,$options['admin_tags']))
                {
                    ?>
                    <div class='panel'>
                        <h2>
                            <span id='adnode_filter_heading' class='ad_heading_text noselect' onclick='open_height("adnode_filter")'>Filters</span>
                            <span id='adnode_filter_show' class='sprite panel_open noselect' onclick='open_height("adnode_filter")'></span>
                        </h2>
                        <div id='adnode_filter_panel' class='panel_closed panel_details'>
                            <div id='name_filter'>
                                <label for='filter' id='filter_name'>text filter:</label>
                                <input id='filter' class='form_field' type='text' onkeyup='filter()' autofocus='autofucus'/>
                            </div>
                            <div class='mine_all'>
                                <div class='js_show_mine selected show_mine'>show just mine</div>
                                <div class='js_show_all show_all'>show all site articles</div>
                            </div>
                            <?php
                                if (in_array($type,$options['admin_tags']))
                                {
                                    ?>
                                    <div id='atag_filter_header'>
                                    <span id='show_atag_filter_panel' onclick='show_filters()'/>show admin tag filters</span>
                                    </div>
                                    <div id='admin_tags' style='height:0px;display:none;'>
                                        <div id='ft_selectors'>
                                            <span id='and_filter' class='filter_type ft_unselected' onclick='filter_and()'>all selected</span>
                                            <span id='or_filter' class='filter_type ft_selected' onclick='filter_or()'>any selected</span>
                                        </div>
                                        <select id='tag_filters' class='form_field' name='tag_filters' multiple='multiple' size='10'>
                                        <?php
                                            for ($x=0;$x<count($admin_tags);$x++)
                                            {
                                                echo "<option value='".$admin_tags[$x]."' onclick='choice_response()'>".$admin_tags[$x]."</option>";
                                            }
                                        ?>
                                        </select>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <div class='panel'>
                    <h2>
                        <span id='adnode_list_heading' class='ad_heading_text noselect' onclick='close_height("adnode_list")'><?php echo ucwords($human_type); ?> List</span>
                        <span id='adnode_list_show' class='sprite panel_close noselect' onclick='close_height("adnode_list")'></span>
                    </h2>
                    <div id='adnode_list_panel' class='panel_details'>
                    <?php
                        echo form_open($type.'/set');
                    ?>
                        <div id='adnode_header_row' class='adnode_header_row'>
                            <span id='show_deletes' onclick='show_deletes()'>show delete buttons</span>
                            <input id='master_view_check' class='master_view_check' type='checkbox' onclick='set_views()'/>
                            <label for='master_view_check'>set <?php echo $human_type; ?> visibility on site</label>
                        </div>

                        <div id='sortable'>
                        <?php
                            // iterate over the nodes
                                for ($x=0;$x<count($nodes);$x++)
                                {
                                    // set colour of row and checkbox based on visible
                                        if (1==$nodes[$x]['visible'])
                                        {
                                            $checked=" checked='checked' ";
                                            $ad_class=' ad_nvisible';
                                        }
                                        else
                                        {
                                            $checked='';
                                            $ad_class=' ad_ninvisible';
                                        }

                                    // get some basic values for the rows
                                        $id=$nodes[$x]['id'];
                                        $url=$nodes[$x]['url'];
                                        $name=$nodes[$x]['name'];
                                        $type=$nodes[$x]['type'];

                                        // super admin user also sees the user name for each node
                                            $name.=" <span class='hide_admin_uname'>[".$nodes[$x]['user_name']."]</span>";


                                    // a jsid for filtering
                                        $jsid=strtolower(preg_replace("/[^A-Za-z0-9]/", '', str_replace(' ','_',$name)));

                                    // get the admin tags
                                        if (isset($nodes[$x]['admin_tags']))
                                        {
                                            $atag_array=explode(";",$nodes[$x]['admin_tags']);
                                            $atag_classes=array_intersect($atag_array,$admin_tags);
                                            $atag_class_list="";
                                            foreach ($atag_classes as $a)
                                            {
                                                $atag_class_list.=" ".$a;
                                            }
                                        }
                                        else
                                        {
                                            $atag_class_list="";
                                        }

                                    // build the different links
                                        $visible_check='';
                                        if (!in_array($type,$options['exclude_visible']))
                                        {
                                            $visible_check.="<input id='".$id."_check' class='ad_nvisible_check' type='checkbox' name='".$id."visible' ".$checked." onclick='set_checked(\"".$id."\")'/>";
                                        }
                                        $edit_link='';
                                        if (!in_array($type,$options['exclude_edit']))
                                        {
                                            $edit_link.="<a class='ad_nedit_link' href='/".$type."/".$id."/edit'>edit</a>";
                                        }

                                        $images_link='';
                                        if (!in_array($type,$options['exclude_images']))
                                        {
                                            $images_link.="<a class='ad_nedit_link' href='/".$type."/".$id."/images'>images</a>";
                                        }

                                        $variations_link='';
                                        if ('product'==$type)
                                        {
                                            $variations_link.="<a class='ad_nedit_link' href='/".$type."/".$id."/variations'>variations</a>";
                                        }

                                        $events_link='';
                                        if ('calendar'==$type)
                                        {
                                            $events_link.="<a class='ad_nedit_link' href='/".$type."/".$id."/events'>events</a>";
                                        }

                                        $event_sequence_link='';
                                        if ('event'==$type)
                                        {
                                            $event_sequence_link.="<a class='ad_nedit_link' href='/event/sequence/".$id."'>edit sequence</a>";
                                        }

                                        $view_link='';
                                        if (!in_array($type,$options['exclude_view']))
                                        {
                                            $view_link.="<a class='ad_nedit_link' href='/".$url."' target='_blank'>view</a>";
                                        }

                                    // output the row
                                        echo "<div id='".$jsid."' class='ad_npanel ".$ad_class." ".$atag_class_list." row".$id."' data-owner-id='".$nodes[$x]['user_id']."'>";
                                        echo "<input type='hidden' name='".$id."_set_node_order' value='".$id."'/>";
                                        echo "<input class='onscreen' type='hidden' name='".$id."onscreen' value='1'/>";
                                        echo "<input id='".$id."visnum' class='visnum' type='hidden' name='".$id."visnum' value='".$nodes[$x]['visible']."'/>";
                                        echo $visible_check;
                                        echo "<span class='ad_nname'>";
                                        echo    $name;
                                        echo "</span>";
                                        echo "<div class='ad_nactions'>";
                                        echo    $edit_link;
                                        echo    $images_link;
                                        echo    $variations_link;
                                        echo    $events_link;
                                        echo    $event_sequence_link;
                                        echo    $view_link;
                                        echo    "<div class='ad_ndelete_space'>";
                                        if (0==$nodes[$x]['visible'] &&
                                            $nodes[$x]['id']!=1)
                                        {
                                            echo    "<a class='ad_ndelete' href='/".$type."/".$id."/delete'>delete</a>";
                                        }
                                        echo    "</div>";
                                        echo "</div>";
                                        echo "</div>";
                                }

                                // add sortable
                                    if (in_array($type,$options['sortable']))
                                    {
                                        ?>
                                        <script type='text/javascript'>
                                            window.onload=function() { $('#sortable').sortable(); }
                                        </script>
                                        <?php
                                    }
                        ?>
                        </div>
                    </div>
                </div>
                <?php
                    // mass adjust
                        if ('product'==$type)
                        {
                            ?>
                            <div class='panel'>
                                <h2>
                                    <span id='adnode_mass_heading' class='ad_heading_text noselect' onclick='open_height("adnode_mass")'>Mass Adjust</span>
                                    <span id='adnode_mass_show' class='sprite panel_open noselect' onclick='open_height("adnode_mass")'></span>
                                </h2>
                                <div id='adnode_mass_panel' class='panel_closed panel_details'>
                                    <div id='mass_adjust'>
                                        <div id='show_mass_adjust'>
                                            <input id='adjust_check' name='adjust_check' type='checkbox' onclick='show_mass_adjust()'/><label for='adjust_check'>mass adjust ?</label>
                                        </div>
                                        <div id='mass_adjust_panel' style='width:0px;display:none;'>
                                            <div id='price_stock' class='ma_radios'>
                                                <input id='price_stock_price' class='ma_radio' type='radio' name='price_stock' value='price' onclick='force_unfocus()' checked='checked'><label for='price_stock_price'>&nbsp;&nbsp;price</label>
                                                <input id='price_stock_stock' class='ma_radio' type='radio' name='price_stock' value='stock' onclick='force_focus()'><label for='price_stock_stock'>&nbsp;&nbsp;stock</label>
                                            </div>
                                            <div id='perc_whole' class='ma_radios'>
                                                <input id='perc_pound_perc' class='ma_radio' type='radio' name='perc_pound' value='perc' checked='checked'><label for='perc_pound_perc'>&nbsp;&nbsp;%</label>
                                                <input id='perc_pound_pound' class='ma_radio' type='radio' name='perc_pound' value='pound'><label for='perc_pound_pound'>&nbsp;&nbsp;whole</label>
                                            </div>
                                            <div id='plus_minus' class='ma_radios'>
                                                <input id='plus_minus_plus' class='ma_radio' type='radio' name='plus_minus' value='plus' checked='checked'><label for='plus_minus_plus'>&nbsp;&nbsp;+</label>
                                                <input id='plus_minus_minus' class='ma_radio' type='radio' name='plus_minus' value='minus'><label for='plus_minus_minus'>&nbsp;&nbsp;-</label>
                                            </div>
                                            <input id='ma_value' name='ma_value' class='form_field' type='text' value='0'/>
                                        </div>
                                    </div>
                                    <div id='undo_mass_adjust'>
                                        <a href='/node_admin/undo_mass'>undo last mass adjust</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }

                    // submit button
                        $attr=array(
                            'name'=>'submit',
                            'id'=>'node_list_submit',
                            'class'=>'submit'
                        );
                        echo form_submit($attr,'save '.$human_type.'s');

                    // close form
                        echo form_close();
            }
        ?>
        </div>
        <div class='admin_instructions'>
        <?php
            // create
                $create=0;
                if (in_array($type,$options['create']))
                {
                    $create=1;
                }

            // default node descriptor
                if ('page'==$type ? $desc="page" : $desc="page generated for this ".$human_type );
                if ('page'==$type ? $desc_plur="pages" : $desc_plur="pages generated for these ".$human_type."s" );

            // create new node button
                if (1==$create)
                {
                    echo "<a class='create_new_node action' href='/".$type."/create'>create a new ".$human_type."</a>";
                }
        ?>

        <p>
            use this list to edit any of the <?php echo $human_type; ?>s on the site.
            If the checkbox is selected and the bar is green the page is visible
            on the site - to hide a page uncheck the box and <strong>click to save at
            the bottom of the list</strong>. NB newly created <?php echo $human_type; ?>s default
            to invisible
        </p>

        <p>
            click <strong>'edit'</strong> to bring up the basic info that goes on the <?php echo $desc; ?>
            <?php
                if (1==$create)
                {
                    echo " - this will bring up the same form as used to create";
                }
            ?>
            .
        </p>

        <p>
            click <strong>'images'</strong> for a list of images for this <?php echo $desc; ?>
            where you can add, delete, name and choose a main image. you can also add any
            of the images into the main site gallery here too. NB - the <strong>main image</strong> is used
            by social media amongst other things as a thumbnail image so set them wherever
            possible, even if the image isn't actually displayed on the site.
        </p>

        <p>
            clicking <strong>'view'</strong> will open a new tab (or window) with the <?php echo $desc; ?>
            shown - you can check it before making it visible.
        </p>

        <p>
            <strong>show delete buttons</strong> will bring up a delete button beside every node in the list
            whose visibility is set to 'hidden'. you will then be able to delete the node by clicking that button.
            please note that the node is then <strong>completely unretrievable</strong> so think, perhaps hiding
            the node is the best idea on a live site.
        </p>

        <p>
            the filter at the top will allow you to drill down into a large list of <?php echo $desc_plur; ?>.
            this can be done by the name of the <?php echo $desc; ?> or by using the admin tags if they are
            activated.
        </p>

        <?php
            if ('product'==$type)
            {
                ?>
                <p>
                    <strong>mass adjust</strong> provides functions to edit prices and stock levels for all
                    products in the list. you can use the filter to restrict the products in the list first
                    but bear in mind that the checkbox for visibility will not effect the application of mass
                    adjust. <strong>all products that are visible in the list when the mass adjust is applied
                    will be edited</strong>. there is also an undo to revert the last mass adjust. this is
                    stored until another mass adjust is applied.
                </p>
                <?php
            }
        ?>

    </div>
