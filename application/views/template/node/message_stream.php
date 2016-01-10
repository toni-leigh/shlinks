<?php
	foreach ($messages as $m)
	{
		$data['m']=$m;
		echo $this->load->view("template/node/message",$data);
	}
?> 