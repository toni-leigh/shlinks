<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
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
