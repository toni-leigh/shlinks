<?php
    function item_spacer($width,$height=0)
    {
        if (0==$height) $height=$width;
        return "<div style='width:".$width."px;height:".$height."px;float:left;'></div>";
    }