<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<div id='page_register'>
<?php
    // open the form
    $attr=array(
        'name'=>'register_form',
        'class'=>'form',
        'autocomplete'=>'off'
    );
    $hidden=array('url'=>uri_string());
    echo form_open('engage/check_register',$attr,$hidden);

    // username field
    echo form_label('User Name:','user_name',array('class' => 'form_label rounded'));
    ?>
        <div id='name_warning'>please double check your username as it can't be changed once it is saved</div>
    <?php
    $attr=array(
        'name'=>'user_name',
        'class'=>'form_field',
        'value'=>get_value(null,'user_name'),
        'autofocus'=>''
    );
    echo form_input($attr,'','onblur="check_username()"');

    ?>
        <div id='check_username'></div>
    <?php

    // email field
    echo form_label('Email Address:','email',array('class' => 'form_label rounded'));
    $attr=array(
        'name'=>'email',
        'class'=>'form_field',
        'value'=>get_value(null,'email')
    );
    echo form_input($attr,'','onblur="check_email()"');

    ?>
        <div id='check_email'></div>
    <?php

    // password field
    echo form_label('Password:','password',array('class' => 'form_label rounded'));
    $attr=array(
        'name'=>'password',
        'class'=>'form_field'
    );
    echo form_password($attr,''); // no set_value on password field, for security

    // submit button
    $attr=array(
        'name'=>'submit',
        'class'=>'submit'
    );
    echo form_submit($attr,'register');
    echo form_close();
?>
</div>
