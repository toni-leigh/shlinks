<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<?=$this->load->view("template/node/details_heading"); ?>
<div id='stream' class='node_panel stream'>
    <div class='inner_content'>
        <?=$this->load->view('template/node/stream'); ?>
    </div>
    <div class='social_images'>
        <?=$this->load->view("template/node/connect_buttons"); ?>
        <?=$this->load->view("template/node/vote_buttons"); ?>
        <?=$this->load->view("template/node/gallery_small"); ?>
        <?=$this->load->view("template/node/social_media"); ?>
    </div>
</div>
<?=$this->load->view("template/node/event_list"); ?>
<?=$this->load->view("template/node/map"); ?>
