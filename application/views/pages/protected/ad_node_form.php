<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
?>
<script type="text/javascript">
    var map;
</script>
<div class='admin_left'>
    <div class='admin_form'>
        <div class='panel'>
            <h2>
                <span id='nodeform_basic_heading' class='ad_heading_text noselect' onclick='close_height("nodeform_basic")'>Basic Details</span>
                <span id='nodeform_basic_show' class='sprite panel_close noselect' onclick='close_height("nodeform_basic")'></span>
            </h2>
            <div id='nodeform_basic_panel' class='panel_details'>
<?php
    if ($type!='video')
    {
        // out put calendar hash
            if ('calendar'==$type &&
                is_numeric($edit_node['id']))
            {
                echo "calendar hash: <strong>".$edit_node['validation_hash']."</strong>";
            }

        // name
            if (is_numeric($edit_node['id']))
            {
                $hidden=array('id'=>$edit_node['id'],'name'=>$edit_node['name']);
                echo form_open($type.'/save','',$hidden);
                echo form_label('Name:','name',array('class' => 'form_label'));
                ?>
                    <div id='saved_name'><?php echo $edit_node['name']; ?></div>
                <?php
            }
            else
            {
                $hidden=array('id'=>$edit_node['id']);
                echo form_open($type.'/save','',$hidden);
                echo form_label('Name:','name',array('class' => 'form_label'));
                ?>
                    <div id='name_warning'>please double check the name of your <?php echo $human_type; ?> as it can't be changed once it is saved</div>
                <?php
                $attr=array(
                    'name'=>'name',
                    'id'=>'name',
                    'class'=>'form_field',
                    'value'=>get_value($edit_node,'name'),
                    'maxlength'=>255,
                    'onkeyup'=>'char_count(\'name\',255)'
                );
                echo form_input($attr);
                ?>
                    <div id='name_count' class='char_counter chars_ok'></div>
                    <script type='text/javascript'>
                        if (window.focus)
                        {
                            $('#name_count').html(255-$('#name').val().length);
                        }
                    </script>
                <?php
            }

        // tags
            $search_nodes=$this->config->item('search_nodes');
            if (in_array($type,$search_nodes))
            {
                echo form_label('Tags:','tags',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'tags',
                    'id'=>'tags',
                    'class'=>'form_field',
                    'value'=>get_value($edit_node,'tags'),
                    'maxlength'=>255,
                    'onkeyup'=>'char_count(\'tags\',255)'
                );
                echo form_input($attr);
                ?>
                    <div id='tags_count' class='char_counter chars_ok'></div>
                    <script type='text/javascript'>
                        if (window.focus)
                        {
                            $('#tags_count').html(255-$('#tags').val().length);
                        }
                    </script>
                <?php
            }
            else
            {
                echo "<input type='hidden' name='tags' value='tags hidden'/>";
            }

        // admin tags
            if ('product'==$type)
            {
                echo "<div id='admin_tags_field'>";
                echo form_label('Admin Tags - seperated by semi-colons,  a-z and 0-9 only:','admin_tags',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'admin_tags',
                    'id'=>'admin_tags',
                    'class'=>'form_field',
                    'value'=>get_value($edit_node,'admin_tags')
                );
                echo form_input($attr,null,'onkeyup="filter_admin_tags()" onfocus="show_atags()"');
                echo "<div id='atags' style='display:none;' onblur='hide_atags()'></div>";
                ?>
                    <script type='text/javascript'>
                        if (window.focus)
                        {
                            var atag_html='';
                            // all the admin tags into a js array
                                <?php
                                    foreach ($admin_tags as $a)
                                    {
                                        if (isset($edit_node['admin_tags']))
                                        {
                                            //echo "|".$a['name']."|".$edit_node['admin_tags'];
                                            if (0===strpos($edit_node['admin_tags'],trim($a['name']).";") or
                                                strpos($edit_node['admin_tags'],trim($a['name']).";")>0)
                                            {
                                                $used_class='used';
                                            }
                                            else
                                            {
                                                $used_class='unused';
                                            }
                                        }
                                        else
                                        {
                                            $used_class='unused';
                                        }
                                        ?>
                                            atag_html+="<span id='<?php echo strtolower(str_replace(array(" ","'"),"_",trim($a['name']))); ?>' class='atag_dropdown <?php echo $used_class; ?>' onclick='add_atag(\"<?php echo $a['name']; ?>\")'><?php echo $a['name']; ?></span>";
                                        <?php
                                    }
                                ?>

                            $('#atags').html(atag_html);

                            // function filters the tags based on the typing
                                function filter_admin_tags()
                                {
                                    var full=$('#admin_tags').val().split(';');
                                    var value=full[full.length-1].trim();
                                    if (value.length>0)
                                    {
                                        $('#admin_tags_field .atag_dropdown').css('display','none');

                                        value=value.replace(' ','_');
                                        value=value.replace("'",'');
                                        value=value.toLowerCase();

                                        $("#admin_tags_field [id*="+value+"]").css('display', '');
                                    }
                                    else
                                    {
                                        $('#admin_tags_field .atag_dropdown').css('display','');
                                    }
                                }

                            // add atag
                                function add_atag(atag)
                                {
                                    var all_tags=$('#admin_tags').val().split(';');

                                    for (x=0;x<all_tags.length;x++)
                                    {
                                        all_tags[x]=all_tags[x].trim();
                                    }

                                    if ($.inArray(atag,all_tags)==-1)
                                    {
                                        var val=$('#admin_tags').val().replace(' ','');

                                        // gets rid of the typing that resulted in the selection being clicked
                                            var val_split=val.split(';');
                                            val='';
                                            for (x=0;x<val_split.length-1;x++)
                                            {
                                                val+=val_split[x]+";";
                                            }

                                        var sep=";";
                                        if (";"==val.substr(val.length-1,1) ||
                                            0==val.length)
                                        {
                                            sep="";
                                        }
                                        $('#admin_tags').val(val+sep+atag+';');
                                        $('#admin_tags_field #'+atag).removeClass('unusued').addClass('used');
                                    }
                                    else
                                    {
                                        $('#admin_tags_field #'+atag).removeClass('unusued').addClass('used');
                                    }
                                }

                            // hide atags function
                                function hide_atags()
                                {
                                    $('#atags').css('display','none');
                                }

                            // show atags function
                                function show_atags()
                                {
                                    $('#atags').css('display','');
                                }
                        }
                    </script>
                <?php
                echo "</div>";
            }

        // short description
            echo form_label('Short Description:','short_desc',array('class' => 'form_label'));
            $attr=array(
                'name'=>'short_desc',
                'id'=>'short_desc',
                'class'=>'form_field',
                'value'=>get_value($edit_node,'short_desc'),
                'maxlength'=>156,
                'onkeyup'=>'char_count(\'short_desc\',156)'
            );
            echo form_input($attr);
            ?>
                <div id='short_desc_count' class='char_counter chars_ok'></div>
                    <script type='text/javascript'>
                        if (window.focus)
                        {
                            $('#short_desc_count').html(156-$('#short_desc').val().length);
                        }
                    </script>
            <?php

        // author
            if ('blog'==$type)
            {
                echo form_label('Author:','author',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'author',
                    'id'=>'author',
                    'class'=>'form_field',
                    'value'=>get_value($edit_node,'author')
                );
                echo form_input($attr);
            }

        ?>
            </div>
        </div>

        <?php

        // site specific types and fields
            if (1==method_exists($this->project_data_array_model,'admin_form_fields'))
            {
                if (strlen($this->project_data_array_model->admin_form_fields($edit_node,$type))>0)
                {
                    ?>
                    <div class='panel'>
                        <h2>
                            <span id='nodeform_specific_heading' class='ad_heading_text noselect' onclick='close_height("nodeform_specific")'><?php echo $human_type; ?> Specific Fields</span>
                            <span id='nodeform_specific_show' class='sprite panel_close noselect' onclick='close_height("nodeform_specific")'></span>
                        </h2>
                        <div id='nodeform_specific_panel' class='panel_details'>
                        <?php
                            echo $this->project_data_array_model->admin_form_fields($edit_node,$type);
                        ?>
                        </div>
                    </div>
                    <?php
                }
            }

        // calendar fields
            if ('calendar'==$type)
            {
                ?>
                <div class='panel'>
                    <h2>
                        <span id='nodeform_calendar_heading' class='ad_heading_text noselect' onclick='close_height("nodeform_calendar")'>Calendar Details</span>
                        <span id='nodeform_calendar_show' class='sprite panel_close noselect' onclick='close_height("nodeform_calendar")'></span>
                    </h2>
                    <div id='nodeform_calendar_panel' class='panel_details'>
                    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
                    <?php
                // until date
                    echo form_label('Until Date - when does this calendar run til ? :','until_date',array('class' => 'form_label'));
                    $attr=array(
                        'name'=>'until_date',
                        'id'=>'until_date',
                        'class'=>'form_field',
                        'value'=>get_value($edit_node,'until_date')
                    );
                    echo form_input($attr);
                    ?>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                $('#until_date').datepicker({dateFormat:'yy-mm-dd'});
                            }
                        </script>
                    <?php

                    echo form_label('Display Granularity - the unit of time that calendar displays in:','granularity',array('class' => 'form_label'));
                    $granularities=array('day','month','year');

                    echo "<select id='granularity_select' class='form_field' name='granularity'>";

                    // set duration selection
                        if (!isset($edit_node['granularity']))
                        {
                            $edit_node['granularity']='month';
                        }

                    // output options
                        foreach ($granularities as $g)
                        {
                            if ($edit_node['granularity']==$g ? $selected=" selected='selected' " : $selected="" );
                            echo "<option value='".$g."' ".$selected.">".$g."</option>";
                        }

                    echo "</select>";

                    // short description
                        echo form_label('CSS Link (link to the css file on the calendars server):','css_link',array('class' => 'form_label'));
                        $attr=array(
                            'name'=>'css_link',
                            'id'=>'css_link',
                            'class'=>'form_field',
                            'value'=>get_value($edit_node,'css_link')
                        );
                        echo form_input($attr);

                ?>
                    </div>
                </div>
                <?php

            }

        // category
            if (isset($categories) &&
                count($categories)>0)
            {
                ?>
                <div class='panel'>
                    <h2>
                        <span id='nodeform_category_heading' class='ad_heading_text noselect' onclick='open_height("nodeform_category_filter")'>Category</span>
                        <span id='nodeform_category_show' class='sprite panel_open noselect' onclick='open_height("nodeform_category_filter")'></span>
                    </h2>
                    <div id='nodeform_category_panel' class='panel_closed panel_details'>
                    <?php
                echo form_label('Categories:','category_id',array('class' => 'form_label'));
                ?>
                    <div class='full_width'>
                        <select id='category_select' name='category_id[]' multiple='multiple' size='7'>
                            <?php
                                $edit_node_cats=json_decode($edit_node['category_id'],true);
                                if (!is_array($edit_node_cats)) $edit_node_cats=array();
                                foreach ($categories as $c)
                                {
                                    // node cannot be actegorised as itself
                                        if ($c['id']!=$edit_node['id'])
                                        {
                                            if (in_array($c['id'],$edit_node_cats) ? $selected="selected='selected'" : $selected="" );
                                            echo "<option value='".$c['id']."' ".$selected.">".$c['name']."</option>";
                                        }
                                }
                            ?>
                        </select>
                    </div>
                <?php
                ?>
                    </div>
                </div>
                <?php
            }
            else
            {
                if (isset($edit_node['category_id']) ? $cat_id=$edit_node['category_id'] : $cat_id='["1077"]' );
                echo "<input type='hidden' name='category_id' value='".$cat_id."'/>";
            }

        // node html
                ?>
                <div class='panel'>
                    <h2>
                        <span id='nodeform_body_heading' class='ad_heading_text noselect' onclick='close_height("nodeform_body")'>Details Text</span>
                        <span id='nodeform_body_show' class='sprite panel_close noselect' onclick='close_height("nodeform_body")'></span>
                    </h2>
                    <div id='nodeform_body_panel' class='panel_details'>
                    <?php

            echo "<div id='textarea_down'>"; // tinymce is soo annoying sometimes
            // html text box (stored in indvidual tables, set in node form)
                echo form_label('Details Text:','node_html',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'node_html',
                    'id'=>'node_html',
                    'class'=>'form_field',
                    'value'=>get_value($edit_node,'node_html')
                );
                echo form_textarea($attr);
            echo "</div>";
                ?>
                    </div>
                </div>
                <?php

        // map types - load a map if either the edited node has a map or if
        // the node type is a mappable node (so this form admin page node will have map=1)
            $map_types=$this->config->item('map_types');
            if (in_array($type,$map_types))
            {
                // map user interface
                ?>
                <div class='panel'>
                    <h2>
                        <span id='nodeform_map_heading' class='ad_heading_text noselect' onclick='close_height("nodeform_map")'>Map</span>
                        <span id='nodeform_map_show' class='sprite panel_close noselect' onclick='close_height("nodeform_map")'></span>
                    </h2>
                    <div id='nodeform_map_panel' class='panel_details'>
                        <p>
                            be precise with map markings, you can zoom right in and mark the very spot where the <?php echo $type; ?> exists -
                            drag the map to get the location in the cross hairs and then when the form is saved that location will be saved with it
                            - the map pointer is accurate to within a couple of metres
                        </p>
                        <label class='map_button' for='map_activate'>click to activate</label><input id='map_activate' type='checkbox'/>
                        <input id='map_postcode' type='text' name='map_postcode' value='ne49al'/>
                        <span class='map_button' id='map_search'>search for postcode</span>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                $('#map_search').on('click',search_postcode);

                                function search_postcode()
                                {
                                    $.ajax({
                                        type: 'GET',
                                        url: '/map/lookup_postcode',
                                        dataType: 'json',
                                        data: { postcode:$('#map_postcode').val() },
                                        success: function (new_html)
                                        {
                                            map.setCenter(new google.maps.LatLng(new_html[0],new_html[1]));
                                            $('#latitude').val(lat_lng.jb);
                                            $('#longitude').val(lat_lng.kb);
                                        }
                                    });
                                }
                            }
                        </script>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                $('#map_activate').on('change',toggle_activate);

                                function toggle_activate()
                                {
                                    if (true==map.draggable)
                                    {
                                        map.setOptions({draggable: false});
                                    }
                                    else
                                    {
                                        map.setOptions({draggable: true});
                                    }
                                }
                            }
                        </script>
                        <?php
                            if (isset($edit_node['latitude']))
                            {
                                $lat=$edit_node['latitude'];
                                $long=$edit_node['longitude'];
                            }
                            else
                            {
                                $map_centre=$this->config->item('map_centre');
                                $lat=$map_centre['latitude'];
                                $long=$map_centre['longitude'];
                            }
                        ?>
                        <input id='latitude' type='hidden' name='latitude' value='<?php echo $edit_node['latitude']; ?>'/>
                        <input id='longitude' type='hidden' name='longitude' value='<?php echo $edit_node['longitude']; ?>'/>
                        <div id='map'>
                        </div>
                        <div class='cross_hair'>
                        </div>

                        <?php
                            $options='';
                            $admap_options=$this->config->item('admap_options');
                            if (is_array($admap_options))
                            {
                                foreach($admap_options as $opt=>$val)
                                {
                                    $options.=$opt.": ".$val.",";
                                }
                            }
                            $options=substr($options,0,-1);
                        ?>

                        <script type="text/javascript">
                            function initialise()
                            {
                                var mapOptions =
                                {
                                    center: new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $long; ?>),
                                    <?php echo $options; ?>
                                };

                                map = new google.maps.Map(document.getElementById("map"),mapOptions);

                                google.maps.event.addListener(map, 'bounds_changed',function()
                                    {
                                        lat_lng=map.getCenter();
                                        $('#latitude').val(lat_lng.jb);
                                        $('#longitude').val(lat_lng.kb);
                                    }
                                );
                            }

                            if (window.focus)
                            {
                                google.maps.event.addDomListener(window, 'load', initialise);
                            }

                            /*  */
                        </script>
                    <?php
                    ?>
                    </div>
                </div>
                <?php
            }
    }

    // see the form/node_edit_close for submit and close
    // the form close is here because another view opens up specific form fields for node type in between
        if ($type!='video')
        {
            // submit button
                $attr=array(
                    'name'=>'submit',
                    'id'=>$type.'_edit_submit',
                    'class'=>'submit'
                );
                echo form_submit($attr,'save '.$human_type);

            // close form
                echo form_close();

            echo "</div>";
            echo "</div>";
            echo "<div class='admin_instructions'>";

            // defaults
                if (is_numeric($edit_node['id']) ? $action='edit' : $action='create' );
                echo    "<p>This form will ".$action." the ".$human_type.".</p>";
                if ('page'==$type ? $desc="page" : $desc="page generated for this ".$human_type );

            // name
                if (!is_numeric($edit_node['id']))
                {
                    echo "<p>";
                    echo    "<strong>'name'</strong> is used to generate an URL ";
                    echo    "for the page and <strong>cannot be changed</strong> - ";
                    echo    "so be careful when entering it.";
                    echo "</p>";
                }

            // tags
                echo "<p>";
                echo    "<strong>'tags'</strong> are used to help with searching ";
                echo    "and filtering on the site, use the words that best describe ";
                echo    "the ".$human_type." and also consider using these words to ";
                echo    "group items together so that searching for the term will bring ";
                echo    "up all the tagged items.";
                echo "</p>";

            // short description
                echo "<p>";
                echo    "The <strong>'short description'</strong> field is used to define ";
                echo    "the strapline which can be used throughout the site, such as on ";
                echo    "small panels, in lists and as part of a header -  it is also used ";
                echo    "by social media websites and search engines where space is tight ";
                echo    "so think carefully about this short synopsis of your content.";
                echo "</p>";

            // specifics
                // blog
                    if ('blog'==$type)
                    {
                        echo "<p>";
                        echo    "you can use the <strong>'author'</strong> field to attribute this ";
                        echo    "blog post to a particular person. It will default to ".$this->config->item('default_blog_author')." ";
                        echo    "if you leave it blank.";
                        echo "</p>";
                    }

                // events
                    if ('event'==$type)
                    {
                        echo "<p>";
                        echo    "the <strong>'category'</strong> drop down allows you to choose ";
                        echo    "to associate this event with a group so that the event can be ";
                        echo    "listed on the page for that group. you can also leave this set to ";
                        echo    "'default category' then it will just be displayed on the calendar ";
                        echo    "and on the home page if it is coming soon.";
                        echo "</p>";
                    }

            // details text
                if ($type!='presslink')
                {
                    echo "<p>";
                    echo    "<strong>'details text'</strong> is used to write the text details ";
                    echo    "which will appear on the ".$desc." - you have basic formatting ";
                    echo    "available and using the link function (look for the chains in the ";
                    echo    "option bar) you can link from the text to other items on the site.";
                    echo "</p>";
                }

            echo "</div>";
        }
        else
        {
            echo "</div>";
            echo "<div class='admin_instructions'>";
            echo    "<p>Use the uploader to add a video to the site</p>";
            echo    "<p>No need to click to save - once the bar is full and it says 'complete' your video will be uploaded</p>";
            echo    "<p>Then you will be able to add videos to any other item on the site using the dropdown menu you will see on ";
            echo    "other create forms</p>";
            echo "</div>";
        }
?>
