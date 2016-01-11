<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'controllers/universal.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
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
            write_file($this->config->item('backup_path').date('W',time()).".txt", $backup,'w');

        // email the backup as well
            $this->send_email("backups@excitedstatelaboratory.com",
                              "backup@".$this->config->item('full_domain'),
                              "database backup from ".$this->config->item('site_name'),
                              $backup,'BACKUP');

        /* BENCHMARK */ $this->benchmark->mark('func_backup_end');
    }
}
