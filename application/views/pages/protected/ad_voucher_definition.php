<div class='admin_left'>
    <div class='panel'>
        <h2>
            <span id='new_vouchers_heading' class='ad_heading_text noselect' onclick='close_height("new_vouchers")'>Create a New Voucher Code</span>
            <span id='new_vouchers_show' class='sprite panel_close noselect' onclick='close_height("new_vouchers")'></span>
        </h2>
        <div id='new_vouchers_panel' class='panel_details'>
            <?php
                echo form_open("/voucher/add_new_voucher");
            ?>
                <div class='voucher_create_row noselect'>
                    <label for='voucher_id' class='voucher_field_label'>voucher code:</label>
                    <input id='voucher_id' class='form_field' type='text' name='voucher_id' maxlength='10' onblur='check_code()'/>
                    <span id='voucher_generate' onclick='generate()'>&lt;--&nbsp;automatically generate code</span>
                    <script type='text/javascript'>
                        function generate()
                        {
                            var code = "";
                            var possible = "bcdefhkmnprtwxyz236789";
                        
                            for( var i=0; i < 6; i++ )
                                code += possible.charAt(Math.floor(Math.random() * possible.length));
                                
                            $('#voucher_id').val(code);
                        }
                        function check_code()
                        {
                            $.ajax({
                                type: 'GET',
                                url: '/voucher/check_code',
                                dataType: 'json',
                                data: { code:$('#voucher_id').val() },
                                success: function (new_html) {  }
                            });
                        }
                    </script>
                </div>
                    
                <div class='voucher_create_row'>
                    <label for='vtid' class='voucher_field_label'>voucher type:</label>
                    <select id='vtid' class='form_field' name='voucher_type_id'>
                    <?php
                        foreach($voucher_types as $vt)
                        {
                            ?>
                            <option value='<?php echo $vt['voucher_type_id']; ?>'>
                            <?php
                                if (strlen($vt['voucher_type_name'])>0 ? $name=$vt['voucher_type_name'] : $name='un-named' );
                                echo $name." - [".$vt['details']."]";
                            ?>
                            </option>
                            <?php
                        }
                    ?>
                    </select>
                </div>
                    
                <div class='voucher_create_row'>
                    <label for='expires' class='voucher_field_label'>expires:</label>
                    <input id='expires' class='form_field' type='text' name='expires' value=''/>
                    <script>
                        $(document).ready(function() {
                            $("#expires").datepicker({dateFormat: 'dd-mm-yy'});
                        });
                    </script>
                </div>
                
                <div class='voucher_create_row'>
                    <span class='voucher_field_label'>multiple use ?</span>
                    <input id='multiple_use' type='checkbox' name='multiple_use'/><label for='multiple_use' class='voucher_radio_text'>check for yes, this is a multiple use voucher</label>
                </div>
                    
                <input id='add_voucher_submit' class='submit' type='submit' name='submit' value='add new voucher'/>
            </form>
        </div>
    </div>
    
    <div class='panel'>
        <h2>
            <span id='new_voucher_heading' class='ad_heading_text noselect' onclick='open_height("new_voucher")'>Create a New Voucher Type</span>
            <span id='new_voucher_show' class='sprite panel_open noselect' onclick='open_height("new_voucher")'></span>
        </h2>
        <div id='new_voucher_panel' class='panel_closed panel_details'>
            <?php
                echo form_open("/voucher/add_new_voucher_type");
            ?>
                <div class='voucher_create_row'>
                    <label for='vtn' class='voucher_field_label'>name:</label>
                    <input id='vtn' class='form_field' type='text' name='voucher_type_name'/>
                </div>
                
                <div class='voucher_create_row'>
                    <label for='av' class='voucher_field_label'>adjust value:</label>
                    <input id='av' class='form_field' type='text' name='adjust_value'/>
                </div>
                    
                <div class='voucher_create_row'>
                    <span class='voucher_field_label'>adjust type:</span>
                    <input id='voucher_pound' type='radio' name='adjust_type' value='pound' checked='checked'/><label for='voucher_pound' class='voucher_radio_text'>&pound;</label>
                    <input id='voucher_perc' type='radio' name='adjust_type' value='percentage'/><label for='voucher_perc' class='voucher_radio_text'>%</label>
                </div>
                
                <div class='voucher_create_row'>
                    <span class='voucher_field_label'>adjust focus:</span>
                    <input id='voucher_post' type='radio' name='adjust_focus' value='postage'/><label for='voucher_post' class='voucher_radio_text'>postage</label>
                    <input id='voucher_total' type='radio' name='adjust_focus' value='total' checked='checked'/><label for='voucher_total' class='voucher_radio_text'>total</label>
                </div>
                
                <div class='voucher_create_row'>
                    <label for='tv' class='voucher_field_label'>threshold:</label>
                    <input id='tv' class='form_field' type='text' name='threshold'/>
                </div>
                
                <input id='add_voucher_type_submit' class='submit' type='submit' name='submit' value='add new voucher type'/>
            </form>
        </div>
    </div>
    
    <div class='panel'>
        <h2>
            <span id='curr_vouchers_heading' class='ad_heading_text noselect' onclick='close_height("curr_vouchers")'>Current Voucher Codes</span>
            <span id='curr_vouchers_show' class='sprite panel_close noselect' onclick='close_height("curr_vouchers")'></span>
        </h2>
        <div id='curr_vouchers_panel' class='panel_details'>
            <?php
                foreach ($vouchers as $v)
                {
                    // simplify voucher details
                        if (1==$v['spent'] or date('ymd',strtotime($v['expires']))<date('ymd',time()))
                        {
                            $row_colour=' light_grey ';
                        }
                        else
                        {
                            $row_colour=' light_blue ';
                        }
                        $details=$voucher_types_by_id[$v['voucher_type_id']]['details'];
                        $code=$v['voucher_id'];
                        $expires=date('jS M Y',strtotime($v['expires']));
                        if (1==$v['single_shot'] ? $single='single' : $single='&nbsp;' );
                        
                    // output row
                        ?>
                        <div class='voucher_row <?php echo $row_colour; ?>'>
                            <span class='vr_code'><?php echo $code; ?></span>
                            <span class='vr_type'><?php echo $details; ?></span>
                            <span class='vr_single'><?php echo $single; ?></span>
                            <span class='vr_expires'><?php echo $expires; ?></span>
                        </div>
                        <?php
                }
            ?>
        </div>
    </div>
