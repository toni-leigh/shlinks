<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<?php
    echo "<div id='list' itemscope itemtype='http://schema.org/ItemList'>";

    // list nodes
        echo $this->view('links');
    // close list div
        echo "</div>  ";
?>
