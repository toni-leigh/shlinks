<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>

<?php
    echo "<div id='event' style='position:absolute; top:-1400px;'>";
    echo    $event_form;
    echo "</div>";

    echo "<span id='current_admin'>... managing events for '".$admin_calendar['name']."' ...</span>";

    echo "<div id='admin_calendar'>";
    echo $month_slider['undo'];
    echo $month_slider['calendar'];
    echo "</div>";
?>
