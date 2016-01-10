<div id='admin_image'>
<?php
    if (isset($form['thumbnail_open']))
    {
        ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('img#header_image').imgAreaSelect({
                        handles: true
                    });
                });
            </script>
            
            <script type="text/javascript">
                function preview(img, selection) {
                    var scaleX = 300 / selection.width;
                    var scaleY = 300 / selection.height;
                
                    $('#thumbnail + div > img').css({
                        width: Math.round(scaleX * <?php echo $img_width; ?>) + 'px',
                        height: Math.round(scaleY * <?php echo $img_height; ?>) + 'px',
                        marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
                        marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
                    });

                    // for loop here
                    $('#x1').val(selection.x1);
                    $('#y1').val(selection.y1);
                    $('#x2').val(selection.x2);
                    $('#y2').val(selection.y2);
                    $('#w').val(selection.width);
                    $('#h').val(selection.height);
                }
                
                /* $(document).ready(function () {
                    $('.image_submit').click(function() {

                    // for loop here
                        var x1 = $('#x1').val();
                        var y1 = $('#y1').val();
                        var x2 = $('#x2').val();
                        var y2 = $('#y2').val();
                        var w = $('#w').val();
                        var h = $('#h').val();
                        if(x1=="" || y1=="" || x2=="" || y2=="" || w=="" || h==""){
                            alert("You must make a selection first - click and hold the left mouse button over the image to make a square or to move the square and define your thumbnail");
                            return false;
                        }else{
                            return true;
                        }
                    });
                }); */
                
                /* $(window).load(function () {
                    <?php
                        foreach ($crops as $name=>$crop)
                        {
                            ?>
                            $('#<?php echo $name; ?>').imgAreaSelect({
                                aspectRatio: '<?php echo $crop["ratio"]; ?>',
                                show:true,
                                handles:true,

                                x1:<?php echo $crop['x1']; ?>,
                                y1:<?php echo $crop['y1']; ?>,
                                x2:<?php echo $crop['x2']; ?>,
                                y2:<?php echo $crop['y2']; ?>,
                                onSelectChange: preview
                            });
                            <?php
                        }
                    ?>
                }); */

                // default is the first one
                    <?php
                        $crop=array();
                        foreach ($crops as $name=>$crop)
                        {
                            $crop['name']=$name;
                            break;
                        }
                    ?>

                    // stores the current one for saving values between switches
                        var current='<?php echo $crop["name"]; ?>';

                    $(window).load(function () 
                    {
                        $('#<?php echo $crop["name"]; ?>').imgAreaSelect({
                            aspectRatio: '<?php echo $crop["ratio"]; ?>',
                            show:true,
                            handles:true,

                            x1:<?php echo $crop['x1']; ?>,
                            y1:<?php echo $crop['y1']; ?>,
                            x2:<?php echo $crop['x2']; ?>,
                            y2:<?php echo $crop['y2']; ?>,
                            onSelectChange: preview
                        });
                    });
            
            </script>            
            <div align='center'>
                <?php echo $form['thumbnail_open']; ?>
                <?php echo $form['image_id']; ?>
                <?php echo $form['node_id']; ?>
                    <div id='image_name'>
                        <span id='image_name_field'>image name / short description:</span>
                        <input id='iname' class='form_field' type='text' name='image_name' value='<?php echo $default_image_name; ?>' maxlength='140' onkeyup='char_count("iname",140)'/>
                        <div id='iname_count' class='char_counter chars_ok'></div>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                $('#iname_count').html(140-$('#iname').val().length);
                            }
                        </script>
                    </div>
                    <?php
                        if (isset($upload['file_name']))
                        {
                            ?>
                            <input type='hidden' name='original_image' value='<?php echo $upload['file_name']; ?>'/>
                            <?php
                        }
                    ?>
                    <span id='wrong_image'><a href='<?php echo $wrong_image; ?>'>wrong Image ? go back and choose again ... </a></span>
                    <div class='image_submit'><input class='submit image_submit' type='submit' name='upload_thumbnail' value='Save Image' id='save_thumb_top' /></div>
                    <div class='image_crop_tabs'>
                        <?php 
                            // for loop here
                                $c=0;
                                foreach ($crops as $name=>$crop)
                                {
                                    if (0==$c ? $crop_tab_class='crop_tab_selected' : $crop_tab_class='' );
                                    echo "<div id='croptab_".$name."' class='js_croptab crop_tab ".$crop_tab_class."' title='".$crop['user_message']."'>";
                                    echo "<span>".$name." [".$crop['ratio']."]</span>";
                                    echo "</div>";
                                    $c++;
                                }
                        ?>
                    </div>
                    <div id='uploaded_image'>
                        <?php 
                            // for loop here
                                $c=0;
                                foreach ($crops as $name=>$crop)
                                {
                                    if (0==$c ? $initial='show_me' : $initial='is_hidden' );
                                    echo "<div class='crop_".$name." ".$initial." crop_image'>";
                                    echo "<span>".$crop['user_message']."</span>";
                                    echo $crop_image[$name]; 
                                    echo "</div>";
                                    $c++;
                                }
                        ?>
                    </div>
                    <script type="text/javascript">
                        $('.js_croptab').on('click',switch_crop);

                        function switch_crop()
                        {
                            var name=this.id.replace('croptab_','');

                            // hide the crop images
                                $('.crop_image img')
                                    .imgAreaSelect({remove:true});
                                $('.crop_image')
                                    .removeClass('is_shown')
                                    .addClass('is_hidden');

                            // store this selection in the form
                                $('#'+current+'_x1').val($('#x1').val());
                                $('#'+current+'_y1').val($('#y1').val());
                                $('#'+current+'_x2').val($('#x2').val());
                                $('#'+current+'_y2').val($('#y2').val());
                                $('#'+current+'_w').val($('#w').val());
                                $('#'+current+'_h').val($('#h').val());

                            // then show the selected one
                                $('.crop_'+name)
                                    .removeClass('is_hidden')
                                    .addClass('is_shown')
                                    .animate({'opacity':'+=1'},100);

                            // set the selected ones values in the form
                                $('#x1').val($('#'+name+'_x1').val());
                                $('#y1').val($('#'+name+'_y1').val());
                                $('#x2').val($('#'+name+'_x2').val());
                                $('#y2').val($('#'+name+'_y2').val());
                                $('#w').val($('#'+name+'_w').val());
                                $('#h').val($('#'+name+'_h').val());

                            // set the selection area
                                $('#'+name).imgAreaSelect({
                                    aspectRatio: $('#'+name+'_ratio').val(),
                                    show:true,
                                    handles:true,

                                    x1:$('#'+name+'_x1').val(),
                                    y1:$('#'+name+'_y1').val(),
                                    x2:$('#'+name+'_x2').val(),
                                    y2:$('#'+name+'_y2').val(),
                                    onSelectChange: preview
                                });

                            // set the current value for next switch
                                current=name;

                            // set the classes on the tab
                                $('.crop_tab').removeClass('crop_tab_selected');
                                $(this).addClass('crop_tab_selected');
                        }
                    </script>

                    <!-- for loop here -->
                    <?php
                        $c=0;
                        foreach ($crops as $name=>$crop)
                        {
                            if (0==$c)
                            {
                                ?>
                                <input type='hidden' name='x1' value='<?php echo $crop['x1']; ?>' id='x1' />
                                <input type='hidden' name='y1' value='<?php echo $crop['y1']; ?>' id='y1' />
                                <input type='hidden' name='x2' value='<?php echo $crop['x2']; ?>' id='x2' />
                                <input type='hidden' name='y2' value='<?php echo $crop['y2']; ?>' id='y2' />
                                <input type='hidden' name='w' value='<?php echo $crop['w']; ?>' id='w' />
                                <input type='hidden' name='h' value='<?php echo $crop['h']; ?>' id='h' />
                                <?php
                            }
                            ?>
                            <input type='hidden' name='<?php echo $name; ?>_x1' value='<?php echo $crop['x1']; ?>' id='<?php echo $name; ?>_x1' />
                            <input type='hidden' name='<?php echo $name; ?>_y1' value='<?php echo $crop['y1']; ?>' id='<?php echo $name; ?>_y1' />
                            <input type='hidden' name='<?php echo $name; ?>_x2' value='<?php echo $crop['x2']; ?>' id='<?php echo $name; ?>_x2' />
                            <input type='hidden' name='<?php echo $name; ?>_y2' value='<?php echo $crop['y2']; ?>' id='<?php echo $name; ?>_y2' />
                            <input type='hidden' name='<?php echo $name; ?>_w' value='<?php echo $crop['w']; ?>' id='<?php echo $name; ?>_w' />
                            <input type='hidden' name='<?php echo $name; ?>_h' value='<?php echo $crop['h']; ?>' id='<?php echo $name; ?>_h' />
                            <input type='hidden' name='<?php echo $name; ?>_ratio' value='<?php echo $crop['ratio']; ?>' id='<?php echo $name; ?>_ratio' />
                            <?php
                            $c++;
                        }
                    ?>
                    <div class='image_submit'><input class='submit image_submit' type='submit' name='upload_thumbnail' value='Save Image' id='save_thumb_bottom' /></div>
                </form>
            </div>
        <?php
    }
    else
    {
        $this->session->set_userdata('image_admin_reload_source','node_images');
        if (1==$owns_node)
        {
            ?>
            <div class='panel'>
                <h2>
                    <span id='image_upload_heading' class='ad_heading_text noselect' onclick='close_height("image_upload")'>Upload Image</span>
                    <span id='image_upload_show' class='sprite panel_close noselect' onclick='close_height("image_upload")'></span>
                </h2>
                <div id='image_upload_panel' class='panel_details'>
                    <!-- this is the image upload form -->
                    <?php echo $form['upload_open']; ?>
                        <input class='aim_fileup' type='file' name='userfile' size='30' />
                        <input class='submit' type='submit' name='upload' value='upload' />
                    </form>
                </div>
            </div>
            <?php
        }
        ?>
        <div class='panel'>
            <h2>
                <span id='current_images_heading' class='ad_heading_text noselect' onclick='close_height("current_images")'>Images Associated With This Node</span>
                <span id='current_images_show' class='sprite panel_close noselect' onclick='close_height("current_images")'></span>
            </h2>
            <div id='current_images_panel' class='panel_details image_panel_list'>
                <!-- this is the main and remove form, it includes all the images as panels -->
                <?php echo $form['set_open']; ?>
                    <input type='hidden' name='save_main'/> 
                    <?php echo $image_panels; ?>
                    <div class='saves_mains_and_delete'><input class='submit' type='submit' name='submit' value='save all'/></div>
                </form>
            </div>
        </div>
        <?php    
    }
?>
</div>