<div class='comment_panels'>
<?php
	foreach ($comments as $comm)
	{
		$data['comm']=$comm;
		echo $this->load->view("template/node/comment",$data);
	}
?>
</div>
<?php
	echo $comment_form;
?>