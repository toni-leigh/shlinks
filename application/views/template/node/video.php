<?=$video_div; ?>

<?php
	if (count($other_videos)>0)
	{
		shuffle($other_videos);

		echo "<a class='another_video' href='/".$other_videos[0]['url']."/details'>another random video ... ".$other_videos[0]['name']."</a>";
	}
?>
