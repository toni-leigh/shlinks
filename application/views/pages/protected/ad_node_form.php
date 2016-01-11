<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
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
        // output the were you looking for message:
            if (isset($edit_node['node_list']))
            {
                $edit_node_list=json_decode($edit_node['node_list'],true);

                if (count($edit_node_list)>0)
                {
                    echo "<div class='looking_for'>";
                    echo "were you looking for your:<br/>";

                    $links='';
                    foreach ($edit_node_list as $enl)
                    {
                        switch ($enl)
                        {
                            case 'blog':
                                $links.="<a href='/".$enl."/list'>blog posts</a><br/>";
                                break;
                            case 'event':
                                $links.="<a href='/".$enl."/list'>events list</a><br/>";
                                break;
                            case 'calendar':
                                $links.="<a href='/".$enl."/list'>calendars</a><br/>";
                        }
                    }
                    echo $links;
                    echo "</div>";
                }
            }

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
                    'class'=>'form_field js-store',
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
                    'class'=>'form_field js-store',
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
                    'class'=>'form_field js-store',
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
                'class'=>'form_field js-store',
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

        // video src
            echo form_label('Video Source (youtube, just the 11 character code from the URL "v=<span style="color:#ff7c00;">{CODE}</span>"):','video_src',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'video_src',
                'id'=>'video_src',
                'class'=>'form_field js-store',
                'value'=>get_value($edit_node,'video_src')
            );
            echo form_input($attr);

        // author
            if ('blog'==$type)
            {
                echo form_label('Author:','author',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'author',
                    'id'=>'author',
                    'class'=>'form_field js-store',
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
                        'class'=>'form_field js-store',
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

                    echo "<select id='granularity_select' class='form_field js-store' name='granularity'>";

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
                            'class'=>'form_field js-store',
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
                                /*'id'=>'node_html',*/
                                'class'=>'form_field js-store',
                                'value'=>get_value($edit_node,'node_html')
                            );
                            echo form_textarea($attr);
                        echo "</div>";
                    ?>
                    </div>
                </div>
                <?php

        // lists
                ?>
                <div class='panel'>
                    <h2>
                        <span id='nodeform_list_heading' class='ad_heading_text noselect' onclick='open_height("nodeform_list")'>Lists</span>
                        <span id='nodeform_list_show' class='sprite panel_open noselect' onclick='open_height("nodeform_list")'></span>
                    </h2>
                    <div id='nodeform_list_panel' class='panel_closed panel_details'>
                        <?php
                        echo form_label('Lists:','node_list',array('class' => 'form_label'));
                        ?>
                            <div class='full_width'>
                                <select id='node_list_select' class='js-store' name='node_list[]' multiple='multiple' size='8'>
                                    <?php
                                        $edit_node_list=json_decode($edit_node['node_list'],true);
                                        if (!is_array($edit_node_list)) $edit_node_list=array();
                                        foreach ($node_types as $t)
                                        {
                                            if (in_array($t['type'],$edit_node_list) ? $selected="selected='selected'" : $selected="" );
                                            echo "<option value='".$t['type']."' ".$selected.">".$t['type']."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php
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
                        <p>
                            if you want to drag the map to a location then click to activate first. if you use the location search then the map point
                            will be saved regardless of whether the drag is activated
                        </p>
                        <div class='map_control_row'>
                            <label class='map_button' for='map_activate'>click to activate drag</label><input id='map_activate' type='checkbox'/>
                        </div>
                        <div class='map_control_row'>
                            <input id='map_search_field' class='form_field js-store' type='text' name='map_search' value='' placeholder='postcode or location name'/>
                            <span class='map_button' id='map_search' title='postcode or location name'>search map</span>
                        </div>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                function search_postcode()
                                {
                                    $.ajax({
                                        type: 'GET',
                                        url: '/map/lookup_postcode',
                                        dataType: 'json',
                                        data: { postcode:$('#map_search_field').val() },
                                        success: function (new_html)
                                        {
                                            map.setCenter(new google.maps.LatLng(new_html[0],new_html[1]));
                                            lat_lng=map.getCenter();
                                            $('#latitude').val(lat_lng.lat());
                                            $('#longitude').val(lat_lng.lng());
                                            $('.on_map').css('display','none');
                                        }
                                    });
                                }

                                function catch_enter(e)
                                {
                                    if (13 === e.which)
                                    {
                                        $('#map_search').trigger('click');

                                        return false;
                                    }
                                }

                                $('#map_search').on('click',search_postcode);

                                $('#map_search_field').on('keydown',catch_enter);
                            }
                        </script>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
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

                                $('#map_activate').on('change',toggle_activate);
                            }
                        </script>
                        <?php
                            if (isset($edit_node['latitude']) &&
                                $edit_node['latitude']!=999)
                            {
                                $lat=$edit_node['latitude'];
                                $long=$edit_node['longitude'];
                                $form_lat=$edit_node['latitude'];
                                $form_long=$edit_node['longitude'];
                                $not_on_map_class='is_not_visible';
                            }
                            else
                            {
                                $map_centre=$this->config->item('map_centre');
                                $lat=$map_centre['latitude'];
                                $long=$map_centre['longitude'];
                                $form_lat=999;
                                $form_long=999;
                                $not_on_map_class='is_visible';
                            }
                        ?>
                        <div class='map_control_row'>
                            <span class='latlng_label'>latitude</span><input id='latitude' class='form_field js-store' type='text' name='latitude' value='<?php echo $form_lat; ?>'/><span class='on_map <?php echo $not_on_map_class; ?>'>not on map!</span>
                        </div>
                        <div class='map_control_row'>
                            <span class='latlng_label'>longitude</span><input id='longitude' class='form_field js-store' type='text' name='longitude' value='<?php echo $form_long; ?>'/><span class='on_map <?php echo $not_on_map_class; ?>'>not on map!</span>
                            <span class='map_remove map_button'>remove from map</span>
                        </div>
                        <div id='map' class='admin_map'>
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
                            $('.map_remove').on('click',function() {
                                $('#latitude').val(999);
                                $('#longitude').val(999);
                                $('.on_map').css('display','block');
                            });
                            function initialise()
                            {
                                var mapOptions =
                                {
                                    center: new google.maps.LatLng(<?php echo $lat; ?>, <?php echo $long; ?>),
                                    <?php echo $options; ?>
                                };

                                map = new google.maps.Map(document.getElementById("map"),mapOptions);
                                google.maps.event.addListener(map, "dragstart",function(event)
                                {
                                    google.maps.event.addListener(map, "idle", function(event2)
                                    {
                                        lat_lng=map.getCenter();
                                        $('#latitude').val(lat_lng.lat());
                                        $('#longitude').val(lat_lng.lng());
                                        $('.on_map').css('display','none');
                                    });
                                });
                            }

                            if (window.focus)
                            {
                                google.maps.event.addDomListener(window, 'load', initialise);
                            }

                            /*  */
                        </script>
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
                    'class'=>'submit js-submit-button'
                );
                echo form_submit($attr,'save '.$human_type);

            // close form
                echo form_close();

            echo "</div>";
            echo "</div>";
            echo "<div class='admin_instructions'>";

        // local storage differences message
            echo "<p id='local_storage_message'>we saved some data from your last visit ";
            echo "that you didn't save to the database, click here if you would like to reload ";
            echo "this data (you can also just ignore this message and edit)";
            echo "</p>";

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

    <script type="text/javascript">
        ;(function() {

            'use strict';

            var t;

            // the collection of inputs to focus on
            var targets;

            // arbitrary key to identify this form, must be unique sitewide
            var form_id;

            // the realod button
            var reload_button;

            // map if present
            var map;

            // the values saved on load form to compare to
            // and the current values that represent the state of the form right now
            var values={
                on_load_vals:{},
                saved_vals:{}
            };

            // the current input
            var input;

            function SaveForm(params) {

                t = this;

                targets=params.input_collection;
                form_id=params.form_id;
                reload_button=params.reload_button;
                map=params.map;

                // initialise
                t._set_ids();
                t._retrieve_saved();
                t._store_form('on_load_vals');
                t._bind_events();

                // check for differences between the database load and the stored values
                t._compare();
            }

            SaveForm.prototype = {

                /* sets ids on any elements that don't have them */
                _set_ids: function()
                {
                    var id_counter=0;

                    targets.each(
                        function()
                        {
                            var input=$(this);
                            var id=input.attr('id');

                            if ('undefined' === typeof id)
                            {
                                input.attr('id','form_save'+id_counter);
                                id_counter++;
                            }
                        }
                    );
                },

                /* retrieves the saved values from local storage */
                _retrieve_saved: function()
                {
                    var saved=$.parseJSON(localStorage.getItem(form_id));

                    if (saved !== null)
                    {
                        if (typeof saved.saved_vals !== 'undefined')
                        {
                            values.saved_vals=saved.saved_vals;
                        }
                    }
                },

                /* stores the current contents of the form */
                _store_form: function(target)
                {
                    targets.each(
                        function()
                        {
                            var input=$(this);

                            values[target][input.attr('id')]=input.val();

                            console.log(input.val());
                        }
                    );

                    t._save_to_local();
                },

                /* binds the required events to the inputs etc. */
                _bind_events: function()
                {
                    targets.each(
                        function()
                        {
                            var input=$(this);

                            input.on('blur',t._save_value);
                        }
                    );

                    reload_button.on('click',t._load_saved);
                },

                /* saves the value from an input */
                _save_value: function()
                {
                    input=$(this);

                    values.saved_vals[input.attr('id')]=input.val();

                    t._save_to_local();
                },

                /* specifically saves a tinymce value */
                _save_tinymce_value: function(tiny_mce)
                {
                    values.saved_vals[tiny_mce.id]=tiny_mce.getContent();

                    t._save_to_local();
                },

                /* saves the values array to local storage */
                _save_to_local: function()
                {
                    localStorage.setItem(form_id,JSON.stringify(values));
                },

                /* loads the saved data on response to the users decision to */
                _load_saved: function()
                {
                    t._retrieve_saved();

                    // get the contents of the targets
                    targets.each(
                        function()
                        {
                            var input=$(this);

                            var id=input.attr('id');

                            var saved_val=values.saved_vals[id];

                            if (typeof saved_val !== 'undefined')
                            {
                                if (typeof tinyMCE.get(id) !== 'undefined')
                                {
                                    tinyMCE.get(id).setContent(saved_val);
                                }
                                else
                                {
                                    input.val(saved_val);
                                }
                            }
                        }
                    );

                    // centre the map if it is present
                    var lat=targets.find('#latitude');
                    var lng=targets.find('#longitude');
                    if (lat.length>0 &&
                        lng.length>0)
                    {
                        var lat_val=lat.val();
                        var lng_val=lng.val();

                        if (lat_val!="999" &&
                            lng_val!="999" &&
                            typeof map !== 'undefined')
                        {
                            map.setCenter(new google.maps.LatLng(lat_val,lng_val));
                        }
                    }

                    reload_button.animate({'opacity':'-=1'},500,function(){ reload_button.css({'height':'0px'}) });
                },

                /* compares the values on load to the saved values in order to decide whether to show the message */
                _compare: function()
                {
                    targets.each(
                        function()
                        {
                            var input=$(this);

                            var id=input.attr('id');

                            if ('undefined' != typeof values.saved_vals[id])
                            {
                                if (values.on_load_vals[id]!=values.saved_vals[id])
                                {
                                    reload_button.css({'display':'block'});
                                }
                            }
                        }
                    );
                }

            };

            window.SaveForm = SaveForm;

        }());
        <?php
            if (is_numeric($edit_node['id']))
            {
                echo "var form_id=".$edit_node['id'].";";
            }
            else
            {
                echo "var form_id='create_".$type."';";
            }
        ?>
        var saveForm = new window.SaveForm(
            {
                input_collection:$('.js-store'),
                form_id:form_id,
                reload_button:$('#local_storage_message'),
                map:map
            }
        );
    </script>

    <?php
?>
