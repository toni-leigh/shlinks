<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Subscribe_model

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
    class Subscribe_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     subscribe_button() -
     @param string $redirect_url - will return the user to exact thing they wanted to view before subscribing
     @param numeric
     @param array
     @return
    */
    public function subscribe_button($redirect_url=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_subscribe_button_start');

        if (isset($this->user['subscribed']) ?  $subscribed=$this->user['subscribed'] : $subscribed=0 );

        if (1==$subscribed)
        {
            $zf="<span id='already_subscribed'>already subscribed</span>";
        }
        else
        {
            $zf="";

            $zf.="<form method='post' action='https://secure.zombaio.com/?".$this->config->item('zsite_id').".".$this->config->item('zrecurr_id').".ZOM'>";
            if (isset($this->user['user_id']))
            {
                // add the user name to the form
                    $zf.="<input type='hidden' name='Username' value='".$this->user['user_name']."'/>";
            }

            // the return URLs
                if (null==$redirect_url ? $redirect="subscribe/approve" : $redirect=$redirect_url );
                $zf.="<input type='hidden' name='return_url_approve' value='http://".$this->config->item('full_domain')."/".$redirect."'/>";
                $zf.="<input type='hidden' name='return_url_decline' value='http://".$this->config->item('full_domain')."/subscribe/decline'/>";
                $zf.="<input type='hidden' name='return_url_error' value='http://".$this->config->item('full_domain')."/subscribe/error'/>";
                $zf.="<input type='hidden' name='site' value='".$this->config->item('site_name')."'/>";

            $zf.="<input type='submit' name='submit' value='subscribe' title='subscribe using Zombaio, the payment provider specifically for adult websites'/>";
            $zf.="</form>";
        }

        return $zf;

        /* BENCHMARK */ $this->benchmark->mark('func_subscribe_button_end');
    }
}
