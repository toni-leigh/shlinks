<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Event

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
    class Events extends Node {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('node_model');
        $this->load->helper('date_convert_helper');

        // now
            $this->now=get_now();
    }

    /* *************************************************************************
         get_meta_event() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function get_event_form()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_meta_event_start');

        $this->load->model('events_admin_model');

        $calendar_id=$this->input->get('calendar_id');

        $event_form=$this->events_admin_model->event_form($calendar_id);

        if (1==$this->input->is_ajax_request())
        {
            exit(json_encode($event_form));
        }
        else
        {
            return $event_form;
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_meta_event_end');
    }

    /* *************************************************************************
         save() - get the post and save the event via the model
         @return
    */
    public function save()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_start');

        $this->load->model('events_admin_model');

        // get post
            $post=$this->get_input_vals();

        // save the sequence and get the node if for the reload
            $node_id=$this->events_admin_model->save_new_event_sequence($post);

        // reload
            $this->_log_action("events added","events added","events added ".$post['calendar_id']);
            $this->_reload("event/".$node_id."/edit","event sequence added",'success');

        /* BENCHMARK */ $this->benchmark->mark('func_save_end');
    }

    /* *************************************************************************
         save_sequence() - get the post and save the edited sequence via the model
         @return
    */
    public function save_sequence()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_sequence_start');

        $this->load->model('events_admin_model');

        // get post
            $post=$this->get_input_vals();

        // save the edits to the events array
            $this->events_admin_model->edit_event_sequence($post);

        // reload
            $this->_log_action("event sequence ".$post['event_id']." edited","event sequence ".$post['event_id']." edited","event sequence ".$post['event_id']." edited");
            $this->_reload("event/sequence/".$post['event_id'],"event sequence ".$post['event_id']." edited",'success');

        /* BENCHMARK */ $this->benchmark->mark('func_save_sequence_end');
    }

    /* *************************************************************************
         edit() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function edit($event_id=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_edit_start');

        $this->load->model('events_admin_model');

        $event=$this->node_model->get_node($event_id,'event');

        $this->data['event_form']=$this->events_admin_model->event_form($event['calendar_id'],$event_id);

        $this->data['event_edit_form']=$this->events_admin_model->event_sequence_form($event_id);

		$this->display_node('edit-event');

        /* BENCHMARK */ $this->benchmark->mark('func_edit_end');
    }

    /* *************************************************************************
         delete_single() - delete a single event
         @param int $date_key
         @param int $event_id
         @param int $nvar_id
         @return
    */
    public function delete_single($date_key,$event_id,$nvar_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_single_start');

        $this->load->model('events_admin_model');

        $this->events_admin_model->delete_sequence_event($date_key,$event_id,$nvar_id);

        // reload
            $this->_log_action("single event deleted ".$date_key." ".$event_id." ","single event deleted ".$date_key." ".$event_id." ","single event deleted ".$date_key." ".$event_id." ");
            $this->_reload("event/sequence/".$event_id,"single event deleted ".$date_key." ".$event_id,'success');

        /* BENCHMARK */ $this->benchmark->mark('func_delete_single_end');
    }

    /* *************************************************************************
         delete_sequence() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function delete_sequence($event_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_sequence_start');

        $this->load->model('events_admin_model');

        $event=$this->node_model->get_node($event_id,'event');

        $this->events_admin_model->delete_sequence($event);

        // reload
            $this->_log_action("sequence ".$event['name']." deleted","sequence ".$event['name']." deleted","sequence ".$event['name']." deleted");
            $this->_reload("calendar/".$event['calendar_id']."/events","sequence ".$event['name']." deleted",'success');

        /* BENCHMARK */ $this->benchmark->mark('func_delete_sequence_end');
    }

    /* *************************************************************************
         get_calendar() - this function is called from a distributed position to get a calendar
         @param string
         @param numeric
         @param array
         @return
    */
    public function get_calendar($node_id,$cgran,$focus,$hash)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_calendar_start');

        $this->load->model('events_model');

        $cal=$this->node_model->get_node($node_id,'calendar');

        if ($hash==$cal['validation_hash'])
        {
            exit ($this->events_model->get_calendar($cal,$cgran,$focus));
        }
        else
        {
            exit ('the calendar hash was incorrect - consider contacting customer support for assistance [ 07786 117 638 or Toni Leigh@excitedstatedesign.com ]');
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_calendar_end');
    }
}
