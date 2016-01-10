<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>

<!-- clear the local storage that might be set by the events processing -->
<script type='text/javascript'>
    if (window.focus)
    {
        if (typeof(Storage)!=='undefined')
        {
            localStorage.clear_basket=1;
        }
    }
</script>

<div id='content_left'>
<?php
	// get the order from the session
		$order_items=$this->session->userdata('order_items');

	// do we have a successful order, i.e. is this a redirect from checkout or just someone loading order success page ?
		$order_found=0;
		if (is_array($order_items) &&
			count($order_items)>0)
		{
			$order_found=1;
		}

	// output the successful order rows
		if (1==$order_found)
		{
			// loop over the rows
		        foreach($order_items as $oi)
		        {
		        	// some extra processing for the voucher if there is one
			            if ('voucher'==$oi['id'])
			            {
			                $show_voucher=1;
			                $voucher_name=$oi['options']['voucher_string'];
			            }

			            if (isset($show_voucher) &&
			            	1==$show_voucher)
			            {
			                $vrow_class=" oivouchrow ";
			                $minus_prefix=" - ";
			            }
			            else
			            {
			                $vrow_class='';
			                $minus_prefix="";
			            }

			        // output the row html
			            ?>
			            <div class='oirow <?php echo $vrow_class; ?>'>
			                <span class='oirow_details'>
			                	<span class='oirow_qty'>
			                		<span class='oirow_qtyval'><?php echo $oi['qty']; ?></span>
			                		<span class='oirow_qtyx'>X</span>
		                		</span>
		                		<span class='oirow_name'><?php echo $oi['name']; ?></span>
		                		<span class='oirow_price'>@ <?php echo format_price($oi['price']); ?> each.</span>
		                		<span class='oirow_subtotal'><?php echo format_price($oi['subtotal'],2); ?></span>
			                	<span class='oirow_nvar'>
			                	<?php
			                		// output the row variation deatils
						                if (isset($oi['options']))
						                {
						                    foreach ($oi['options'] as $k=>$v)
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
						                            echo "<span class='oirow_nvarvalue oirow_".$k."'>".str_replace('_',' ',$k)." ".str_replace('_',' ',$v)."; </span>";
						                        }
						                    }
						                }
						        ?>
			                    </span>
		                    </span>
			            </div>
			            <?php
		        }
		}
?>
</div>
<div id='content_right'>
<?php
	// output the order totals for the basket
		if (1==$order_found)
		{
			// get our order totals
				$order_totals=$this->session->userdata('order_totals');

			// output
				?>
			    <span class='left reassurance_total'>basket totals:</span>
			    <div id='prod_total_row' class='total_row'>
			        <span id='prod_total_heading' class='gt_heading'>Products:</span>
			        <span id='pg_total_val' class='gt_val'>
			            <div id='prod_grand_total'>
			                <?php echo format_price($order_totals['product']); ?>
			            </div>
			        </span>
			    </div>

			    <div id='post_total_row' class='total_row'>
			        <span id='pos_total_heading' class='gt_heading'>Postage:</span>
			        <span id='pg_total_val' class='gt_val'>
			            <div id='post_grand_total'>
			                <?php echo format_price($order_totals['postage']); ?>
			            </div>
			        </span>
			    </div>

			    <div id='nv_total_row' class='total_row'>
			        <span id='total_heading' class='gt_heading'>Total:</span>
			        <span id='nv_total_val' class='gt_val'>
			            <span style='width:7px;float:left;'>&pound;</span>
			            <div id='nv_grand_total'>
			                <?php echo number_format($order_totals['total_nv'],2); ?>
			            </div>
			        </span>
			    </div>

			    <div id='voucher_total_row' class='total_row'>
			        <span id='voucher_total_heading' class='gt_heading'>Voucher:</span>
			            <span style='float:left;'>&pound;&nbsp;-&nbsp;</span>
			        <span id='v_total_val' class='gt_val'>
			            <div id='voucher_grand_total'>
			                <?php echo number_format($order_totals['voucher'],2); ?>
			            </div>
			        </span>
			    </div>

			    <div id='total_row' class='total_row'>
			        <span id='total_heading' class='gt_heading'>You Paid:</span>
			        <span id='pg_total_val' class='gt_val'>
			            <span style='width:7px;float:left;'>&pound;</span>
			            <div id='grand_total'>
			                <?php echo number_format($order_totals['total'],2); ?>
			            </div>
			        </span>
			    </div>
			    <?php
		}
?>
</div>
