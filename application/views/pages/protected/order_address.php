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
    $total=$this->basket_model->total();
    echo "<div id='address_panel'>";
    echo form_open('order/initialise/'.$this->config->item('payment_processor'));

    // contact details
        ?> <div id='address_contact_panel' class='address_panel'> <?php
        ?> <div class='address_heading'><h2>Contact Details</h2></div> <?php

        // email field
            echo form_label('Email Address:','email',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'email',
                'id'=>'rmail',
                'class'=>'form_field',
                'value'=>get_value($last_order,'email')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'email\',\'email address\')"');
            ?>
                <span id='email_error' class='address_error'>
                </span>
            <?php

        // phone number
            echo form_label('Delivery Phone:','phone',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'phone',
                'id'=>'phone',
                'class'=>'form_field',
                'value'=>get_value($last_order,'phone')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'phone\',\'phone number\')"');
            ?>
                <span id='phone_error' class='address_error'>
                </span>
            <?php

        ?> </div> <?php

    // delivery address
        ?> <div id='address_delivery_panel' class='address_panel'> <?php
        ?> <div class='address_heading'><h2>Delivery Address</h2></div> <?php

        // delivery house name / number
            echo form_label('Delivery Name:','dname',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'dname',
                'id'=>'dname',
                'class'=>'form_field',
                'value'=>get_value($last_order,'dname')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'dname\',\'delivery name\')"');
            ?>
                <span id='dname_error' class='address_error'>
                </span>
            <?php

        // delivery house name / number
            echo form_label('Delivery House Name / Number:','dhouse',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'dhouse',
                'id'=>'dhouse',
                'class'=>'form_field',
                'value'=>get_value($last_order,'dhouse')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'dhouse\',\'delivery house name / number\')"');
            ?>
                <span id='dhouse_error' class='address_error'>
                </span>
            <?php

        // delivery address 1
            echo form_label('Delivery Address 1:','daddress1',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'daddress1',
                'id'=>'daddress1',
                'class'=>'form_field',
                'value'=>get_value($last_order,'daddress1')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'daddress1\',\'delivery address 1\')"');
            ?>
                <span id='daddress1_error' class='address_error'>
                </span>
            <?php

        // delivery address 2
            echo form_label('Delivery Address 2:','daddress2',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'daddress2',
                'id'=>'daddress2',
                'class'=>'form_field',
                'value'=>get_value($last_order,'daddress2')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'daddress2\',\'delivery address 2\')"');
            ?>
                <span id='daddress2_error' class='address_error'>
                </span>
            <?php

        // delivery town
            echo form_label('Delivery Town:','dtown',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'dtown',
                'id'=>'dtown',
                'class'=>'form_field',
                'value'=>get_value($last_order,'dtown')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'dtown\',\'delivery town\')"');
            ?>
                <span id='dtown_error' class='address_error'>
                </span>
            <?php

        // delivery postcode
            echo form_label('Delivery Post Code:','dpostcode',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'dpostcode',
                'id'=>'dpostcode',
                'class'=>'form_field',
                'value'=>get_value($last_order,'dpostcode')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'dpostcode\',\'delivery post code\')"');
            ?>
                <span id='dpostcode_error' class='address_error'>
                </span>
            <?php

        // delivery country
            ?> <span id='dcountry'> <?php
                echo form_label('Delivery Country:','dcountry',array('class' => 'form_label rounded'));
                echo $dcountry;
            ?> </span> <?php
        ?> </div> <?php

    // copy to billing address

        ?> <span id='copy_address' onclick='copy_address()'> billing address the same as delivery address ? click here </span> <?php

    // billing address
        ?> <div id='address_billing_panel' class='address_panel'> <?php
        ?> <div class='address_heading'><h2>Billing Address</h2></div> <?php

        // billing house name / number
            echo form_label('Billing Name:','bname',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'bname',
                'id'=>'bname',
                'class'=>'form_field',
                'value'=>get_value($last_order,'bname')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'bname\',\'billing name\')"');
            ?>
                <span id='bname_error' class='address_error'>
                </span>
            <?php

        // billing house name / number
            echo form_label('Billing House Name / Number:','bhouse',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'bhouse',
                'id'=>'bhouse',
                'class'=>'form_field',
                'value'=>get_value($last_order,'bhouse')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'bhouse\',\'billing house name / number\')"');
            ?>
                <span id='bhouse_error' class='address_error'>
                </span>
            <?php

        // billing address 1
            echo form_label('Billing Address 1:','baddress1',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'baddress1',
                'id'=>'baddress1',
                'class'=>'form_field',
                'value'=>get_value($last_order,'baddress1')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'baddress1\',\'billing address 1\')"');
            ?>
                <span id='baddress1_error' class='address_error'>
                </span>
            <?php

        // billing address 2
            echo form_label('Billing Address 2:','baddress2',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'baddress2',
                'id'=>'baddress2',
                'class'=>'form_field',
                'value'=>get_value($last_order,'baddress2')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'baddress2\',\'billing address 2\')"');
            ?>
                <span id='baddress2_error' class='address_error'>
                </span>
            <?php

        // billing town
            echo form_label('Billing Town:','btown',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'btown',
                'id'=>'btown',
                'class'=>'form_field',
                'value'=>get_value($last_order,'btown')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'btown\',\'billing town\')"');
            ?>
                <span id='btown_error' class='address_error'>
                </span>
            <?php

        // billing postcode
            echo form_label('Billing Town:','bpostcode',array('class' => 'form_label rounded'));
            $attr=array(
                'name'=>'bpostcode',
                'id'=>'bpostcode',
                'class'=>'form_field',
                'value'=>get_value($last_order,'bpostcode')
            );
            echo form_input($attr,'','onblur="check_address_form(\''.$this->config->item('payment_processor').'\',\'bpostcode\',\'billing post code\')"');
            ?>
                <span id='bpostcode_error' class='address_error'>
                </span>
            <?php

        // billing country
            ?> <span id='bcountry'> <?php
                echo form_label('Billing Country:','bcountry',array('class' => 'form_label rounded'));
                echo $bcountry;
            ?> </span> <?php

        // billing state
            ?> <span id='bstate' style='display:none;'> <?php
                echo form_label('Billing State:','bstate',array('class' => 'form_label rounded'));
                echo $bstate;
            ?> </span> <?php
        ?> </div> <?php

    // submit button
        $attr=array(
            'name'=>'submit',
            'id'=>'address_submit',
            'class'=>'checkout'
        );
        echo form_submit($attr,'go to payment');
    echo form_close();
    echo "</div>";

    ?>

<div id='reassurance_panel'>

    <div id='prod_total_row' class='total_row'>
        <span id='prod_total_heading' class='gt_heading'>Products:</span>
        <span id='pg_total_val' class='gt_val'>
            <div id='prod_grand_total'>
                <?php echo format_price($total['product']); ?>
            </div>
        </span>
    </div>

    <div id='post_total_row' class='total_row'>
        <span id='pos_total_heading' class='gt_heading'>Postage:</span>
        <span id='pg_total_val' class='gt_val'>
            <div id='post_grand_total'>
                <?php echo format_price($total['postage']); ?>
            </div>
        </span>
    </div>

    <div id='total_row' class='total_row'>
        <span id='total_heading' class='gt_heading'>Total:</span>
        <span id='pg_total_val' class='gt_val'>
            <span style='width:7px;float:left;'>&pound;</span>
            <div id='grand_total'>
                <?php echo number_format($total['total'],2); ?>
            </div>
        </span>
    </div>
</div>
