<div class='admin_left'>
    <div class='panel'>
        <h2>
            <span id='threshold_heading' class='ad_heading_text noselect' onclick='open_height("threshold")'>Free Postage Threshold</span>
            <span id='threshold_show' class='sprite panel_open noselect' onclick='open_height("threshold")'></span>
        </h2>
        <div id='threshold_panel' class='panel_closed panel_details'>
            <?php
                echo form_open('postage/save_calc_vals');
            ?>
                <span id='threshold_entry' class='full_screen_width'>
                    <input id='p_thresh' class='form_field' type='text' name='p_thresh' value='<?php echo $postage_threshold; ?>' onkeyup='check_numeric("#p_thresh")' onchange='set_changes()'/>
                </span>
                <input id='thresh_bracket_submit' class='submit' type='submit' name='submit' value='save postages' onclick='unset_changes()'/>
            </form>
        </div>
    </div>
    <div class='panel'>
        <h2>
            <span id='postage_heading' class='ad_heading_text noselect' onclick='close_height("postage")'>Postage Bands</span>
            <span id='postage_show' class='sprite panel_close noselect' onclick='close_height("postage")'></span>
        </h2>
        <div id='postage_panel' class='panel_details'>
            <?php
                echo form_open('postage/save_calc_vals');
            ?>
            <div id='weight_brackets'>
                <span class='band_heading_left'>range values (in grams or items)</span>
                <?php
                    foreach ($classes as $pclass)
                    {
                        echo "<span class='band_heading'>".$pclass['pclass_heading']."</span>";
                    }
                    echo $bracket_updater;
                ?>
            </div>
        </form>
        <script language='JavaScript'>
            var changes_made=0;
            window.onbeforeunload = confirmExit;
            function confirmExit()
            {
                if (1==changes_made)
                {
                    return "You have made changes on the 'edit postage calculation values' panel. Click 'stay on this page' to return to the page and save the changes. Or click 'leave this page' to navigate away and lose the changes";
                }
            }
        </script>
        </div>
    </div>
</div>
<div class='admin_instructions'>
    <p>
        here you set postage based on different bands. the bands refer to weights or quantities of items -
        you will be using either one or the other site wide - it is recommended that weights are used
    </p>
    <p>
        then when you create and edit products you can set a postage calculation value for each product
        or product variation - this could be the weight of the product or the number of items it represents -
        in the case of items a single product would have a value of 1, multipack would have a value above 1
    </p>
    <p>
        when a customer adds products to their basket all of the postage calculation values from the products
        in their basket are added together in order to retrieve the correct postage cost for their basket, based
        on the values defined in the table
    </p>
    <p></p>
    <p>
        the threshold value in the top form will allow free postage above a certain value
    </p>
    <p>
        <span class='strong'>setting this to &pound;0 will offer free postage on all orders !</span>
    </p>
    <p>
        current threshold is <span id='postage_threshold' class='strong'>&pound;<?php echo $postage_threshold; ?></span>
    </p>
</div>



























