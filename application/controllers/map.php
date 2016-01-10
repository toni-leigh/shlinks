<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2013)
*/
    class Map extends Node {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     lookup_postcode() - takes a postcode and requests the latitude and longitude from google maps API
     @return
    */
    public function lookup_postcode()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_lookup_postcode_start');

        $postcode=$this->get_input_vals();

        $search_code = urlencode($postcode['postcode']);

        $details=json_decode(file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".$search_code."&sensor=false"),true);

        $location=$details['results'][0]['geometry']['location'];

        exit(json_encode(array(0=>$location['lat'],1=>$location['lng'])));

        /* BENCHMARK */ $this->benchmark->mark('func_lookup_postcode_end');
    }
}
