<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Search

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
*/
    class Search extends Node {

    public function __construct()
    {
        parent::__construct();

        // models
            $this->load->model('search_model');

    }

    /* *************************************************************************
         search_reload() - redirects to a page that includes the search term as part of the URL allowing for search pages to be shared and linked to
         @return void
    */
    public function search_reload()
    {
		// get post
			$post=$this->get_input_vals();

        if (''==$post['search_input'])
		{
            $term='search';
		}
        else
		{
            $term=$post['search_input'];
		}

		// store in the session for the scroller
			$this->session->set_userdata(array('search_term'=>$term));

		$this->_reload("search-results/".urlencode($term),"search performed","success");
    }

    /* *************************************************************************
     search_nodes() - searches the node table for the term provided, creating a list
        of nodes for display
     @param string $term
     @return loads a node instead of returning
    */
    public function search_nodes($term)
    {
        $term=urldecode($term);

        // do search
            $this->data['node_list']=$this->search_model->search($term,$this->config->item('search_nodes'));

        // record search
			$this->search_model->store_search($term,$this->data['node_list']);

        $this->display_node('search-display');
    }
}
