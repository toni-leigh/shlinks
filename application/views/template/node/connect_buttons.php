<div id='friend_buttons'>
    <?php
    	foreach ($connection_buttons as $k=>$b)
    	{
    		if (strlen($b))
    		{
                echo "<div class='button_wrapper'>";
                echo $b;
                echo "</div>";
    		}
    	}
    ?>
</div>