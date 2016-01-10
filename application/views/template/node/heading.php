<?php 
	if (isset($current_tab))
	{
		echo "<h1>".$h1.": ".$current_tab; 
	}
	else
	{
		echo "<h1>".$h1; 
	}
	echo $vote_buttons;
	echo "</h1>";
?>