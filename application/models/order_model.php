<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Order_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 * @license     granted to be used by COMPANY_NAME only
 *              granted to be used only for PROJECT_NAME at URL
 *              COMPANY_NAME is free to modify and extend
 *              COMPANY_NAME is not permitted to copy, resell or re-use on other projects
 *              this license applies to all code in the root folder and all sub folders of
 *                  PROJECT_NAME that also exists in the corresponding folder(s) in the
 *                  copy of PROJECT_NAME kept by Toni Leigh Sharpe at sign off, even if
 *                  modified by COMPANY_NAME or their third party consultants
 *                  any copy of this code found without a corresponding copy in
 *                  Toni Leigh Sharpe's repository at http://bitbucket.org/Toni Leighsharpe will be
 *                  considered as copied without permission
 *                  (NB - does not apply to code covered GPL or similar, an example being jQuery)
 *              THIS CODE COMMENT MUST REMAIN INTACT IN ITS ENTIRITY
*/
    class Order_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        $this->load->library('cart');
        $this->load->model('basket_model');
    }

    /* *************************************************************************
         save_order() - saves an order to the database
         @param array $vals - array of values for the order, inc. address
         @return
    */
    public function save_order($vals)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_order_start');

            // cart
                $cart=$this->cart->contents();

            // totals
                $total=$this->basket_model->total();
                $vals['order_total']=$total['total'];
                $vals['product_total']=$total['product'];
                $vals['postage_total']=$total['postage'];
                $vals['voucher_total']=$total['voucher'];

            // voucher id
                $voucher_id='';
                foreach($cart as $c)
                {
                    if ('voucher'==$c['id'])
                    {
                        $voucher_id=$c['options']['voucher_id'];
                    }
                }
                $vals['voucher_id']=$voucher_id;

            // basket
                // also add the cart into the user table so it is there after payment processing
                    $this->basket_model->basket_to_user();

                // also get the items into the post array to store in the order
                    $vals['items']=json_encode($cart);

            // user id
                if (isset($this->user['user_id']) ? $vals['user_id']=$this->user['user_id'] : $vals['user_id']=0 );


            // address (doesn't exist if this is a paypal submit)
                if (!isset($vals['email'])) $vals['email']='';
                if (!isset($vals['phone'])) $vals['phone']='';

                if (!isset($vals['dname'])) $vals['dname']='';
                if (!isset($vals['dhouse'])) $vals['dhouse']='';
                if (!isset($vals['daddress1'])) $vals['daddress1']='';
                if (!isset($vals['daddress2'])) $vals['daddress2']='';
                if (!isset($vals['dtown'])) $vals['dtown']='';
                if (!isset($vals['dpostcode'])) $vals['dpostcode']='';
                if (!isset($vals['dcountry'])) $vals['dcountry']='';

                if (!isset($vals['bname'])) $vals['bname']='';
                if (!isset($vals['bhouse'])) $vals['bhouse']='';
                if (!isset($vals['baddress1'])) $vals['baddress1']='';
                if (!isset($vals['baddress2'])) $vals['baddress2']='';
                if (!isset($vals['btown'])) $vals['btown']='';
                if (!isset($vals['bpostcode'])) $vals['bpostcode']='';
                if (!isset($vals['bstate'])) $vals['bstate']='';
                if (!isset($vals['bcountry'])) $vals['bcountry']='';

        // create an order
            $this->db->insert('user_order',$vals);
            $vals['order_id']=$this->db->insert_id();

        /* BENCHMARK */ $this->benchmark->mark('func_save_order_end');

        return $vals;
    }

    /* *************************************************************************
         paypal_postdata() - build an array of paypal post data
         @param string
         @param numeric
         @param array
         @return
    */
    public function paypal_postdata()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_paypal_postdata_start');

        // again some quantities
            $contents=$this->cart->contents();
            $product_count=$this->basket_model->get_actual_cart_total();

        if ($product_count>0)
        {
            // open form and initialise various paypal values - inc vendor from config
                $post_data['cmd']='_cart';
                $post_data['upload']='1';
                $post_data['business']=$this->config->item('paypal_vendor');
                $post_data['currency_code']=$this->config->item('paypal_currency');
                $post_data['return']='http://'.$this->config->item('full_domain').'/order/complete';
                $post_data['shopping_url']='http://'.$this->config->item('full_domain').'/products';
                $post_data['cancel_return']='http://'.$this->config->item('full_domain').'/order/fail';

            // now iterate through the cart contents
                $counter=1;
                $discount_rate=0;
                foreach ($contents as $c)
                {
                    if ('voucher'==$c['id'])
                    {
                        // voucher, add to discount rate so paypal knows this is an amount off
                            $discount_rate=$c['price'];
                    }
                    else
                    {
                        // postage needs to be updatedable on edit on basket page so we need to add special classes to this for jquery
                            if ('postage'==$c['id'] ? $extra_class=" class='paypal_postage' " : $extra_class="" );

                        // get the product variation
                            $name_append='';
                            if ($c['id']!='postage')
                            {
                                foreach ($c['options'] as $k=>$v)
                                {
                                    $name_append.=str_replace('_',' ',$k)." ".str_replace('_',' ',$v)."; ";
                                }
                            }

                        // now add the item name, amount and quantity
                            $post_data['item_name_'.$counter]=$counter.". ".$c["name"]." [".$name_append."]";
                            $post_data['amount_'.$counter]=$c["price"];
                            $post_data['quantity_'.$counter]=$c["qty"];

                        $counter++;
                    }
                }

            // finally close by adding the discount rate and adding the submit button (again this is updatable by user input so a class is added like postage)
                $post_data['discount_amount_cart']=$discount_rate;
        }

        /* BENCHMARK */ $this->benchmark->mark('func_paypal_postdata_end');

        return $post_data;
    }

    /* *************************************************************************
         worldpay_postdata() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function worldpay_postdata($order)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_worldpay_postdata_start');

        $post_data['testMode']=$this->config->item('worldpay_mode');

        $post_data['instId']='';
        $post_data['cartId']=$order['order_id'];

        $post_data['amount']=$order['order_total'];
        $post_data['currency']=$this->config->item('worldpay_currency');

        $post_data['desc']="basket of products from ".$this->config->item('site_name');

        $post_data['name']=$order['bname'];
        $post_data['address1']=$order['bhouse'];
        $post_data['address2']=$order['baddress1'];
        $post_data['address3']=$order['baddress2'];
        $post_data['town']=$order['btown'];
        $post_data['region']='';
        $post_data['postcode']=$order['bpostcode'];
        $post_data['country']=$order['bcountry'];

        $post_data['tel']=$order['phone'];
        $post_data['email']=$order['email'];

        return $post_data;

        /* BENCHMARK */ $this->benchmark->mark('func_worldpay_postdata_end');
    }

    /* *************************************************************************
         get_last_order() - get the last order for this signed in user
         @return $last_order array
    */
    public function get_last_order()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_last_order_start');

        // look to see if there are any orders for this user saved in the db (on user id only, not on cookie or such like -
        // anonymous means nothing obvious like a filled in form on the users PC)
            $query=$this->db->select('*')->from('user_order')->where(array('user_id'=>$this->user['user_id']))->order_by('order_date desc')->limit(1);
            $res=$query->get();
            $last_order=$res->row_array();

        // not present so set it to null
            if (0==count($this->data['last_order']))
            {
                $last_order=null;
            }

        return $last_order;

        /* BENCHMARK */ $this->benchmark->mark('func_get_last_order_end');
    }

    /* *************************************************************************
         get_countries() - get countries
         @return $countries array
    */
    public function get_countries()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_countries_start');

        $query=$this->db->select('*')->from('country_code')->order_by('country_id');
        $res=$query->get();
        return $res->result_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_countries_end');
    }

    /* *************************************************************************
         get_states() - get US states
         @return $states array
    */
    public function get_states()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_states_start');

        $query=$this->db->select('*')->from('us_state_code')->order_by('state_name');
        $res=$query->get();
        return $res->result_array();

        /* BENCHMARK */ $this->benchmark->mark('func_get_states_end');
    }

    /* *************************************************************************
         stock_notify_email() - look at this basket of products and see if any stock notifications need to be sent
    */
    public function stock_notify_email()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_stock_notify_email_start');

        // as stock levels are now confirmed as an order is successful we can check if any
        // thresholds have been breached and warn the site owner
            $stock=$this->basket_model->get_stock();

            // build up a notification array containing the variations whose stock has slipped
            // to less than or equal to the threshold value
                $notify=array();
                foreach ($stock as $k=>$v)
                {
                    if ($v['level']<=$v['thresh'])
                    {
                        $notify[]=$k;
                    }
                }

            // if we have some notifications to send then build an email and send it to site admin
                if (count($notify)>0)
                {
                    // email body
                        $em_bod="This is a stock level notification from ".$this->config->item('site_name').":<br/><br/>";
                        $em_bod.='These items have variations that have fallen below their threshold level:<br/><br/>';
                        for ($x=0;$x<count($notify);$x++)
                        {
                            $variation=$this->variation_model->get_nvar($notify[$x]);
                            $node=$this->node_model->get_node($variation['node_id']);
                            $em_bod.="<a href='http://".$this->config->item('full_domain')."/product/".$node['id']."/variations'>".$node['name']." [click to view variations]</a><br/><br/>";
                        }
                        $em_bod.='NB automated email, please do not reply, use the email address in the message above';

                    // headers, allows html through, sets from value
                        $headers="MIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\nFrom: ".$this->config->item('full_domain')." Stock Level Notification <noreply@".$this->config->item('full_domain')."> \r\n";

                    // send the email
                        mail($this->config->item('site_email'),"Stock Level email from <".$this->config->item('from_email').">",$em_bod,$headers);
                }

        /* BENCHMARK */ $this->benchmark->mark('func_stock_notify_email_end');
    }

    /* *************************************************************************
         mark_sold() - record the sold quantities in the nvar table for this product
    */
    public function mark_sold()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_mark_sold_start');

            $cart=$this->cart->contents();

            foreach ($cart as $c)
            {
                if ($c['id']!='postage' &&
                    $c['id']!='voucher')
                {
                    $query=$this->db->select('*')->from('nvar')->where(array('nvar_id'=>$c['id']));
                    $res=$query->get();
                    $nvar=$res->row_array();

                    $sold=$nvar['sold']+$c['qty'];

                    $update_data = array(
                        'sold' =>$sold
                    );

                    $this->db->where('nvar_id', $c['id']);
                    $this->db->update('nvar', $update_data);
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_mark_sold_end');
    }
}
