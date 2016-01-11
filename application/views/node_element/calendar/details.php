<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<?=$this->load->view("template/node/details_heading"); ?>

<div id='details' class='node_panel details'>

	<?=$this->load->view("template/node/calendar",$calendar); ?>

</div>

<?=$this->load->view("template/node/map"); ?>
