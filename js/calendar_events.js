/*
 class Node_admin

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/

$(document).ready(function() {
    $("#until_date").datepicker({dateFormat: 'dd-mm-yy'});
    $("#from_date").datepicker({dateFormat: 'dd-mm-yy'});
});

function show_event_panel(year,month,day,event_id,calendar_id)
{
    // hide the calendar functions we don't want clicked
        $('.new_event').css('opacity','0');
        $('.slider_buttons').css('opacity','0');

    // calendar id
        $('#calendar_id').val(calendar_id);

    // event id
        $('#event_id').val(event_id);

    // date panel
        var formatted_date=$.datepicker.formatDate('D d M yy',new Date(year,month-1,day));
        var num_date=$.datepicker.formatDate('dd-mm-yy',new Date(year,month-1,day));
        $('.event_date').html(formatted_date);
        $('#from_date').val(num_date);

    // month date
        $('#month_date').val(''+year+''+add_zero(month)+''+add_zero(day));
        console.log($('#month_date').val());

    // slide in
        show_slider('slide','#event');
}

function add_zero(date_val)
{
    if (date_val<10)
    {
        return "0"+date_val;
    }
    else
    {
        return date_val;
    }
}

function count_events()
{
    var fields=$('#event_form').serialize();

    var field_array=fields.split("&");

    var counts=Array();
    counts[0]=1;
    counts[1]=1;
    counts[2]=1;
    counts[3]=1;

    if ($('#until_date').val()!="")
    {
        var until= $('#until_date').val().split('-');
        var until_date=until[2]+"-"+until[1]+"-"+until[0];
        var diff =  Math.floor(( Date.parse(until_date) - Date.parse(from_date) ) / 86400000);

        counts[0]=diff;
        counts[1]=Math.ceil(diff/7);
        counts[2]=Math.ceil(diff/14);
        counts[3]=Math.ceil(diff/30.43); // the exact average of days in a month over four year cycle;
    }

    //alert('days:'+counts[0]+'weeks:'+counts[1]+'fn:'+counts[2]+'months'+counts[3]);

    // set up array of checkboxes
        for (x=0;x<field_array.length;x++)
        {
            pair=field_array[x].split("=");
            field_array[x]=Array();
            field_array[x]=pair;
            /* alert(field_array[x][0]);
            alert(field_array[x][1]); */
        }

    var full_count=0;

    // count events per day
        var per_day=0;
        for (x=0;x<field_array.length;x++)
        {
            if ('daily'==field_array[x][0].substr(0,5) &&
                'on'==field_array[x][1])
            {
                per_day++;
            }
        }

        if (0==per_day)
        {
            per_day=1;
        }

    // count events per week
        var per=0;
        var per_multiplier=1;
        if ($('#weekly').is(':checked'))
        {
            for (x=0;x<field_array.length;x++)
            {
                if ('weekly'==field_array[x][0].substr(0,6) &&
                    'on'==field_array[x][1])
                {
                    per++;
                }
            }
            per_multiplier=counts[1];
        }
        else if ($('#fortnightly').is(':checked'))
        {
            for (x=0;x<field_array.length;x++)
            {
                if ('fortnightly'==field_array[x][0].substr(0,11) &&
                    'on'==field_array[x][1])
                {
                    per++;
                }
            }
            per_multiplier=counts[2];
        }
        else if ($('#monthly').is(':checked'))
        {
            for (x=0;x<field_array.length;x++)
            {
                if ('monthly'==field_array[x][0].substr(0,7) &&
                    'on'==field_array[x][1])
                {
                    per++;
                }
            }
            per_multiplier=counts[3];
        }

        if (0==per)
        {
            per=1;
        }

    // full count
        //alert('per:'+per+'per_day:'+per_day+'per_multiplier:'+per_multiplier);
        full_count=per*per_day*per_multiplier;

    $('#ecount').html('<strong>approx. '+full_count+/* 'per:'+per+'per_day:'+per_day+'per_multiplier:'+per_multiplier+*/ '</strong>');
}

function show_repeater()
{
    if ($('#show_repeat').is(':checked'))
    {
        $('#repeat_panel_hider,#repeat_panel').animate({'opacity':'1','height':'+=195'},175);
    }
    else
    {
        $('#repeat_panel_hider,#repeat_panel').animate({'opacity':'0','height':'-=195'},175);
    }
}

function focus_repeat_panel(id)
{
    // only allow one of weekly, fortnightly and monthly to be selected
        if ('weekly'==id)
        {
            rp_focus(id,1);
            rp_focus('fortnightly',0);
            rp_focus('monthly',0);
        }
        if ('fortnightly'==id)
        {
            rp_focus('weekly',0);
            rp_focus(id,1);
            rp_focus('monthly',0);
        }
        if ('monthly'==id)
        {
            rp_focus('weekly',0);
            rp_focus('fortnightly',0);
            rp_focus(id,1);
        }

    // switch the right panels on and off
        if ($('#'+id).is(':checked'))
        {
            rp_focus(id,1);
        }
        else
        {
            rp_focus(id,0);
        }

    count_events();
}

function rp_focus(id,focus)
{
    if (1==focus)
    {
        $('#'+id+'_repeats').animate({'opacity':'1'},100);
        $('.'+id+'_repeat_field').removeAttr('disabled');
    }
    else
    {
        $('#'+id+'_repeats').animate({'opacity':'0.6'},100);
        $('.'+id+'_repeat_field').attr('disabled','disabled');
        $('#'+id).attr('checked',false);
    }
}

function toggle_events()
{
    var passed=$('.passed');
    var link=$('.js_show_all_events');

    if (passed.first().hasClass('hidden'))
    {
        passed.removeClass('hidden');
        link.html('[hide passed events]');
    }
    else
    {
        passed.addClass('hidden');
        link.html('[show passed events too]');
    }
}
$('.js_show_all_events').on('click',toggle_events);

$('.js_event_sweep').each(
    function()
    {
        console.log(0);
        var panel=$(this);

        var events={};

        panel.find('.cal_cell').each(
            function()
            {
                console.log(1);
                var cell=$(this);

                var classes=cell.attr('class').split(' ');

                for (x=0;x<classes.length;x++)
                {
                    console.log(2);
                    if (classes[x].indexOf('hl')>-1)
                    {
                        if ('undefined'===typeof events[classes[x]])
                        {
                            events[classes[x]]={
                                'count':1,
                                'hl_class':classes[x]
                            };
                        }
                        else
                        {
                            events[classes[x]].count++;
                        }
                    }
                }
            }
        );

        for (var event_key in events)
        {
            var curr_event=events[event_key];
            var style="background-color:"+$('#'+curr_event.hl_class).first().css('background-color')+";border:"+$('#'+curr_event.hl_class).first().css('border')+";";
            panel.find('.sweep_events').append("<span style='"+style+"opacity:0.75;' class='sweep_event "+curr_event.hl_class+"'>"+curr_event.count+"</span>");
        }

    }
);
