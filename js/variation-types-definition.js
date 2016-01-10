/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

/* saves a new variation value using ajax then updates the panel */
function save_new_vvalue(var_type_id)
{
    var var_value=$('#'+var_type_id+'_new').val();
    if (check_vartype('#'+var_type_id+'_new'))
    {
        $.ajax({
          url: '/variation/save_var_value',
          dataType: 'json',
          data: { var_type_id:var_type_id , var_value:var_value },
          success: function (new_html)
            {
                $('#'+var_type_id+'_vals').html(new_html);
                $('#'+var_type_id+'_new').val('').focus();
            }
        });
    }
}

/* removes a single var value */
function remove_vvalue(var_value_id)
{
    $.ajax({
      url: '/variation/remove_var_value',
      dataType: 'json',
      data: { var_value_id:var_value_id },
      success: function (new_html) { $("#"+var_value_id).html(''); }
    });
}
