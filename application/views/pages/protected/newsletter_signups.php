<?php
    echo "<a href='/newsletter/csv'>download csv</a>";
    echo "<div id='signup_list'>";
    $c=1;
    foreach ($signups as $s)
    {
        echo "<div class='signup_row'>";
        echo    "<span class='signup_email'><span class='signup_count'>".$c.".</span> ".$s['email']."</span>";
        echo    "<span class='signup_time'>".$s['sign_up_time']."</span>";
        echo "</div>";
        
        if (0==$c%10)
        {
            echo "<div class='signup_spacer'>";
            echo "</div>";
        }
        
        $c++;
    }
    echo "</div>";
?>