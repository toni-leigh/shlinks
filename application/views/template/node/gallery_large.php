<?php
	if (count($images)>0)
	{
		//echo "<img class='main_image' src='".thumbnail_url($images[0],940,'s')."' alt=''/>";

		foreach ($images as $i)
		{
			echo "<img class='thumb' src='".thumbnail_url($i,300,'t')."' alt='' width='225'/>";
		}
	}
?>