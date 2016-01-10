<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<?=$this->load->view("template/node/details_heading"); ?>
<div id='messages' class='node_panel messages'>
<?php
	if ($user['user_id']==$node['id'])
	{
		?>
	    <div class='conversations'>
	    	<?php
	    		echo $this->load->view("template/node/conversations");
	    	?>
	    </div>
	    <div class='my_messages message_stream'>
	    	<div class='message_panels'>
	    	<?php
	    		echo $this->load->view("template/node/message_stream");
	    	?>
		    </div>
	    	<?=$message_form; ?>
	    </div>
		<?php
	}
	else
	{
		?>
	    <div class='inner_content message_stream'>
	    	<div class='message_panels'>
	    	<?php
	    		echo $this->load->view("template/node/message_stream");
	    	?>
		    </div>
	    	<?=$message_form; ?>
	    </div>
	    <div class='social_images'>
	        <?=$this->load->view("template/node/connect_buttons"); ?>
	        <?=$this->load->view("template/node/gallery_small"); ?>
	        <?=$this->load->view("template/node/social_media"); ?>
	    </div>
		<?php
	}
?>
</div>
