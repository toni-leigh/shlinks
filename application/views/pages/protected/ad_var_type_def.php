<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<div class='admin_left'>
    <div class='panel'>
        <h2>
            <span id='newvar_heading' class='ad_heading_text noselect' onclick='open_height("newvar")'>New Variation Type</span>
            <span id='newvar_show' class='sprite panel_open noselect' onclick='open_height("newvar")'></span>
        </h2>
        <div id='newvar_panel' class='panel_closed panel_details'>
            <?php
            echo form_open('variation/add_type');
            ?>
                <div class='vtv_form'>
                    <input type='hidden' name='vtype_sub'/>
                    <input id='vtype' class='form_field' type='text' name='vtype' value=''/>
                    <input id='vtype_submit' class='vtv_add submit' type='submit' name='submit' value='add variation type'/>
                </div>
            </form>
        </div>
    </div>
    <?php
    foreach ($var_types as $vtype)
    {
        if (!in_array($vtype['var_type_name'],array('price','post calc')))
        {
            $id=$vtype['var_type_id'];
            $name=$vtype['var_type_name'];
            $html=$vtype['html'];
            ?>
            <div class='panel'>
                <h2>
                    <span id='<?php echo $id; ?>_var_heading' class='ad_heading_text noselect' onclick='open_height("<?php echo $id; ?>_var")'><?php echo $name; ?></span>
                    <span id='<?php echo $id; ?>_var_show' class='sprite panel_open noselect' onclick='open_height("<?php echo $id; ?>_var")'></span>
                </h2>
                <div id='<?php echo $id; ?>_var_panel' class='panel_closed panel_details'>
                    <div class='vtv_form'>
                        <input id='<?php echo $id; ?>_new' class='form_field' type='text' value='' onkeyup='check_vartype("#<?php echo $id; ?>_new")'/>
                        <input class='vtv_add submit' value='add new <?php echo $name; ?>' onclick='save_new_vvalue(<?php echo $id; ?>)'/>
                    </div>
                    <div id='<?php echo $id; ?>_vals' class='vtype_vals'>
                        <?php echo $html; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>

<div class='admin_instructions'>
    <p>
        use the top panel to create a new variation type - such as colour, size, length.
    </p>
    <p>
        once you have created the variation type you can then add values to it - such as
        blue, red or green; or 100cm, 200cm and 500cm.
    </p>
    <p>
        once you have created the variation type and values you will then be able to use it
        on your products when you create them. you can come back and add more values later
        should there be any need to expand the range - for example if a new colour is
        manufactured.
    </p>
</div>
