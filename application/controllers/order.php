<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Order

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 *
 * order controller
 *   - responds to various order related events, such as success or failure from a payment processor
*/

    class Order extends Node {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('basket_model');
        $this->load->model('node_model');
        $this->load->model('order_model');
        $this->load->model('variation_model');
        $this->load->library('cart');
    }

    /* *************************************************************************
         address() - loading the order-address page redirects through here to set some variables up
         @reload - loads the address page view which displays the address form
    */
    public function address()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_address_start');

        // get last order
            $this->data['last_order']=$this->order_model->get_last_order();

        // get the drop downs for country and US state - use the last order to pre-select
            // countries
                $countries=$this->order_model->get_countries();

            // countries for delivery
                $this->data['dcountry']="<select id='dcountry_select' name='dcountry'>";
                foreach ($countries as $c)
                {
                    if (isset($last_order['dcountry']) && $c['country_code']==$last_order['dcountry'] ? $selected=" selected='selected' " : $selected="" );
                    $this->data['dcountry'].="<option value='".$c['country_code']."' ".$selected." >".$c['country_name']."</option>";
                }
                $this->data['dcountry'].="</select>";

            // countries for billing
                $this->data['bcountry']="<select id='bcountry_select' name='bcountry'>";
                foreach ($countries as $c)
                {
                    if (isset($last_order['bcountry']) && $c['country_code']==$last_order['bcountry'] ? $selected=" selected='selected' " : $selected="" );
                    $this->data['bcountry'].="<option value='".$c['country_code']."' ".$selected." >".$c['country_name']."</option>";
                }
                $this->data['bcountry'].="</select>";

            // states
                $states=$this->order_model->get_states();

            // states for billing only
                $this->data['bstate']="<select name='bstate'>";
                foreach ($states as $s)
                {
                    if (isset($last_order['bstate']) && $s['state_code']==$last_order['bstate'] ? $selected=" selected='selected' " : $selected="" );
                    $this->data['bstate'].="<option value='".$s['state_code']."' ".$selected." >".$s['state_name']."</option>";
                }
                $this->data['bstate'].="</select>";

        // display the node for address
            $this->display_node('order-address');

        /* BENCHMARK */ $this->benchmark->mark('func_address_end');
    }

    /* *************************************************************************
         initialise() - does some stuff before redirecting to payment processing
         @param Boolean $payment_redirect - calls payment redirect function, not used if this is paypal call
         @return void
    */
    public function initialise($provider)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_initialise_start');

        // get the post and rid of the submit button for use in a db query
            $post=$this->get_input_vals();

        // check for not enough stock and reload to the basket page if this is hit
            // processes - remember these functions actually process and return true or false if the made changes
            $stock_check=$this->basket_model->order_stock_check();
            $price_check=$this->basket_model->price_check();
            if (1==$stock_check or
                1==$price_check)
            {
                // catch and reload
                    // open message
                        $catch_message="you have been redirected from clicking to pay due to changes to items in your basket, ".
                        "either by the site adminstrators or other customers, that occured during the checkout process:<br/><br/>";

                    // append with stock bit
                        if (1==$stock_check)
                        {
                            $catch_message.="- another customer was checking out with the same items as you in their basket ".
                            "and they got their first meaning the site doesn't have enough stock in to fully process your order. ".
                            "you will see fewer items in your basket than you originally added or items that have gone due to all ".
                            "available stock being bought by other customers<br/><br/>";
                        }

                    // sale
                        if (1==$price_check)
                        {
                            $catch_message.="- the site administrators have changed the prices of items in your basket, by either ".
                            "directly changing them or by setting them on or off sale. you will see items in your basket where the ".
                            "sub totals and prices have changed<br/><br/>";
                        }

                    // close catch message
                        $catch_message.="this is most likely to have arisen if you left your browser window open for a while then ".
                        "returned to complete your transaction<br/><br/>please check the contents of your basket before proceeding";

                    $this->_reload("basket",$catch_message,"fail");
            }
            else
            {
                    // don't lock the stock twice - if this is a re-processing (after processing, then back button, without refresh, i.e. someone
                    // checking details then clikcing 'checkout' again) then we don't want to lock another bags worth of stock
                    // so we check to see if the stock has been unlocked in the session, which will only have happened on a back button and reload, or
                    // a successful navigation of the payment processing system
                        if (0==$this->session->userdata('lock_stock'))
                        {
                            $this->basket_model->order_stock_adjust('down');

                            // set stock lock cookie, this is used to re-add the stock if the user backs up from the payment processing in the browser
                            // as soon as the page is refreshed after they have backed up it will be hit and used to re-add their stock to the system
                                $this->session->set_userdata(array('lock_stock'=>1));
                        }

                // save order
                    $post=$this->order_model->save_order($post);

                // store the order id in the session
                    $this->session->set_userdata(array('order_id'=>$post['order_id']));

                // now we redirect to payment ...
                    $this->payment_redirect($provider,$post);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_initialise_end');
    }

    /* *************************************************************************
         payment_redirect() - redirects to a payment processing service
         @return
    */
    public function payment_redirect($provider,$order_details)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_payment_redirect_start');

        // switch over payment providers (a config value decides which one is used)
            //$this->_reload("order/fail","order failed","fail");

        if ('LIVE'==$this->config->item('payment_live'))
        {
            switch ($provider)
            {
                case 'ccbill':
                    break;
                case 'paypal':
                    $this->make_paypal_post();
                    break;
                case 'sagepay':
                    $this->make_sagepay_post($order_details);
                    break;
                case 'worldpay':
                    $this->make_worldpay_post($order_details);
                    break;
                case 'zombaio':
                    break;
            }
        }
        else
        {
            echo "<a href='/order/fail'>fail</a><br/><br/>";
            echo "<a href='/order/success'>success</a><br/><br/>";
        }

        // foreach establish the data as the payment provider requires it

        // foreach redirect string formatted appropriately using config values

        /* BENCHMARK */ $this->benchmark->mark('func_payment_redirect_end');
    }

    /* *************************************************************************
         make_paypal_post() - post data to paypal
    */
    public function make_paypal_post()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_make_paypal_post_start');

        $post_data=$this->order_model->paypal_postdata();

        header("Location: https://www.paypal.com/cgi-bin/webscr?".http_build_query($post_data));
        exit();

        /* BENCHMARK */ $this->benchmark->mark('func_make_paypal_post_end');
    }

    /* *************************************************************************
         make_sagepay_post() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function make_sagepay_post($order)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_make_sagepay_post_start');

        /* BENCHMARK */ $this->benchmark->mark('func_make_sagepay_post_end');
    }

    /* *************************************************************************
         make_worldpay_post() - post data to worldpay
         @param array $order - the order details
         @return
    */
    public function make_worldpay_post($order)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_make_worldpay_post_start');

        $post_data=$this->order_model->worldpay_postdata();

        header("Location: ".$this->config->item('worldpay')."?".http_build_query($post_data));
        exit();

        /* BENCHMARK */ $this->benchmark->mark('func_make_worldpay_post_end');
    }

    /* *************************************************************************
     success() - function executes when the payment provider returns a success value
     @reloads a order success page for user feedback
    */
    public function success()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_success_start');

        // unset the cookie so this just happens once after back from payment
            $this->session->set_userdata(array('lock_stock'=>0));

        // notify with stock
            $this->order_model->stock_notify_email();

        // we must also increment the 'sold' value in the nvar
            $this->order_model->mark_sold();

        // get the order and check if a single shot voucher was spent
            $this->voucher_model->spend_voucher();

        // add the cart into data array, then we can display to the user what they just bought
            $this->session->set_userdata('order_items',$this->cart->contents());
            $this->session->set_userdata('order_totals',$this->basket_model->total());

        // get rid of the stored cart, it is not needed anymore
            $this->cart->destroy();

        // if anon get cookie key to do this

        // display a success node
            $this->_log_action("order-success","order success - your order was placed","success");
            $this->_reload("order-success","order success - your order was placed","success");

        /* BENCHMARK */ $this->benchmark->mark('func_success_end');
    }

    /* *************************************************************************
     fail() - function executes when the payment provider returns a fail value
     @reloads a order fail page for user feedback
    */
    public function fail()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_fail_start');

        // unset the cookie so this just happens once after back from payment
            $this->session->set_userdata(array('lock_stock'=>0));

        // adjust the stock levels back up to where they were pre-order, effectively unlock the stock that was locked on initialise
            $this->basket_model->order_stock_adjust('up');

        // display a fail node
            $this->_log_action("order-failed","order failed","fail");
            $this->_reload("order-failed","order failed","fail");

        /* BENCHMARK */ $this->benchmark->mark('func_fail_end');
    }

    /* *************************************************************************
         list_orders() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function list_orders()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_list_orders_start');

        $this->display_node('order-list');

        /* BENCHMARK */ $this->benchmark->mark('func_list_orders_end');
    }
}
