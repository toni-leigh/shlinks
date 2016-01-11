<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Basket_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 *
 * basket model
 *   - functions that operate on the basket
*/
    class Basket_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('cart');
        $this->load->model('events_model');
        $this->load->model('node_model');
        $this->load->model('postage_model');
        $this->load->model('voucher_model');
    }

    /* *************************************************************************
         add_to_basket() - adds an array of buyable nodes to the basket - could be a single product from a buy button,
            an array of products from a 're-bag' call or an array of events passed in from an external, embedded calendar
         @param array $adds - the array of nodes to iterate over adding to basket
         @return
    */
    public function add_to_basket($adds)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_to_basket_start');

        foreach ($adds as $add)
        {
            // correct price - if the item is on sale then we need to add in the sale price
                $now=date('Y-m-d',time());
                if ($add['nvar']['sale_start']<=$now &&
                    $add['nvar']['sale_end']>=$now)
                {
                    $price=$add['nvar']['sale_price'];
                }
                else
                {
                    $price=$add['nvar']['price'];
                }

            // look to see if a row exists for this product variation in the basket
                $update_row=$this->check_for_row($add['nvar']);

            // no row then we insert, otherwise we update the quantity for the existing row
                if (null==$update_row)
                {

                    // stock no more than limit - only add in a number of items that stock levels can cover
                        if ($add['quantity']>$add['nvar']['stock_level'] ? $qty=$add['nvar']['stock_level'] : $qty=$add['quantity'] );

                    // define item
                        $item=array(
                            'id'=>$add['nvar']['nvar_id'],
                            'qty'=>$qty,
                            'price'=>$price,
                            'name'=>str_replace("'","",$add['product']['name']),
                            'pcalc'=>$add['nvar']['post_calc'],
                            'options'=>$add['nvar']['vals'],
                            'type'=>$add['product']['type'] // event or product
                        );

                    // add
                        $this->cart->insert($item);
                }
                else
                {
                    // get a total quantity, bagged plus to be bagged, then use this for stock check
                        $total_qty=$add['quantity']+$update_row['qty'];

                    // stock no more than limit - only add in a number of items that stock levels can cover
                        if ($total_qty<=$add['nvar']['stock_level'] ? $qty=$total_qty : $qty=$add['nvar']['stock_level'] );

                    // set item values, with this rowid for the row to update
                        $item=array(
                            'rowid'=>$update_row['rowid'],
                            'qty'=>$qty
                        );

                    // update
                        $this->cart->update($item);
                }
        }

        /* BENCHMARK */ $this->benchmark->mark('func_add_to_basket_end');
    }

    /* *************************************************************************
         basket_to_user() - stores the current basket in the user table for the signed in user
         @return
    */
    public function basket_to_user()
    {
        /* BENCHMARK */ $this->benchmark->mark('func__start');

        if (isset($this->user['user_id']))
        {
			$update_data = array(
				'basket' =>json_encode($this->cart->contents())
			);

			$this->db->where('user_id', $this->user['user_id']);
			$this->db->update('user', $update_data);
        }

        /* BENCHMARK */ $this->benchmark->mark('func__end');
    }

    /* *************************************************************************
         check_for_row() - checks the cart to see if a row exists in the cart, based on the rows 'id' value (not the rowid)
         @return array $update_row - the rowid and qty of the row to be updated
    */
    public function check_for_row($nvar)
    {
        // update or insert, depends on whether there is already one in there
        $contents=$this->cart->contents();

        $update_row=null;
        foreach ($contents as $k=>$v)
        {
            if ($v['id']==$nvar['nvar_id'])
            {
                $update_row['rowid']=$k;
                $update_row['qty']=$v['qty'];
            }
        }

        return $update_row;
    }

    /* *************************************************************************
         header_basket() - prepares a basic output for the basket in the header - uses some config values to decide what to show
            also sets some values to do with the basket on page load (as the header always loads)
         @return $html - the html for the header basket panel
    */
    public function header_basket()
    {
        $this->load->helper('cookie_helper');
        $this->load->helper('data_helper');
        $this->load->helper('url_helper');

        $html="";

        // perform an unlock stock operation if we need to (this will be necessary to unlock stock that is locked by paypal
        // the cookie will only be set by paypal redirect and is unset by any return from paypal that represents a completed transaction

                //dev_dump($this->session->userdata('lock_stock'));
            if (1==$this->session->userdata('lock_stock'))
            {
                // only do this if we haven't successfully gone through the payment processing - this is just for the back button
                    if (uri_string()!='order-success' &&
                        uri_string()!='order-failed')
                    {
                        $this->order_stock_adjust('up');
                    }

                // unset the cookie so this just happens once after back from payment
                    $this->session->set_userdata(array('lock_stock'=>0));

                //dev_dump($this->session->userdata('lock_stock'));
            }

        // check the stock levels in the basket to make sure that since the last load there is still enough stock for the basket
            $stock_check=$this->order_stock_check();

        // checks the sale values, if a bagged product has gone of sale it must be reset to its original price (and vide versa)
            $price_adjust=$this->basket_model->price_check();

        // apply the voucher to the basket too so the retrieved totals are correct
            $this->voucher_model->apply_voucher();

        // get the actual count of products, CI cart library doesn't actually factor the quantities in, it just counts rows
            $product_count=$this->get_actual_cart_total();

        // get the totals for output, format them
            $total=$this->basket_model->total();
            $product_total=number_format($total['product'],2);
            $cart_total=number_format($total['total'],2);
            $postage_total=number_format($total['postage'],2);
            $voucher_total=number_format($total['voucher'],2);

        // pluralise items if necessary
            if (1==$product_count ? $items_text='item' : $items_text='items' );

        // use the config values to decide what to output - lots of css identifiers here for styling
            if ($this->config->item('hbasket_count') ? $html.="<span id='hbasket_itemcount' class='hb_row'><span id='hb_iclbl' class='hb_lbl'>items</span> <span id='hb_icval' class='hb_val'>".$product_count."</span></span>" : $html.="" );
            if ($this->config->item('hbasket_prod') ? $html.="<span id='hbasket_itemcost' class='hb_row'><span id='hb_pcstlbl' class='hb_lbl'>items</span> <span id='hb_pcstval' class='hb_val'>&pound;<span id='header_items'>".$product_total."</span> </span> </span>" : $html.="" );
            if ($this->config->item('hbasket_postage') ? $html.="<span id='hbasket_postagecost' class='hb_row'><span id='hb_pstlbl' class='hb_lbl'>postage</span> <span id='hb_pstval' class='hb_val'>&pound;<span id='header_postage'>".$postage_total."</span> </span> </span>" : $html.="" );
            if ($this->config->item('hbasket_voucher') ? $html.="<span id='hbasket_voucher' class='hb_row'><span id='hb_vclbl' class='hb_lbl'>voucher</span> <span id='hb_vcval' class='hb_val'>&pound;<span class='voucher_row'>".$voucher_total."</span> </span> </span>" : $html.="" );

        // finish off the basket with grand total (always displayed)
            $html.="<span id='hbasket_grandtotal' class='hb_row'><span id='hb_totlbl' class='hb_lbl'>".$this->config->item('hbasket_total_text')."</span> <span id='hb_vcval' class='hb_val'>&pound;<span id='header_total'>".$cart_total."</span> </span> </span><span id='header_voucher' class='voucher_colour' style='display:none;'></span>".$this->config->item('basket_link');
            $html.="<div id='header_basket_bottom'>";

        // if we adjusted things (stock, sales) then notify the user
            $message='';
            if (1==$stock_check)
            {
                $message.="NB some bagged quantities adjusted to avoid out of stock items <a href='/basket'>view basket</a><br/>";

            }

        // if any price has changed this will be noticed here in fact
            if (1==$price_adjust)
            {
                $message.="NB basket quantity may have changed as some items in the basket have had their prices changed (due to sale or site owner price edit) <a href='/basket'>view basket</a>";
            }
            if (strlen($message))
            {
                $this->session->set_userdata("message","<span class='fail message'>".$message."</span>");
            }

            if (1==$this->config->item('pay_in_header_basket'))
            {
                if (1==$this->config->item('hbasket_pay'))
                {
                    $html.="<div id='header_paypal'>";
                    $html.=$this->paypal_form();
                    $html.="</div>";
                }
                $html.="</div>";
            }
            else
            {
                $html.="</div>";
                if (1==$this->config->item('hbasket_pay'))
                {
                    $html.="<div id='header_paypal'>";
                    $html.=$this->paypal_form();
                    $html.="</div>";
                }
            }

        return $html;
    }

    /* *************************************************************************
         total() - defines an array of values for the monetary totals associated with the current basket
         @return array $total - the aforementioned array
    */
    public function total()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_total_start');

        // get cart contents
            $cart=$this->cart->contents();

        // set everything to 0 in case it isn't initialised
            $total=array();
            $total['total']=0;
            $total['product']=0;
            $total['postage']=0;
            $total['voucher']=0;

        // iterate through the cart, adding values and also dealing with the 'voucher' and 'postage' rows
        foreach ($cart as $c)
        {
            if ('voucher'==$c['id'])
            {
                // if its a voucher row the don't add it to the total, but keep it in the voucher total
                    $total['voucher']=$c['price'];
            }
            else
            {
                // other wise, all other cart items add to the total
                    $total['total']+=($c['price']*$c['qty']);
            }

            // in the case of postage make sure the postage total is initialised
                if ('postage'==$c['id'])
                {
                    $total['postage']=$c['price'];
                }
        }

        // the product total is the total minus the postage (before voucher adjustment)
            $total['product']=$total['total']-$total['postage'];

        // the total nv is equal to the total before voucher adjust - used to show the user how much they are saving more clearly
            $total['total_nv']=$total['total'];

        // then adjust with the voucher amount - total is the actual amount the user will pay for their order
            $total['total']-=$total['voucher'];

        return $total;

        /* BENCHMARK */ $this->benchmark->mark('func_total_end');
    }

    /* *************************************************************************
         get_actual_cart_total() - using qty doh CI - a count of items not cost
         @return int $count - an item count for the cart
    */
    public function get_actual_cart_total()
    {
        $contents=$this->cart->contents();
        $count=0;
        foreach ($contents as $k=>$v)
        {
            if ($v['id']!='postage' && $v['id']!='voucher')
            {
                $count+=$v['qty'];
            }
        }
        return $count;
    }

    /* *************************************************************************
         get_total_postage() - gets the amount that this cart costs based on the admin users id
         @param string $class - the class to use, defaults to UK first class postage
            (as a user chooses postage at basket page something need to be displayed in the header)
         @return array $pvalues - a price and calc total (weight or item count) that defines the
            postage numbers for this cart
    */
    public function get_total_postage($class='uk_first')
    {
        // the admin users id
            $super_admin_id=10;

        // these are our two values for the return array
            $pcalc_total=number_format(0,2);
            $pprice=number_format(0,2);

        // add up the weight / item count for this basket
            $contents=$this->cart->contents();
            foreach ($contents as $k=>$v)
            {
                if ($v['id']!='postage' &&
                    $v['id']!='voucher')
                {
                    $pcalc_total+=($v['qty']*$v['pcalc']);
                }
            }

        // get the postage values for the differnt bands
            $postage=$this->postage_model->get_postages($super_admin_id);

        // got though each postage band looking for the correct postage
            foreach ($postage as $p)
            {
                // if the calculation total is found within a band then thats our price, so grab and break
                // NB -1 is the value used to denot 'MAX' in the db so we need a seperate conditional for this
                    if (($pcalc_total>=$p['min_value'] && $pcalc_total<=$p['max_value']) or
                        ($pcalc_total>=$p['min_value'] && -1==$p['max_value']))
                    {
                        // grab the price at the class handed in as an argument
                            $pprice=$p[$class];

                        break;
                    }
            }

        // build return array
            $pvalues=array(
                'price'=>$pprice,
                'calc'=>$pcalc_total
            );

        return $pvalues;
    }

    /* *************************************************************************
         paypal_form() - function builds a paypal form to be displayed -
            NB this is formed straight from the cart, we don't need the total array
         @return $html - a full paypal form ready to be implanted in the html
    */
    public function paypal_form()
    {
        $html="";

        // again some quantities
            $contents=$this->cart->contents();
            $product_count=$this->get_actual_cart_total();

        if ($product_count>0 or
            1==$this->config->item('always_show_pay_button'))
        {
            if (0==$product_count)
            {
                $html.="<span id='pp_button_deactivated'>".$this->config->item('paypal_button_text')."</span>";
            }
            else
            {
                // open form and initialise various paypal values - inc vendor from config
                    $html.="<form action='/order/initialise/paypal' method='post'>";
                    $html.="<input id='pp_button' class='checkout' type='submit' value='".$this->config->item('paypal_button_text')."' title='pay with credit card or paypal account using paypal - the payment portal most trusted by ebay users'/>";
                    $html.="</form>";
            }
        }
        return $html;
    }
    /* *************************************************************************
         do_postage() - calculates the postage for a basket
         @param string $class - the class of postage to apply
         @param numeric $user_id - the user whose postage should be retrieved,
                this defaults to 10, super admin user, for a single merchant e-commerce situation
         @return void, the cart is updated by this function
    */
    public function do_postage($class='uk_first',$user_id=1)
    {
        // get user for threshold value
            $user=$this->node_model->get_node($user_id,'user');
            $totals=$this->total();

        // postage - here we also check the threshold value
            if ($totals['product']>$user['postage_threshold'])
            {
                $postage=array('price'=>number_format(0,2),'calc'=>number_format(0,2));
            }
            else
            {
                $postage=$this->basket_model->get_total_postage($class);
            }

        // in cart
            $postage_row=$this->basket_model->check_for_row(array('nvar_id'=>'postage'));
            if (null==$postage_row)
            {
                $item=array(
                    'id'=>'postage',
                    'qty'=>1,
                    'class'=>$class,
                    'price'=>$postage['price'],
                    'calc'=>$postage['calc'],
                    'name'=>'postage'
                );

                $this->cart->insert($item);
            }
            else
            {
                // remove, as this update replaces rather than incrementing qty
                    $item=array(
                        'rowid'=>$postage_row['rowid'],
                        'qty'=>0
                    );
                    $this->cart->update($item);

                // then re-add
                    $item=array(
                        'id'=>'postage',
                        'qty'=>1,
                        'class'=>$class,
                        'price'=>$postage['price'],
                        'calc'=>$postage['calc'],
                        'name'=>'postage'
                    );

                    $this->cart->insert($item);
            }

        // re-apply the voucher with the new postage values, for % based vouchers
            $this->voucher_model->apply_voucher();
    }
    /* *************************************************************************
         get_stock() - gets the stock and threshold values for this carts contents -
             used for the locking of stock during checkout and also for seeing if notifications need to be sent
         @return array $stock_key - the array containing level and thresholds, with the nvar_id as the array key
    */
    public function get_stock()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_stock_start');

        // initialise
            $stock_key=array();
            $cart=$this->cart->contents();

        if (count($cart)>0)
        {
            // get all the variations that are bagged
                foreach($cart as $c)
                {
                    $where_in[]=$c['id'];
                }
                $query=$this->db->select('*')->from('nvar')->where_in('nvar_id',$where_in);
                $res=$query->get();
                $stock_levels=$res->result_array();

            // now convert these into an array keyed with the nvar_id containing the level and threshold values
                foreach ($stock_levels as $s)
                {
                    $stock_key[$s['nvar_id']]['level']=$s['stock_level'];
                    $stock_key[$s['nvar_id']]['thresh']=$s['stock_threshold'];
                }
        }

        return $stock_key;

        /* BENCHMARK */ $this->benchmark->mark('func_get_stock_end');
    }

    /* *************************************************************************
         order_stock_check () - checks the cart for quantities that are greater than the available stock
            adjusting those quanitites if they are too high, this is called from the header so no orders can go through
            that are for more than the available stock when stock is locked too for the payment porcessing
         @return int true or false depending on whether it found an issue
    */
    public function order_stock_check()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_order_stock_check_start');

        $cart=$this->cart->contents();

        // check the stock levels of each of the cart items

        // if any are below what the user has bagged then redirect to the basket page with a message that some of their stock was edited
        // due to levels
            $stock_key=$this->get_stock();

            $pclass='uk_first';

            $back_to_bag=0;
            foreach ($cart as $c)
            {
                // check
                    if ($c['id']!='postage' &&
                        $c['id']!='voucher')
                    {
                        if ($stock_key[$c['id']]['level']<$c['qty'])
                        {
                            $c['qty']=$stock_key[$c['id']]['level'];
                            $back_to_bag=1;
                        }
                    }

                // get postage class for do postage call
                    if ('postage'==$c['id'])
                    {

                        if (isset($c['class']) &&
                            strlen($c['class'])>0)
                        {
                            $pclass=$c['class'];
                        }
                    }
            }

        // update the cart if stock bad
            if (1==$back_to_bag)
            {
                $update_cart=array();
                foreach ($cart as $c)
                {
                    if ($c['id']!='postage' &&
                        $c['id']!='voucher')
                    {
                        if ($stock_key[$c['id']]['level']<$c['qty'])
                        {
                            $update_cart[]=array('rowid'=>$c['rowid'],'qty'=>$stock_key[$c['id']]['level']);
                        }
                    }
                }
                $this->cart->update($update_cart);
            }

        // redo the postage to cater for stock level changes (if they happened)
            $this->do_postage($pclass);

        return $back_to_bag;

        /* BENCHMARK */ $this->benchmark->mark('func_order_stock_check_end');
    }

    /* *************************************************************************
         price_check() - check basket and adjust for any items coming on or off sale
         this function will also notice if a price has changed for any reason
    */
    public function price_check()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_price_check_start');

        $cart=$this->cart->contents();

        $changes_made=0;

        if (count($cart)>0)
        {
            // get all the variations that are bagged
                $where_in=array();
                $sales=array();
                foreach($cart as $c)
                {
                    if ($c['id']!='postage' &&
                        $c['id']!='voucher')
                    {
                        $where_in[]=$c['id'];
                    }
                }
                if (count($where_in)>0)
                {
                    $query=$this->db->select('*')->from('nvar')->where_in('nvar_id',$where_in);
                    $res=$query->get();
                    $sales=$res->result_array();
                }

            // now convert these into an array keyed with the nvar_id containing the sales values
                foreach ($sales as $s)
                {
                    $sale_key[$s['nvar_id']]['start']=$s['sale_start'];
                    $sale_key[$s['nvar_id']]['end']=$s['sale_end'];
                    $sale_key[$s['nvar_id']]['sale']=$s['sale_price'];
                    $sale_key[$s['nvar_id']]['price']=$s['price'];
                }

            $now=date('Y-m-d',time());

            foreach ($cart as $c)
            {
                if ($c['id']!='postage' &&
                    $c['id']!='voucher')
                {
                    // check sale dates and get price
                        if ($sale_key[$c['id']]['start']<=$now &&
                            $sale_key[$c['id']]['end']>=$now)
                        {
                            // on sale
                                $new_price=$sale_key[$c['id']]['sale'];
                        }
                        else
                        {
                            // not on sale
                                $new_price=$sale_key[$c['id']]['price'];
                        }

                    // check for difference between prices
                        if ($new_price!=$c['price'])
                        {
                            $changes_made=1;

                            // initialise the new row with the same values as $c but a new price
                                $new_row=$c;
                                $new_row['price']=$new_price;

                            // get ready to remove the old row
                                $remove_row=array('rowid'=>$c['rowid'],'qty'=>0);

                            // remove then add
                                $this->cart->update($remove_row);
                                $this->cart->insert($new_row);
                        }

                }
            }
        }

        return $changes_made;

        /* BENCHMARK */ $this->benchmark->mark('func_price_check_end');
    }

    /* *************************************************************************
         order_stock_adjust() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function order_stock_adjust($operator)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_order_stock_adjust_start');

        //dev_dump($operator,"operator up or down");

        $cart=$this->cart->contents();

        //dev_dump($cart,'cart');

        $stock_key=$this->get_stock();

        // decrease the stock for each cart item based on bagged quantity
            foreach($cart as $c)
            {
                if ($c['id']!='postage' &&
                    $c['id']!='voucher')
                {
                    // adjust value
                        if ('down'==$operator ? $adjust=$stock_key[$c['id']]['level']-$c['qty'] : $adjust=$stock_key[$c['id']]['level']+$c['qty'] );

                    //dev_dump($c['qty'],'c qty');
                    //dev_dump($adjust,'adjuster');

                    // update the stock, all cases
                        $update_data = array(
                            'stock_level' =>$adjust
                        );
                        $this->db->where('nvar_id', $c['id']);
                        $this->db->update('nvar', $update_data);

                    // update the event json - event only
                        if ('event'==$c['type'])
                        {
                            $calendar=$this->node_model->get_node($c['options']['calendar_id'],'calendar');
                            $events=json_decode($calendar['event_json'],true);

                            if ('d'==$c['options']['booked_time'])
                            {
                                // simple, allday event, just update 0 element
                                    $spaces=$events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['t'][0]['s'];
                                    $spaces=$adjust;
                                    $events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['t'][0]['s']=$spaces;
                            }
                            else
                            {
                                // iterate to check the correct event time
                                    for ($x=0;$x<count($events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['t']);$x++)
                                    {
                                        // extract $t for simplicity
                                            $t=$events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['t'][$x];

                                        // if correct nvar then adjust otherwise leave
                                            if ($t['nv']==$c['id'])
                                            {
                                                $t['s']=$adjust;
                                            }

                                        // set the t value back in the event array
                                            $events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['t'][$x]=$t;
                                    }
                            }

                            //dev_dump($events);

                            // if this event is exclusive then we need to do some clever stuff to change the values for other events
                                if (1==$c['options']['exclusive'])
                                {
                                    // this is used to count down the duration of the focus event
                                    // default to null so as zero can be treated as the end in the loop
                                        $count_down=null;

                                    // the list of keys to use to decrement
                                        $to_decrement=array();

                                    // over all dates
                                        foreach ($events as $date=>$elist)
                                        {
                                            // til we find the one for the start of the current event
                                                if ($date>=$c['options']['booked_date'])
                                                {
                                                        //dev_dump($count_down,'check countdown on date - START');
                                                    // set the duration, but only if the countdown is null as we need to break on 0
                                                        if (null===$count_down)
                                                        {
                                                            //dev_dump(1,'in null count down');
                                                            $dur=$this->events_model->get_duration($events[$c['options']['booked_date']]['e'][$c['options']['event_id']]['dr']);
                                                            $count_down=$dur['count'];
                                                        }

                                                        //dev_dump($date.'---'.$count_down,'check countdown on date');

                                                    // only keep getting the exclusivity elements to effect if this event is still ongoing
                                                        if ($count_down>0)
                                                        {
                                                            foreach ($events[$date]['x'] as $k=>$v)
                                                            {
                                                                if (!in_array($k,$to_decrement))
                                                                {
                                                                    $to_decrement[]=$k;
                                                                }
                                                            }
                                                        }
                                                        else
                                                        {
                                                            // get out of here, we are done
                                                                break;
                                                        }
                                                        //dev_dump($count_down,'check countdown on date - END');
                                                }

                                            // decrement the duration so we stop at the right point
                                                $count_down--;
                                        }

                                        //dev_dump($to_decrement,'TO_DECREMENT');

                                    // finally we need to go over the array of elements to decrement and decrement them all by
                                    // qty in both the event json and the nvar table
                                         $nvar_ids=array();

                                        for ($x=0;$x<count($to_decrement);$x++)
                                        {
                                            // break the key into the two needed ids
                                                $id_bits=explode('-',$to_decrement[$x]);
                                                $date_key=$id_bits[0];
                                                $event_id=$id_bits[1];

                                                //dev_dump($id_bits,'TO_DECREMENT_ID_BITS');

                                                // only if its not this event
                                                    //dev_dump($to_decrement[$x],'loop fail - to decrement [x]');
                                                    //dev_dump($c['options']['booked_date'].'-'.$c['options']['event_id'],'loop fail - cart key');
                                                    if ($to_decrement[$x]!=$c['options']['booked_date'].'-'.$c['options']['event_id'])
                                                    {
                                                        //dev_dump(1,'this time in outer loop');
                                                        // get the nvar id for this key
                                                        // and set the spaces as adjust
                                                            for ($y=0;$y<count($events[$date_key]['e'][$event_id]['t']);$y++)
                                                            {

                                                                //dev_dump($events[$date_key]['e'][$event_id]['t'],'this time in inner loop');
                                                                // set the new adjust value
                                                                   if ('down'==$operator ? $adjust=$events[$date_key]['e'][$event_id]['t'][$y]['s']-$c['qty'] : $adjust=$events[$date_key]['e'][$event_id]['t'][$y]['s']+$c['qty'] );

                                                                // update the event array
                                                                    $events[$date_key]['e'][$event_id]['t'][$y]['s']=$adjust;

                                                                // store for nvar update
                                                                    $nvar_ids[$events[$date_key]['e'][$event_id]['t'][$y]['nv']]=$adjust;
                                                            }
                                                    }
                                        }
                                        /*dev_dump($nvar_ids,'NVAR_IDS');
                                        dev_dump($events,'EVENTS');

                                        die();*/

                                        // last thing, iterate over the nvars to update those too
                                            foreach ($nvar_ids as $k=>$v)
                                            {
                                                $update_data = array(
                                                    'stock_level' =>$v
                                                );
                                                $this->db->where('nvar_id', $k);
                                                $this->db->update('nvar', $update_data);
                                            }

                                }

                            // update with the fresh json
                                $update_data = array(
                                    'event_json' =>json_encode($events)
                                );

                                $this->db->where('node_id', $c['options']['calendar_id']);
                                $this->db->update('calendar', $update_data);
                        }
                }
            }

        // update the product json so that the front end reflects the locked stock
        // also this will work on events
            $this->basket_set_json();

        /* BENCHMARK */ $this->benchmark->mark('func_order_stock_adjust_end');
    }

    /* *************************************************************************
         basket_set_json() - set the json for each node in the current basket, so that the front end reflects locked stock
    */
    public function basket_set_json()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_basket_set_json_start');

        $this->load->model('variation_model');

        $cart=$this->cart->contents();

        // get the variation ids from the cart
            $where_in=array();
            foreach ($cart as $c)
            {
                if ($c['id']!='postage' &&
                    $c['id']!='voucher')
                {
                    $where_in[]=$c['id'];
                }
            }

        // get the variations for the node ids
            $result=array();
            if (count($where_in)>0)
            {
                $query=$this->db->select('*')->from('nvar')->where_in('nvar_id',$where_in);
                $res=$query->get();
                $result=$res->result_array();
            }

        // get the ids
            $nodes=array();
            foreach ($result as $r)
            {
                if (!in_array($r['node_id'],$nodes))
                {
                    $nodes[]=$r['node_id'];
                }
            }

        // then update each
            foreach ($nodes as $k=>$v)
            {
                $node=$this->node_model->get_node($v);

                if ('product'==$node['type'])
                {
                    $this->variation_model->set_vjson($v);
                }
                else
                {
                    // call event model function here to set event json
                    /* $this->load->model('events_model');
                    $this->event_model->booked_json($node['id']); */
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_basket_set_json_end');
    }
}



































