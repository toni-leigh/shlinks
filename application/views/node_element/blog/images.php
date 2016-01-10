<?=$this->load->view("template/node/details_heading"); ?>
<div id='images' class='node_panel images'>
	<?php
		if (count($images)>0)
		{
			echo "<img class='main_image' src='".thumbnail_url($images[0],960,'gal')."' alt=''/>";

			foreach ($images as $i)
			{
				echo "<img class='thumb' src='".thumbnail_url($i,225,'gal')."' alt=''/>";
			}
		}
	?>
</div>