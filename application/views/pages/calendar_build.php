<?php
    $hash=$node_details['validation_hash'];
    $calendar=file_get_contents("http://template.excitedstatelaboratory.com/event/get_calendar/".$node['id']."/".$cgran."/".$focus."/".$hash);  
    echo $calendar;
?>