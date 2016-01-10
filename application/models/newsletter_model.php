<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/node_model.php');
/*
 class Newsletter_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Newsletter_model extends Node_model {

    public function __construct()
    {
        parent::__construct();
    }

    /*
         get_newsletter_form() - gets a form for newsletter sign up
         @return string $newsletter_html - html for a form, or maybe an empty string if a certain type of user is signed in
    */
    public function get_newsletter_form()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_newsletter_form_start');
        $this->load->helper('data');

        $newsletter_html='';

        // only make a newsletter form if users who are not site admins are signed in
        /*if ('super_admin'==$this->user['user_type'] or
            'admin_user'==$this->user['user_type'] or
            'supplier_user'==$this->user['user_type'])
        {
            $newsletter_html.='';
        }
        else
        {*/
            $attr=array(
                'name'=>'newsletter_form',
                'id'=>'newsletter_form',
                'class'=>'form'
            );
            $hidden=array('url'=>uri_string()); // reload url
            $newsletter_html.=form_open('newsletter/signup',$attr,$hidden);

            // email field
                $attr=array(
                    'name'=>'newsletter_email',
                    'id'=>'newsletter_email',
                    'class'=>'form_field',
                    'placeholder'=>'enter your email ...',
                    'value'=>get_value(null,'newsletter_email')
                );
                $newsletter_html.=form_input($attr,'');

            $newsletter_html.=form_input(array('name' => 'phone_number','class' => 'phone_number','style'=>'position:absolute;top:-10000px;'));

            // submit button
                $attr=array(
                    'name'=>'submit',
                    'id'=>'newsletter_submit',
                    'class'=>'checkout submit'
                );
                $newsletter_html.=form_submit($attr,'sign up');

            $newsletter_html.=form_close();
        /*}*/

        /* BENCHMARK */ $this->benchmark->mark('func_get_newsletter_form_end');

        return $newsletter_html;
    }
    /*
         save_email() - saves the users email in the newsletter table
         @param string $email - sign-ups email
         @return
    */
    public function save_email($email)
    {
        /* BENCHMARK */ $this->benchmark->mark('func__start');

        $query=$this->db->select('*')->from('newsletter')->where(array('email'=>$email));
        $res=$query->get();
        $signed_up=$res->result_array();
        if (0==count($signed_up))
        {
            $insert_data=array(
                'email'=>$email
            );
            $this->db->insert('newsletter',$insert_data);
        }

        /* BENCHMARK */ $this->benchmark->mark('func__end');
    }

    /* *************************************************************************
         newsletter_csv() - create a csv file of the newsletter emails
         @param string
         @param numeric
         @param array
         @return
    */
    public function newsletter_csv()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_newsletter_csv_start');

        // get signups
            $query=$this->db->select('*')->from('newsletter')->order_by('sign_up_time');
            $res=$query->get();
            $signups=$res->result_array();

        // open
            $csv="Email\n";

        // all signups
            foreach ($signups as $s)
            {
                $csv.=$s['email']."\n";
            }

        return $csv;

        /* BENCHMARK */ $this->benchmark->mark('func_newsletter_csv_end');
    }
}
