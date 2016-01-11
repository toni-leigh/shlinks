/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/

function update_all(id,type)
{
    if (id.indexOf('name')>=0)
    {
        if (0==check_alpha_num('#'+id))
        {
            $('.'+id).val($('#'+id).val());
            $('.'+id).html($('#'+id).val());
        }
    }
    else
    {
        if (0==check_numeric('#'+id))
        {
            $('.'+id).val($('#'+id).val());
            $('.'+id).html($('#'+id).val());
        }
    }
}

function open_close(move_amount,id)
{
    if (1==$('#'+id+'_process').val())
    {
        $('#mo_'+id).removeClass('month_close').addClass('month_open');
        $('#'+id+'_mes').addClass('red').removeClass('green').html('[events will not be edited]');
        $('#'+id+'_process').val(0);
        $('#'+id+'_month_hide').css('display','none').animate({'height':'-='+move_amount},100);
    }
    else
    {
        $('#mo_'+id).removeClass('month_open').addClass('month_close');
        $('#'+id+'_mes').addClass('green').removeClass('red').html('[edits will be saved]');
        $('#'+id+'_process').val(1);
        $('#'+id+'_month_hide').css('display','').animate({'height':'+='+move_amount},100);
    }
}
