<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<div id='content_left'>
<?php
    if (count($basket)>0)
    {
        echo form_open('basket/save_basket');
        foreach($basket as $b)
        {
            // voucher and postage rows are handled differently, the else bit is for row output where the
            // voucher is output too, based on what is set here
                if ('voucher'==$b['id'])
                {
                    $show_voucher=1;
                    $voucher_name=$b['options']['voucher_string'];
                }
                elseif ('postage'==$b['id'])
                {
                    ?>
                    <div class='birow bipostrow'>
                        <span class='birow_details bipostrow_details'>Postage:</span>
                            <select id='pclass' name='pclass' onchange='update_basket_postage()'>
                                <?php foreach ($pclasses as $pclass): ?>
                                    <?php if ($pclass['pclass_name']==$b['class'] ? $selected=" selected='selected' " : $selected='' ); ?>
                                    <option value='<?php echo $pclass['pclass_name']; ?>' <?php echo $selected; ?>><?php echo str_replace('_',' ',$pclass['pclass_heading']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <span class='birow_subtotal bipostrow_subtotal'>&pound;<span id='postage_total'><?php echo number_format($b['subtotal'],2); ?></span></span>
                        <?php $postage_total=$b['subtotal']; ?>
                        <input type='hidden' id='current_postage' value='<?php echo $postage_total; ?>'/>
                        <script type='text/javascript'>
                            if (window.focus)
                            {
                                // function handles updating postage values on the front end
                                // the call also actually updates the value in the system
                                    function update_basket_postage()
                                    {
                                        var pclass_name=$('#pclass').val();
                                        var curr_postage=$('#current_postage').val();
                                        $.ajax({
                                            type: 'GET',
                                            url: '/basket/update_postage',
                                            dataType: 'json',
                                            data: { pclass_name:pclass_name , curr_postage:curr_postage },
                                            success: function (new_html)
                                            {
                                                $('#post_grand_total').html("&pound;"+new_html[0]);
                                                $('.paypal_postage').val(new_html[0]);
                                                $('#header_postage').html(new_html[0]);
                                                $('#current_postage').val(new_html[0]);
                                                $('#postage_total').html(new_html[0]);

                                                $('#grand_total').html(new_html[1]);
                                                $('#header_total').html(new_html[1]);

                                                $('.voucher_row').html("&pound;&nbsp;-&nbsp;"+new_html[2]);
                                                $('.paypal_voucher').val(new_html[2]);
                                                $('#voucher_grand_total').html(new_html[2]);

                                                $('#nv_grand_total').html(new_html[3]);

                                                refresh_message("<div class='message success'>the postage has been updated - if it hasn't changed then there was no difference in cost</div>");
                                            }
                                        });
                                    }
                            }
                        </script>
                    </div>
                    <?php
                }
                else
                {
                    // voucher specific processing
                        if (1==$show_voucher)
                        {
                            $vrow_class=" bivouchrow ";
                            $minus_prefix=" - ";
                        }
                        else
                        {
                            $vrow_class='';
                            $minus_prefix="";
                        }

                    // now output the row itself, the voucher row too
                        ?>
                        <div class='birow <?php echo $vrow_class; ?>'>
                            <span class='birow_details'>
                                <span class='birow_qty'>
                                    <input id='<?php echo $b['rowid']; ?>' class='basket_quantity form_field rounded' name='<?php echo $b['rowid']; ?>' type='text' value='<?php echo $b['qty']; ?>' onkeyup='check_numeric(\"#<?php echo $b['rowid']; ?>\")'/>
                                    <span class='birow_qtyx'>X</span>
                                </span>
                                <span class='birow_name'><?php echo $b['name']; ?></span>
                                <span class='birow_price'>@ <?php echo format_price($b['price']); ?> each.</span>
                                <span class='birow_subtotal'><?php echo format_price($b['subtotal'],2); ?></span>
                                <span class='birow_nvar'>
                                <?php
                                    foreach ($b['options'] as $k=>$v)
                                    {
                                        if (!in_array($k,array('calendar_id','event_id','exclusive','voucher_id','voucher_type')))
                                        {
                                            if ('booked_date'==$k)
                                            {
                                                $v=substr($v,6,2).'-'.substr($v,4,2).'-'.substr($v,0,4);
                                            }
                                            if ('booked_time'==$k)
                                            {
                                                if ('d'==$v ? $v='all day' : $v=substr($v,0,2).':'.substr($v,2,2) );
                                            }
                                            echo "<span class='bi_nvar bi_".$k."'>".str_replace('_',' ',$k)." ".str_replace('_',' ',$v)."; </span>";
                                        }
                                    }
                                ?>
                                </span>
                        </div>
                        <?php
                }
        }

        // submit button
            $attr=array(
                'name'=>'submit',
                'id'=>'update_basket_submit',
                'class'=>'submit'
            );
            echo form_submit($attr,'update basket');

        echo form_close();
    }
    else
    {
        echo "basket is empty";
    }
?>
</div>
<div id='content_right'>
    <span class='left reassurance_total'>basket totals:</span>
    <div id='prod_total_row' class='total_row'>
        <span id='prod_total_heading' class='gt_heading'>Products:</span>
        <span id='pg_total_val' class='gt_val'>
            <div id='prod_grand_total'>
                <?php echo format_price($total['product']); ?>
            </div>
        </span>
    </div>

    <div id='post_total_row' class='total_row'>
        <span id='pos_total_heading' class='gt_heading'>Postage:</span>
        <span id='pg_total_val' class='gt_val'>
            <div id='post_grand_total'>
                <?php echo format_price($total['postage']); ?>
            </div>
        </span>
    </div>

    <div id='nv_total_row' class='total_row'>
        <span id='total_heading' class='gt_heading'>Total:</span>
        <span id='nv_total_val' class='gt_val'>
            <span style='width:7px;float:left;'>&pound;</span>
            <div id='nv_grand_total'>
                <?php echo number_format($total['total_nv'],2); ?>
            </div>
        </span>
    </div>

    <div id='voucher_total_row' class='total_row'>
        <span id='voucher_total_heading' class='gt_heading'>Voucher:</span>
            <span style='float:left;'>&pound;&nbsp;-&nbsp;</span>
        <span id='v_total_val' class='gt_val'>
            <div id='voucher_grand_total'>
                <?php echo number_format($total['voucher'],2); ?>
            </div>
        </span>
    </div>

    <div id='total_row' class='total_row'>
        <span id='total_heading' class='gt_heading'>You Pay:</span>
        <span id='pg_total_val' class='gt_val'>
            <span style='width:7px;float:left;'>&pound;</span>
            <div id='grand_total'>
                <?php echo number_format($total['total'],2); ?>
            </div>
        </span>
    </div>

    <span id='voucher_header' class='left reassurance_total'>enter voucher code:</span>
    <span id='voucher_message' class='left'>
            <?php if (1==$show_voucher): ?>
                <?php echo $voucher_name; ?>
            <?php else: ?>
                no voucher applied yet
            <?php endif; ?>
    </span>

    <div id='voucher_apply'>
        <?php echo form_open('/voucher/check_voucher'); ?>
            <input id='voucher_id' class='form_field' type='text' name='voucher_id' maxlength='10'/>
            <input id='voucher_submit' class='submit' type='submit' name='submit' value='redeem'/>
        </form>
    </div>
    <script type='text/javascript'>
        if (window.focus)
        {
            var voucher_apply="<input id='voucher_field' class='form_field' type='text' name='voucher_field' maxlength='10'/>";
            voucher_apply+="<span id='voucher_submit' class='submit' onclick='check_voucher()'>redeem</span>";
            $('#voucher_apply').html(voucher_apply);

            function check_voucher()
            {
                voucher_id=$('#voucher_field').val();
                $.ajax({
                    type: 'GET',
                    url: '/voucher/check_voucher',
                    dataType: 'json',
                    data: { voucher_id:voucher_id },
                    success: function (new_html)
                    {
                        $('#voucher_message').html(new_html[0]);
                        // on basket
                        $('#postage_total').html(new_html[2]);

                        $('.voucher_row').html("&pound;&nbsp;-&nbsp;"+new_html[3]);
                        $('#voucher_grand_total').html(new_html[3]);

                        // grand totals
                            $('#grand_total').html(new_html[1]);
                            $('#post_grand_total').html('&pound;'+new_html[2]);

                        // header
                            $('#header_total').html(new_html[1]);
                            $('#header_postage').html('&pound;'+new_html[2]);
                    }
                });
            }
        }
    </script>

    <span class='left reassurance_total'>click to pay:</span>
    <div id='reassurance_checkouts'>
        <?php if (strlen($this->config->item('payment_processor'))>0): ?>
            <a id='address_checkout' class='checkout' href='/order-address'>checkout</a>
        <?php endif; ?>
        <?php echo $paypal_form; ?>
    </div>
</div>
