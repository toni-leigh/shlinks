<?=$this->load->view("template/node/details_heading"); ?>
<?php
	foreach ($articles as $panel)
	{
		$this->data['panel']=$panel;
		echo $this->load->view("node_element/blog/list_panel",$this->data);
	}
?>