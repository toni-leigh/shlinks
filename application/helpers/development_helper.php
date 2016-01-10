<?php
    /*DEBUG FUNCTIONS*/
    /*
     function that outputs development info coherently
     $heading - some info about where the call was made and a description to embolden above the dev output
     $argument - the actual data to output, ususally a variable value or query string
     $developer_mode - whether or not to output anything - calls are activated page wide or function wide allowing developer info to be switched on / off easily
    */
    function dev_dump($argument,$heading="",$dev=1,$query_trigger=0)
    {
        //print_r($_SERVER);
        //allows the whole lot to be switched off
        $all_off=0;
        //allows every dev dump encountered to be switched on
        $all_on=0;
        if (($dev&&!$all_off)||$all_on)
        {
            if (is_array($argument))
            {
                echo "<div style='width:95%; float:left; position:relative; background-color:#fff; z-index:1000000; margin-bottom:20px; padding:2%; border:1px solid #4242ff; color:#4242ff; border-radius:2px;'>";
                if ($query_trigger)
                {
                    echo "<span class='dd_q_out full_screen_width bold'><strong>".$heading."</strong></span>";
                }
                else
                {
                    echo "<span>ARRAY DUMP - <strong>".$heading."</strong></span><br/><br/>";
                }
                echo "<span class='dd_a_out full_screen_width'>";
                print_array("",$argument);
                echo "</span>";
                echo "</div>";
            }
            else
            {
                if (is_numeric($argument))
                {
                    echo "<div style='width:95%; float:left; position:relative; background-color:#fff; z-index:1000000; margin-bottom:20px; padding:2%;border:1px solid #bf42bf; color:#bf42bf; border-radius:2px;'>";
                    $type='NUMBER';
                }
                else
                {
                    if (is_null($argument))
                    {
                        echo "<div style='width:95%; float:left; position:relative; background-color:#fff; z-index:1000000; margin-bottom:20px; padding:2%;border:1px solid #ff4242; color:#ff4242; border-radius:2px;'>";
                        $type='NULL';
                    }
                    else
                    {
                        echo "<div style='width:95%; float:left; position:relative; background-color:#fff; z-index:1000000; margin-bottom:20px; padding:2%;border:1px solid #428f42; color:#428f42; border-radius:2px;'>";
                        $type='STRING / OTHER';
                    }
                }
                echo "<span>".$type." DUMP - <strong>".$heading."</strong></span><br/><br/>";
                echo "<span style='dd_v_out full_screen_width'>";
                var_dump($argument);
                echo "</span>";
                echo "</div>";
            }
            echo "\n\n";
            if (!$query_trigger) {echo "<span style='width:100%;height:10px;float:left;'></span>";}
        }
    }
    function print_array($title,$array){

        if(is_array($array)){

            echo "\n\n";
            echo $title."<br/>".
            "<pre>";
            print_r($array); 
            echo "\n\n";
            echo "</pre>";

        }else{
             echo $title." is not an array.";
        }
    }
    function dump_globals()
    {
        dev_dump($_POST,"Post Values Array",1);
        dev_dump($_GET,"Get Values Array",1);
        dev_dump($_SESSION["user"],"Signed in user Values Array",1);
        dev_dump($_SESSION,"All Session Values Array",1);
        dev_dump($_FILES,"Files Array",1);
        dev_dump($_COOKIE,"Cookie Array",1);
        //dev_dump($_SERVER,"Server Values Array",1);
        //phpinfo();
    }
?>