<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Voucher_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Voucher_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('cart');
        $this->load->helper('data_helper');
    }

    /* *************************************************************************
         save_voucher_type() - save a new voucher type to the db
         @param array $voucher - the post array voucher type definition
    */
    public function save_voucher_type($voucher_type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_voucher_type_start');

		// default the threshold value if not set
			if (!is_numeric($voucher_type['threshold']))
			{
				$voucher_type['threshold']=0;
			}

		// insert new
            $this->db->insert('voucher_type',$voucher_type);

        /* BENCHMARK */ $this->benchmark->mark('func_save_voucher_type_end');
    }

    /* *************************************************************************
         save_voucher() - saves an actual voucher of a particular voucher type
         @return $message with feedback
    */
    public function save_voucher($voucher)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_voucher_start');

        // check for this code existing
            $query=$this->db->select('*')->from('voucher')->where(array('voucher_id'=>$voucher['voucher_id']));
            $res=$query->get();
            $result=$res->result_array();

        // return message
            $message=array();

        // check
            if (count($result)>0)
            {
                // code exists
                    $message['text']="this code is already in use";
                    $message['pass']="fail";
            }
            else
            {
                if (strlen($voucher['expires']))
                {
                    // make expires date
                        $expires_split=explode("-",$voucher['expires']);
                        $voucher['expires']=$expires_split[2].'-'.$expires_split[1].'-'.$expires_split[0];
                }
                else
                {
                    // get now
                        $split=explode("-",date('Y-m-d',time()));

                    // year, mostly this year
                        $year=$split[0];

                    // month, next year too if its month 12 as we are adding one month on
                    // and formatted with leading zero
                        if (12==$split[1])
                        {
                            $month='01';
                            $year=$split[0]+1;
                        }
                        elseif ($split[1]>9 &&
                                $split[1]<12)
                        {
                            $month=$split[1]+1;
                        }
                        else
                        {
                            $month="0".($split[1]+1);
                        }

                    // day, same as now, formatted with leading zero
                        if ($split[2]>28)
                        {
                            $day="28";
                        }
                        elseif ($split[2]>9 &&
                                $split[2]<29)
                        {
                            $day=$split[2];
                        }
                        else
                        {
                            $day="0".$split[2];
                        }

                    // voucher expires one month from now as default
                        $voucher['expires']=$year."-".$month."-".$day;
                }

                // the multiple use checkbox becomes single shot in the db, MU off is a better default
                    if (isset($voucher['multiple_use']) ? $voucher['single_shot']=0 : $voucher['single_shot']=1 );
                    unset($voucher['multiple_use']);

                // save
                    $this->db->insert('voucher',$voucher);

                // success message
                    $message['text']="the voucher was added";
                    $message['pass']="success";
            }

        /* BENCHMARK */ $this->benchmark->mark('func_save_voucher_end');

        return $message;
    }

    /* *************************************************************************
     apply_voucher() - applies a voucher to the cart, saving the result in the cart
     @param string $voucher_id - the voucher to apply
     @return
    */
    public function apply_voucher($voucher_id=null)
    {
            /* BENCHMARK */ $this->benchmark->mark('func_apply_voucher_start');

        // first see if there is a voucher in the cart if there has not been one entered - we use that for the calculation
            if (null==$voucher_id)
            {
                // get voucher from cart and set voucher id from that
                $cart=$this->cart->contents();

                foreach ($cart as $c)
                {
                    if ('voucher'==$c['id'])
                    {
                        $voucher_id=$c['options']['voucher_id'];
                    }
                }
            }

        // display message
            $display_message['pass']='fail';
            $display_message['message']='';

        // if there is still not a voucher then check the cart has no voucher set - else we have a voucher id and must calculate
            if (null==$voucher_id)
            {
                // no voucher entered so remove it from the cart
                    $this->remove_voucher();

                $display_message['message']='no voucher applied';
            }
            else
            {
                // get the voucher details
                    $query=$this->db->select('*')->from('voucher')->where(array('voucher_id'=>$voucher_id));
                    $res=$query->get();
                    $voucher=$res->row_array();

                // get the basket totals
                    $total=$this->basket_model->total();
                    $order_total=$total['total_nv'];
                    $product_total=$total['product'];
                    $postage_total=$total['postage'];

                // if the code is for a voucher that exists in the voucher table
                    if (count($voucher)>0)
                    {
                        // now get the details of the voucher type so we can see expires etc.
                            $query=$this->db->select('*')->from('voucher_type')->where(array('voucher_type_id'=>$voucher['voucher_type_id']));
                            $res=$query->get();
                            $voucher_type=$res->row_array();

                        // get the expires date
                            $now=date('Y-m-d');

                        // if voucher is valid - runs right the way to the end of the expiray date
                            if ($voucher['expires']<=$now)
                            {
                                $display_message['message']="<span class='voucher_fail_message'>voucher expired, sorry</span>";

                                // remove the voucher from the cart if it has expired
                                    $this->remove_voucher();
                            }
                            else
                            {
                                // voucher spent
                                    if (1==$voucher["spent"])
                                    {
                                        $display_message['message']="<span class='voucher_fail_message'>this voucher has already been used</span>";

                                        $this->remove_voucher();
                                    }
                                    else
                                    {
                                        // check the threshold value
                                            if (('total'==$voucher_type["adjust_focus"] && $order_total<$voucher_type["threshold"]) or
                                                ('postage'==$voucher_type["adjust_focus"] && $postage_total<$voucher_type["threshold"]))
                                            {
                                                $display_message['message']="<span class='voucher_success_message'>you have not spent enough on your ".$voucher_type["adjust_focus"]."</span>";

                                                $this->remove_voucher();
                                            }
                                            else
                                            {
                                                // valid

                                                // set the cal vals
                                                    // basic values
                                                        $vcfocus=$voucher_type["adjust_focus"];
                                                        $vctype=$voucher_type["adjust_type"];
                                                        $vcvalue=$voucher_type["adjust_value"];

                                                        /* dev_dump($vcfocus);
                                                        dev_dump($vctype);
                                                        dev_dump($vcvalue); */

                                                    // focus val will be operated on, old val will be used to calculate the discount
                                                        if ('total'==$vcfocus)
                                                        {
                                                            $focus_value=$order_total;
                                                            $old_val=$order_total;
                                                        }
                                                        else
                                                        {
                                                            $focus_value=$postage_total;
                                                            $old_val=$postage_total;
                                                        }
                                                        /* dev_dump($focus_value);
                                                        dev_dump($old_val); */

                                                // calculate
                                                    if ('pound'==$vctype)
                                                    {
                                                        $focus_value-=$vcvalue;
                                                    }
                                                    elseif ('percentage'==$vctype)
                                                    {
                                                        $focus_value=$focus_value-(($focus_value*$vcvalue)/100);
                                                    }

                                                    // format a zero, so we don't get total -0.01 pence or some such
                                                        if ($focus_value<0)
                                                        {
                                                            $focus_value=0;
                                                        }

                                                    // calculate the amount saved for display
                                                        $discount_amount=$focus_value-$old_val;

                                                        /* dev_dump($focus_value);
                                                        dev_dump($discount_amount); */

                                                // build display string
                                                    if ('percentage'==$vctype &&
                                                        $vcvalue>99)
                                                    {
                                                        $vdesc="FREE! ";
                                                    }
                                                    else
                                                    {
                                                        if ('pound'==$vctype ? $vdesc=format_price($vcvalue).' off ' : $vdesc=$vcvalue.'% off ' );
                                                    }

                                                    $vdesc.=$vcfocus;

                                                    if ($voucher_type["threshold"]>0)
                                                    {
                                                        $vdesc.=" if you spend over ".format_price($voucher_type["threshold"]);
                                                    }

                                                // remove the voucher to add a new one
                                                    $this->remove_voucher();

                                                // voucher values
                                                    $cart_voucher=array(
                                                        'id'=>'voucher',
                                                        'qty'=>1,
                                                        'price'=>$discount_amount,
                                                        'name'=>'Voucher',
                                                        'options'=>array(
                                                            'voucher_id'=>$voucher_id,
                                                            'voucher_type'=>$voucher_type['voucher_type_id'],
                                                            'voucher_string'=>"'".$voucher_id."' applied: ".$vdesc
                                                    ));

                                                $this->cart->insert($cart_voucher);

                                                $display_message['message']="<span class='voucher_success_message'>'".$voucher_id."' applied: ".$vdesc."</span>";
                                                $display_message['pass']='success';
                                            }
                                    }
                            }
                    }
                    else
                    {
                        // this code is for a voucher that does not exist
                            $display_message['message']="<span class='voucher_fail_message'>not a code</span>";

                    }
            }

        // this message may be displayed front stage if the voucher has just been applied by the user on the basket page
            return $display_message;

        /* BENCHMARK */ $this->benchmark->mark('func_apply_voucher_end');
    }

    /* *************************************************************************
         remove_voucher() - gets rid of the current voucher from the cart
    */
    public function remove_voucher()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_remove_voucher_start');

        $cart=$this->cart->contents();
        foreach ($cart as $c)
        {
            if ('voucher'==$c['id'])
            {
                $this->cart->update(array('rowid'=>$c['rowid'],'qty'=>0));
            }
        }

        /* BENCHMARK */ $this->benchmark->mark('func_remove_voucher_end');
    }

    /* *************************************************************************
         spend_voucher() - called by order success, checks if a voucher has been spent
         @param string
         @param numeric
         @param array
         @return
    */
    public function spend_voucher()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_spend_voucher_start');

        // get the order id
            $query=$this->db->select('*')->from('user_order')->where(array('order_id'=>$this->session->userdata('order_id')));
            $res=$query->get();
            $order=$res->row_array();

        // get the voucher details
            $query=$this->db->select('*')->from('voucher')->where(array('voucher_id'=>$order['voucher_id']));
            $res=$query->get();
            $voucher=$res->row_array();

        // if single shot
            if (isset($voucher['single_shot']) &&
                1==$voucher['single_shot'])
            {
                // spend
                    $update_data = array(
                        'spent' =>1
                    );

                    $this->db->where('voucher_id', $order['voucher_id']);
                    $this->db->update('voucher', $update_data);
            }

        /* BENCHMARK */ $this->benchmark->mark('func_spend_voucher_end');
    }
}
