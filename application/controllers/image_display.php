<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/node.php');
/*
 class Image_display

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
