<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Basket

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 *
 * basket controller
 *   - responds to ajax calls to edit stored basket values
*/
    class Basket extends Universal {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('data_helper');
        $this->load->library('cart');
        $this->load->model('basket_model');
        $this->load->model('node_model');
        $this->load->model('variation_model');
    }

    /* *************************************************************************
     add() - add an item to the basket
     @param $get['nvar_id'] - the node variation that was added to the basket
     @exit - the header basket html to be reloaded
    */
    public function add($nvar_id=null,$quantity=null,$calendar_id=null,$event_id=null,$booked_date=null,$time=null,$exclusive=0)
    {
		// get array
			$get=$this->get_input_vals();

        // an array for all the adds to be done by this call
            $adds=array();

        // first we need to check for an event submit
            if (isset($get['add_form_cid']) &&
				is_numeric($get['add_form_cid']))
            {
                // counter
                    $c=0;

                // process
                    foreach ($this->input->post() as $k=>$p)
                    {
                        if ($k!='submit' &&
                            $k!='add_form_cid')
                        {
                            // split key
                                $vals=explode('_',$k);

                            // add vals to array
                                $adds[$c]['nvar']=$this->variation_model->get_nvar($vals[1]);
                                $adds[$c]['product']=$this->node_model->get_node($adds[$c]['nvar']['node_id']);
                                $adds[$c]['nvar']['vals']=array(
                                    'calendar_id'=>$this->input->post('add_form_cid'),
                                    'event_id'=>$vals[2],
                                    'booked_date'=>$vals[3],
                                    'booked_time'=>$vals[4],
                                    'exclusive'=>$vals[5]
                                );

                            // quantity
                                $adds[$c]['quantity']=$p;

                            // increment
                                $c++;
                        }
                    }

                // set the calendar css file link in the session
                    $calendar=$this->node_model->get_node($this->input->post('add_form_cid'),'calendar');
                    $this->session->set_userdata(array('cal_css'=>$calendar['css_link']));
            }
            else
            {
				// if args is not set then this is a an onsite product page submit
				// else it is an external url submit (events direct link)
				// this deals with all submissions, form or ajax, unless from calendars
                    if (null==$nvar_id)
                    {
                        // get the full variation details
                            $adds[0]['nvar']=$this->variation_model->get_nvar($get['nvar_id']);

                        // get the full product details
							$node=$this->node_model->get_node($adds[0]['nvar']['node_id'],'product');
                            $adds[0]['product']=$node;

                        // quantity to add
                            $adds[0]['quantity']=$get['add_quantity'];

						// product details for message
							$product_details=$node['name']." added (the basket total includes postage)";
                    }
                    else
                    {
                        // get the full variation details
                            $adds[0]['nvar']=$this->variation_model->get_nvar($nvar_id);

                        // get the event details
                            $adds[0]['product']=$this->node_model->get_node($nvar['node_id']);

                        // set some calendar values as options
                            $adds[0]['nvar']['vals']=array(
                                'calendar_id'=>$calendar_id,
                                'event_id'=>$event_id,
                                'booked_date'=>$booked_date,
                                'booked_time'=>$time,
                                'exclusive'=>$exclusive
                            );

                        // quantity
                            $adds[0]['quantity']=$quantity;

                        // set the calendar css file link in the session
                            $calendar=$this->node_model->get_node($calendar_id,'calendar');
                            $this->session->set_userdata(array('cal_css'=>$calendar['css_link']));
                    }
            }

        // now iterate over the $adds array adding all the things to add !
            $this->basket_model->add_to_basket($adds);

        // finally reset the postage to take into account the full bag with items added
            $this->basket_model->do_postage();

		// ajax ?
			$ajax_exit=$this->input->is_ajax_request();

        // add via ajax
            if (1==$ajax_exit)
            {
                $html[0]=$this->basket_model->header_basket();
                $html[1]=$this->basket_model->paypal_form();
				$html[2]="<div class='message success'>".$product_details."</div>";

                exit(json_encode($html));
            }
            else
            {
				if (isset($get['product_add']))
				{
					// log reload
						$this->_log_action($node['url'],$product_details,"success");
						$this->_reload($node['url'],$product_details,"success");

				}
				else
				{
					// log reload, events from distributed
						$this->_log_action('basket',"your events were added to the basket","success");
						$this->_reload('basket',"your events were added to the basket","success");
				}
            }
    }

    /* *************************************************************************
         save_basket() - updates the basket quantity values, removing any that are set to 0
            NB - stock issues will then be cauhgt by the page reloade
         @reload the basket page
    */
    public function save_basket()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_basket_start');

        // retrieve the form and prepare array for save
			$post=$this->get_input_vals();
            $pclass=$post['pclass'];
            unset($post['pclass']);
            unset($post['submit']);

        // set up the updated values
            $updates=array();
            foreach ($post as $k=>$v)
            {
                $updates[]=array('rowid'=>$k,'qty'=>$v);
            }

        // save the updates
            $this->cart->update($updates);

        // recalculate the postage
            $this->basket_model->do_postage($pclass);

        // apply the voucher again
            $this->voucher_model->apply_voucher();

		// store cart in user table
            $this->basket_model->basket_to_user();

        // log, reload
            $this->_log_action('basket',"the basket amounts were updated","success");
            $this->_reload('basket',"the basket amounts were updated","success");

        /* BENCHMARK */ $this->benchmark->mark('func_save_basket_end');
    }

    /* *************************************************************************
         update_postage() - responds to the changing of the postage drop down on basket page
         @param $get['pclass_name'] - the postage class to apply to the basket
         @return $html set of values for updating various totals on the page
    */
    public function update_postage()
    {
		// get array
			$vals=$this->get_input_vals();

        // repeat the postage calculation on the cart with the new postage
            $this->basket_model->do_postage($vals['pclass_name']);

        // get totals after this calculation
            $total=$this->basket_model->total();

        // set up the html values for output, each total is used on different bits of the basket page
            $html[0]=number_format($total['postage'],2);
            $html[1]=number_format($total['total'],2);
            $html[2]=number_format($total['voucher'],2);
            $html[3]=number_format($total['total_nv'],2);

        if ($this->input->is_ajax_request())
        {
            exit(json_encode($html));
        }
        else
        {
            // log, reload
                $this->_log_action('basket',"the postage amount was updated","success");
                $this->_reload('basket',"the postage amount was updated","success");
        }
    }
}
