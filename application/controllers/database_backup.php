<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
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
    class Database_backup extends Universal {

    public function __construct()
    {
        parent::__construct();

        // load code igniters db utilities class
            $this->load->dbutil();
    }

    /* *************************************************************************
     backup() - perform a database back-up
    */
    public function backup()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_backup_start');

        // backup your entire database and assign it to a variable
            $backup = $this->dbutil->backup(array('format'=>'txt'));

        // Load the file helper and write the file to your server
            $this->load->helper('file');
            write_file($this->config->item('backup_path'), $backup,'w');

        // email the backup as well
            $this->send_email("backups@excitedstatelaboratory.com",
                              "backup@".$this->config->item('full_domain'),
                              "database backup from ".$this->config->item('site_name'),
                              $backup,'BACKUP');

        /* BENCHMARK */ $this->benchmark->mark('func_backup_end');
    }
}
