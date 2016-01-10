/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

function remove_postage(bracket_id,last_id)
{
    /* alert(bracket_id);
    alert('#'+bracket_id+'bracket_row');
    alert($('#'+bracket_id+'bracket_row').html());
    alert($('#5bracket_row').html()); */
    $('#'+bracket_id+'bracket_row').html('');
    $('#'+last_id+'max_value').val('MAX');
    //$('#'+last_id+'max_value').unbind('blur').bind( 'blur', {id:last_id}, function(event) { add_postage(event.data.id); } );
}
function add_postage(bracket_id)
{
    if (!isNaN($('#'+bracket_id+'max_value').val()) &&
        typeof($('#'+(bracket_id+1)+'min_value').val())==='undefined')
    {
        $('.remove'+bracket_id).css('display','none');
        var new_bracket_id=bracket_id+1;
        var new_min_value=parseInt($('#'+bracket_id+'max_value').val())+1;
        // new row
        $.ajax({
            type: 'GET',
            url: '/postage/ajax_postage_classes',
            dataType: 'json',
            data: { new_bracket_id:new_bracket_id , new_min_value:new_min_value },
            success: function (returned_html)
            {
                new_html=$('#new_brackets').html()+returned_html[0];
                $('#new_brackets').html(new_html);
                $('#'+bracket_id+'max_value').val(returned_html[1]);

                // hack over bug, time is of the essence right now, that's my excuse
                for (x=1;x<=new_bracket_id;x++)
                {
                    if (x==new_bracket_id)
                    {
                        $('#'+x+'max_value').val('MAX');
                    }
                    else
                    {
                        $('#'+x+'max_value').val(parseInt($('#'+(x+1)+'min_value').val())-1);
                    }
                }
            }
        });
    }
    else if ('MAX'==$('#'+bracket_id+'max_value').val())
    {
        var c=bracket_id+1;
        while (typeof($('#'+c+'min_value').val())!='undefined')
        {
            $('#'+c+'bracket_row').html('');
            c++;
        }
    }
}
/*
 check numeric function which takes an id and a border colour to revert to if the value is correct
*/
function pcheck_numeric(id,colour)
{
    if (isNaN($('#'+id).val()) &&
        $('#'+id).val()!='MAX')
    {
        $('#'+id).css('border-color','#800000');
        $('#'+id).css('background-color','#f0a0a0');
    }
    else
    {
        $('#'+id).css('border-color',colour);
        $('#'+id).css('background-color','#ffffff');
    }
}
