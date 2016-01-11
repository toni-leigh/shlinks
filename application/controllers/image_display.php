<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Image_display

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Image_display extends Node {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('image_model');
    }

    /* *************************************************************************
     display_image() - retrieves an image and it's node then loads a node to display it
     @param int $image_id - the id of the image to retrieve
     @return
    */
    public function display_image($image_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_display_image_start');

        // retrieve the image
            $this->data['individual_image']=$this->image_model->get_individual_image($image_id);

        // retrieve the node the image belongs too
            if (count($this->data['individual_image']))
            {
                $this->data['image_node']=$this->node_model->get_node($this->data['individual_image']['node_id']);
                if (count($this->data['image_node'])>0)
                {
                    $this->data['image_node']['human_type']=$this->node_model->get_human_type($this->data['image_node']['type']);
                }
                else
                {
                    $this->data['image_node']=null;
                    $this->data['individual_image']=null;
                }
            }
            else
            {
                $this->data['image_node']=null;
            }

        // load the image display node
            $this->display_node('individual_image');

        /* BENCHMARK */ $this->benchmark->mark('func_display_image_end');
    }
}