</div>
<div class='admin_instructions'>
    <p><strong>to create a new voucher code</strong></p>
    <p>use the top panel</p>
    <p>
        1. first make sure you have created a voucher type, see the bottom panel
    </p>
    <p>
        2. <strong>'voucher codes'</strong> are up to ten characters long and can include numbers or letters.
    </p>            
    <p>
        3. you can also set an <strong>'expires'</strong> date on a voucher here - the voucher will then be
        valid until the very end of that day. NB - leaving this field blank will default to one month from
        creation.
    </p>
    <p>
        4. use the <strong>'multiple use'</strong> checkbox to set a voucher as one that can be used repeatedly
        (e.g. mailshot / facebook promotion) or a one off use only (e.g. a competition winner).
    </p>
    
    <p><strong>to create a new type of voucher</strong></p>
    <p>use the middle panel<p>
    <p>
        1. choose whether the voucher will effect the cost by reducing its total number of pounds or by
        reducing the amount by a percentage.
    </p>
    <p>
        2. choose the numerical value which defines how much effect the voucher will have on the amount - i.e.
        a values of '50' will reduce the amount by &pound;50 or by 50% depending on your first choice.
    </p>
    <p>
        3. choose which amount the voucher focuses on, either the order total or the postage cost of the order.
    </p>
    <p>
        4. finally set a threshold - this allows you to create a voucher type that only applies when an order
        total is over a certain amount (always a value in pounds and pence).
    </p>
    <p>
        5. optionally you can give your voucher a name for ease of admin reference.
    </p>
</div>
        