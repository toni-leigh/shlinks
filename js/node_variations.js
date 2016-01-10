/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/

function filter_variations()
{
    var value=$('#filter').val();
    value=value.replace(/ /g,'_');
    value=value.replace(/'/g,'');
    value=value.toLowerCase();
    $('.filter_row').css('display','none');
    $('.filter_row :input').attr('disabled',true);
    console.log("[id*="+value+"]");
    console.log(JSON.stringify($("[id*="+value+"]").css('display'), null, 4));
    console.log($("[id*="+value+"]").attr('disabled',false));
    $("[id*="+value+"]").css('display', '');
    $("[id*="+value+"] :input").attr('disabled',false);
}

function update_adder(vtype_id,node_id)
{
    if (1==$('#'+vtype_id).val())
    {
        // edit the display
            $('#'+vtype_id).val(0);
            $('#'+vtype_id+'_panel').removeClass('nvtype_selected').addClass('nvtype_unselected');

        // now remove the var_type from the node
        $.ajax({
            type: 'GET',
            url: '/variation/remove_vtype',
            dataType: 'json',
            data: { vtype_id:vtype_id , node_id:node_id },
            success: function () {  }
        });
    }
    else
    {
        // edit the display
            $('#'+vtype_id).val(1);
            $('#'+vtype_id+'_panel').removeClass('nvtype_unselected').addClass('nvtype_selected');

        // now add the var_type to the node
        $.ajax({
            type: 'GET',
            url: '/variation/add_vtype',
            dataType: 'json',
            data: { vtype_id:vtype_id , node_id:node_id },
            success: function () {  }
        });
    }

    // update the adder
        $.ajax({
            type: 'GET',
            url: '/variation/adder',
            dataType: 'json',
            data: { node_id:node_id },
            success: function (new_html)
            {
                $("#nv_adder").html(new_html[0]);
                $("#nv_preview").html(new_html[1]);
            }
        });
}

function update_preview()
{
    var inputs=$("#nvar_adder_form").serialize();
    $.ajax({
        type: 'GET',
        url: '/variation/preview',
        dataType: 'json',
        data: { inputs:inputs },
        success: function (new_html) { $("#nv_preview").html(new_html); }
    });
}
function check_zeros()
{
    var inputs=$("#nvar_adder_form").serialize();
    var input_array=inputs.split('&');
    var zeros=0;
    for (x=0;x<input_array.length;x++)
    {
        var pair=input_array[x].split('=');
        if (0==pair[1])
        {
            zeros=1;
        }
    }
    if (1==zeros)
    {
        var ask=confirm('some of the values in the variation adder fields are not set - either zeros in text boxes or option lists with nothing selected. would you still like to proceed ?');
        return ask;
    }
}
function mark_row(variation_ID)
{
    // remove the current line
    remove_lines(variation_ID);

    // perform checks to find out what the row should display, if depth reflects lack of priority
    if ($("."+variation_ID+"remove").attr("checked"))
        $("#"+variation_ID+"_nvar").addClass("remove_row");
    else
        if (parseInt($("#"+variation_ID+"_stock").val())>parseInt($("#"+variation_ID+"_thresh").val()))
            $("#"+variation_ID+"_nvar").addClass("instock_row");
        else
            $("#"+variation_ID+"_nvar").addClass("outstock_row");
}
function mark_main(variation_ID,last_main)
{
    // get the remove checkboxes displayed right
    if ($(".remove_checkbox").hasClass("hidden")) $(".remove_checkbox").removeClass("hidden");
    $("."+variation_ID+"remove").addClass("hidden");
    $("."+variation_ID+"remove").attr("checked",false);

    // remove the old variation
    remove_lines(main_id);
    mark_row(main_id);

    // then mark the row
    mark_row(variation_ID);

    // set main ready for next mark
    main_id=variation_ID;
}
function remove_lines(variation_ID)
{
    $("#"+variation_ID+"_nvar").removeClass("outstock_row").removeClass("instock_row").removeClass("main_row").removeClass("main_row_out").removeClass("remove_row");
}
function update_preview_vals(vtype_id)
{
    // set the actual output text
        $('.'+vtype_id+'U').html($('#'+vtype_id+'value_add').val());

    // set the hidden value
        $('.'+vtype_id+'U_hid').val($('#'+vtype_id+'value_add').val());

}
function apply_sale()
{
    if (isNaN($('#master_sale').val()) ||
        ''==$('#master_sale').val())
    {
        // do nothing, avoid bad values
    }
    else
    {
        var inputs=$("#variation_editor").serialize();
        var input_array=inputs.split('&');
        var add_val=parseInt($('#master_sale').val());

        $('#sale_applied').val(1);

        for (x=0;x<input_array.length;x++)
        {
            var pair=input_array[x].split('=');
            var id=pair[0].split('_');
            if ('price'==id[1])
            {
                curr=parseFloat($('#'+pair[0]).val());
                new_val=curr;
                if($('#sale_type_pound').is(':checked'))
                {
                    new_val=curr-$('#master_sale').val();
                }
                if($('#sale_type_perc').is(':checked'))
                {
                    temp_perc=curr/100*$('#master_sale').val();
                    new_val=curr-temp_perc;
                }
                $('#'+id[0]+'_sale').val(new_val.toFixed(2));
            }
        }
    }
}
function set_saletype_head(type)
{
    $('#sale_type_heading').html(type);
}
function add_stock()
{
    var inputs=$("#variation_editor").serialize();
    var input_array=inputs.split('&');
    var add_val=parseInt($('#set_stock').val());
    if (isNaN(add_val)) add_val=0;
    for (x=0;x<input_array.length;x++)
    {
        var pair=input_array[x].split('=');
        var id=pair[0].split('_');
        if ('stock'==id[1] &&
            pair[0]!='set_stock')
        {
            curr=parseInt($('#'+pair[0]).val());
            new_val=curr+add_val*1;
            $('#'+pair[0]).val(new_val);
            mark_main(id[0],0);
        }
    }
}
