<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class

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
    class File_upload_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         file_upload_form() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function file_upload_field($salt)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_file_upload_form_start');

        $salt='unique_salt';

        $fuf="";

        //$fuf.="<form>";
        $fuf.="<div id='queue'></div>";
        $fuf.="<input id='file_upload' name='file_upload' type='file' multiple='true'>";
        $fuf.="<a style='position: relative; top: 8px;' href='javascript:$(\"#file_upload\").uploadifive(\"upload\")'>Upload Files</a>";
        //$fuf.="</form>";

        $fuf.="<script type='text/javascript'>";
        $fuf.="$(function() {";
        $fuf.="$('#file_upload').uploadifive({";
        $fuf.="'auto'             : false,";
        $fuf.="'checkScript'      : '/check_exists.php',";
        $fuf.="'formData'         : {";
        $fuf.="'user_id': '".$this->session->userdata('user_id')."',";
        $fuf.="'timestamp' : '".time()."',";
        $fuf.="'token'     : '".md5($salt.time())."'";
        $fuf.="},";
        $fuf.="'queueID'          : 'queue',";
        $fuf.="'uploadScript'     : '/uploadifive.php',";
        $fuf.="'onUploadComplete' : function(file, data) { console.log(data); }";
        $fuf.="});";
        $fuf.="});";
        $fuf.="</script>";

        return $fuf;

        /* BENCHMARK */ $this->benchmark->mark('func_file_upload_form_end');
    }
}
