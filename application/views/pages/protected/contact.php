<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<div id='contact_form'>
    <?php
        // helpers
        $this->load->helper('data');
        echo form_open('contact/send_contact');
        echo form_input(array('name' => 'phone_number','style'=>'position:absolute;top:-10000px;'));
        echo form_label('Your contact details (phone, email, both if you like ... ):','contact_details',array('class' => 'form_label rounded'));
        echo form_input(array('name' => 'contact_details','id' => 'contact_details','class' => 'form_field rounded','value'=>get_value(null,'contact_details')),'','onblur="check_empty(\'#contact_details\')" onkeyup="revert_error(\'#contact_details\')"');
        echo form_label('Your message for '.$this->config->item('site_name').':','message',array('class' => 'form_label rounded'));
        echo form_textarea(array('name' => 'message','id' => 'message','class' => 'form_field rounded','value'=>get_value(null,'message')),'','onblur="check_empty(\'#message\')" onkeyup="revert_error(\'#message\')"');

        // submit button
        $attr=array(
            'name'=>'submit',
            'id'=>'contact_submit',
            'class'=>'submit'
        );
        echo form_submit($attr,'send contact');
        echo form_close();
    ?>
</div>
<div id='contact_details'>

</div>
