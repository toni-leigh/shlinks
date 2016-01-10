<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<div class='admin_left'>
    <div class='admin_form'>
        <div class='panel'>
            <h2>
                <span id='email_heading' class='ad_heading_text noselect' onclick='close_height("email")'>Email</span>
                <span id='email_show' class='sprite panel_close noselect' onclick='close_height("email")'></span>
            </h2>
            <div id='email_panel' class='panel_details'>
            <?php
                // open the form
                $attr=array(
                    'name'=>'register_form',
                    'id'=>'register_form',
                    'class'=>'form',
                    'autocomplete'=>'off'
                );
                $hidden=array('url'=>uri_string());
                echo form_open('engage/check_credentials',$attr,$hidden);

                // email field
                echo form_label('Email Address:','user_name',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'email',
                    'id'=>'email',
                    'class'=>'form_field',
                    'value'=>$this->user['email']
                );
                echo form_input($attr,'','onblur="check_email()"');
            ?>
            <div id='check_email'></div>
            </div>
        </div>
        <div class='panel'>
            <h2>
                <span id='password_heading' class='ad_heading_text noselect' onclick='close_height("password")'>Password</span>
                <span id='password_show' class='sprite panel_close noselect' onclick='close_height("password")'></span>
            </h2>
            <div id='password_panel' class='panel_details'>
            <?php

                // password field
                echo form_label('Old Password:','old_password',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'old_password',
                    'id'=>'old_password',
                    'class'=>'form_field'
                );
                echo form_password($attr,''); // no set_value on password field, for security

                // password field
                echo form_label('New Password:','password',array('class' => 'form_label'));
                $attr=array(
                    'name'=>'new_password',
                    'id'=>'new_password',
                    'class'=>'form_field'
                );
                echo form_password($attr,''); // no set_value on password field, for security
            ?>
            </div>
        </div>
        <?php
            // submit button
            $attr=array(
                'name'=>'submit',
                'id'=>'register_submit',
                'class'=>'submit'
            );
            echo form_submit($attr,'change credentials');
            echo form_close();
        ?>
        <script type='text/javascript'>
            function check_email()
            {
                var email=$("#email").val();
                $.ajax({
                    type:'GET',
                    url: '/ajax_email/check',
                    dataType: 'json',
                    data: { email : email },
                    success: function (new_html) { $("#check_email").html(new_html); }
                });
            }
        </script>
    </div>
</div>
<div class='admin_instructions'>
    <p>
        To change email address and / or password - enter the new details here
    </p>
    <p>
        You must fill in all fields to make changes - you can just use your old
        password as the new password if you just want to change the email address
    </p>
</div>
