/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

function check_address_form(payment_processor,id,field_name)
{
    check_empty(id,field_name);
    if ('sagepay'==payment_processor)
    {
    }
}

function check_empty(id,field_name)
{
    if (''==$('#'+id).val())
    {
        $('#'+id+'_error').css('display','block').html(field_name+' must be present');
        $('#'+id).removeClass('address_input_clear').addClass('address_error_input');
    }
}

function copy_address()
{
    $('#bname').val($('#dname').val());check_empty('bname','billing name');
    $('#bhouse').val($('#dhouse').val());check_empty('bhouse','billing house name / number');
    $('#baddress1').val($('#daddress1').val());check_empty('baddress1','billing address 1');
    $('#baddress2').val($('#daddress2').val());check_empty('baddress2','billing address 2');
    $('#btown').val($('#dtown').val());check_empty('btown','billing town');
    $('#bpostcode').val($('#dpostcode').val());check_empty('bpostcode','billing post code');
    $('#bcountry_select').val($('#dcountry_select').val());
}
