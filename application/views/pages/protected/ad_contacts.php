<div class='admin_left contact_list'>
    <?php
        echo form_open('contact/save_responded'); 
    ?>
    <div class='panel'>
        <h2>
            <span id='adnode_filter_heading' class='ad_heading_text noselect' onclick='open_height("adnode_filter")'>Filters</span>
            <span id='adnode_filter_show' class='sprite panel_open noselect' onclick='open_height("adnode_filter")'></span>
        </h2>
        <div id='adnode_filter_panel' class='panel_closed panel_details'>
            <div id='name_filter'>
                <label for='filter' id='filter_name'>text filter:</label>
                <input id='filter' class='filter form_field' type='text' autofocus='autofucus'/>
            </div>
        </div>
        <script type="text/javascript">
            $('.filter').on('keyup',filter);

            function filter()
            {
                var value=$('.filter').val();

                if (value!='')
                {
                    // hide all            
                        $('.contact_list .cl').css('display','none');
                        
                    // get filter value
                        value=value.replace(/\W/g,'').toLowerCase();

                        console.log(value);
                    
                    // show those that pass the filter
                        $(".contact_list [id*="+value+"]").css('display','block');
                }
                else
                {
                    // show all if filter is empty
                        $('.contact_list .cl').css('display','block');
                }
            }
        </script>
    </div>
    <div class='panel'>
        <h2>
            <span id='unresponded_heading' class='ad_heading_text noselect' onclick='close_height("unresponded")'>Unresponded Contacts</span>
            <span id='unresponded_show' class='sprite panel_close noselect' onclick='close_height("unresponded")'></span>
        </h2>
        <div id='unresponded_panel' class='panel_details'>
            <?php
                $attr=array(
                    'name'=>'submit',
                    'class'=>'submit contact_submit'
                );
                echo form_submit($attr,'mark responded');
            ?>
        	<?php
        		foreach ($contact_list['unresponded'] as $cl)
        		{                    
                    // a jsid for filtering
                        $jsid=strtolower(preg_replace("/[^A-Za-z0-9]/",'',$cl['contact_name'].$cl['contact_email'].$cl['contact_phone'].date('My MY Fy FY Dd ld Dj lj Ds',strtotime($cl['contact_time']))));

        			?>
        			<div id='<?php echo $jsid; ?>' class='cl'>
	        			<span class='cl_heading'>
                            <span id='<?php echo $cl['contact_id']; ?>' class='responded_clicker unresponded noselect'>still to respond</span>
                            <span class='cl_time'>
                                <?php echo date('D dS M Y [H:i]',strtotime($cl['contact_time'])); ?>
                            </span>
                            <span class='cl_name'>
                                From: <strong><?php echo $cl['contact_name']; ?></strong>
                            </span>
                            <input id='contact<?php echo $cl['contact_id']; ?>' class='responded_mark' type='hidden' name='contact<?php echo $cl['contact_id']; ?>' value='<?php echo $cl['responded']; ?>'/>
                        </span>
                        <?php
    	        			if (isset($cl['contact_details']) &&
                                strlen($cl['contact_details'])>0)
                            {
                                echo "<span class='cl_contact'>".$cl['contact_details']."</span>";
                            }
                            if (isset($cl['contact_phone']) &&
                                strlen($cl['contact_phone'])>0)
                            {
                                echo "<span class='cl_contact'><a href='tel:".$cl['contact_phone']."'>".$cl['contact_phone']."</a></span>";
                            }
                            if (isset($cl['contact_email']) &&
                                strlen($cl['contact_email'])>0)
                            {
                                echo "<span class='cl_contact'><a href='mailto:".$cl['contact_email']."'>".$cl['contact_email']."</a></span>";
                            }
                        ?>
	        			<div class='cl_message'><?php echo $cl['message']; ?></div>
        			</div>
        			<?php
        		}
        	?>
            <?php
                $attr=array(
                    'name'=>'submit',
                    'class'=>'submit contact_submit'
                );
                echo form_submit($attr,'mark responded');
            ?>
        </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='responded_heading' class='ad_heading_text noselect' onclick='open_height("responded")'>Responded Contacts</span>
            <span id='responded_show' class='sprite panel_open noselect' onclick='open_height("responded")'></span>
        </h2>
        <div id='responded_panel' class='panel_details panel_closed'>
            <?php
                foreach ($contact_list['responded'] as $cl)
                {                    
                    // a jsid for filtering
                        $jsid=strtolower(preg_replace("/[^A-Za-z0-9]/",'',$cl['contact_name'].$cl['contact_email'].$cl['contact_phone'].date('My MY Fy FY Dd ld Dj lj Ds',strtotime($cl['contact_time']))));                    

                    ?>
                    <div id='<?php echo $jsid; ?>' class='cl'>
                        <span class='cl_heading'>
                            <span id='<?php echo $cl['contact_id']; ?>' class='responded_clicker responded noselect'>responded</span>
                            <span class='cl_time'>
                                <?php echo date('D dS M Y [H:i]',strtotime($cl['contact_time'])); ?>
                            </span>
                            <span class='cl_name'>
                                From: <strong><?php echo $cl['contact_name']; ?></strong>
                            </span>
                            <input id='contact<?php echo $cl['contact_id']; ?>' class='responded_mark' type='hidden' name='contact<?php echo $cl['contact_id']; ?>' value='<?php echo $cl['responded']; ?>'/>
                        </span>
                        <?php
                            if (isset($cl['contact_details']) &&
                                strlen($cl['contact_details'])>0)
                            {
                                echo "<span class='cl_contact'>".$cl['contact_details']."</span>";
                            }
                            if (isset($cl['contact_phone']) &&
                                strlen($cl['contact_phone'])>0)
                            {
                                echo "<span class='cl_contact'><a href='tel:".$cl['contact_phone']."'>".$cl['contact_phone']."</a></span>";
                            }
                            if (isset($cl['contact_email']) &&
                                strlen($cl['contact_email'])>0)
                            {
                                echo "<span class='cl_contact'><a href='mailto:".$cl['contact_email']."'>".$cl['contact_email']."</a></span>";
                            }
                        ?>
                        <div class='cl_message'><?php echo $cl['message']; ?></div>
                    </div>
                    <?php
                }
                $attr=array(
                    'name'=>'submit',
                    'class'=>'submit contact_submit'
                );
                echo form_submit($attr,'mark responded');
            ?>
        </div>
    </div>
    <?php
        echo form_close();
    ?>
</div>
<script type="text/javascript">
    $('.responded_clicker').on('click',set_hidden);

    function set_hidden()
    {
        if (0==$('#contact'+this.id).val())
        {
            $('#contact'+this.id).val(1);
            $(this).removeClass('unresponded').addClass('responded').html('responded');
        }
        else
        {
            $('#contact'+this.id).val(0);
            $(this).removeClass('responded').addClass('unresponded').html('still to respond');
        }
        
    }
</script>
<div class='admin_instructions'>
</div>