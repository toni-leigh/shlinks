<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class Voucher

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Voucher extends Universal {

    public function __construct()
    {
        parent::__construct();

        // models
			$this->load->model('basket_model');
			$this->load->model('voucher_model');

        // libraries
			$this->load->library('cart');

        // helpers
			$this->load->helper('data_helper');
    }

    /* *************************************************************************
         add_new_voucher_type() - save a new voucher type - these are used to populate the
			voucher drop down for creating actual vouchers
    */
    public function add_new_voucher_type()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_new_voucher_start');

		// prepare $post
			$post=$this->get_input_vals();

		// save
			$this->voucher_model->save_voucher_type($post);

		//log, reload
			$this->_log_action("voucher-definition","the voucher type was added","success");
			$this->_reload("voucher-definition","the voucher type was added","success");

        /* BENCHMARK */ $this->benchmark->mark('func_add_new_voucher_end');
    }

    /* *************************************************************************
         add_new_voucher() - saves an actual voucher of a specific type with a few voucher level
			variables set
         @return
    */
    public function add_new_voucher()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_new_voucher_start');

		// prepare $post
			$post=$this->get_input_vals();

		// save the voucher
			$message=$this->voucher_model->save_voucher($post);

		//log, reload
			$this->_log_action("voucher-definition",$message['text'],$message['pass']);
			$this->_reload("voucher-definition",$message['text'],$message['pass']);


        /* BENCHMARK */ $this->benchmark->mark('func_add_new_voucher_end');
    }

    /* *************************************************************************
     check_voucher() - gets a voucher id when entered by the customer and calls the check voucher function
		the values set are for the basket displays on the site
     @return
    */
    public function check_voucher()
    {
		// get values
			$get=$this->get_input_vals();

		// apply the voucher to the cart
			$ajax_display_message=$this->voucher_model->apply_voucher($get['voucher_id']);

		// recalculate the basket totals for output
			$total=$this->basket_model->total();

		if ($this->input->is_ajax_request())
		{
			// html array with various totals we might need in it
				$html=array(
					0=>$ajax_display_message['message'],
					1=>number_format($total['total'],2),
					2=>number_format($total['postage'],2),
					3=>number_format($total['voucher'],2)
				);
		}
		else
		{
			//log, reload
				$this->_log_action("basket",$ajax_display_message['message'],$ajax_display_message['pass']);
				$this->_reload("basket",$ajax_display_message['message'],$ajax_display_message['pass']);
		}
        exit(json_encode($html));
    }
}
