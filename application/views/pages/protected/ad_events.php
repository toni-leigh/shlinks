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

    echo "<div id='admin_calendar'>";
    echo $month_slider['undo'];
    echo $month_slider['calendar'];
    echo "</div>";
?>
