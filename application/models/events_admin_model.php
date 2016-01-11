<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Events_admin_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Events_admin_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        // helpers
            $this->load->helper('date_convert_helper');
            $this->load->helper('date_helper');

        // models
            $this->load->model('events_model');

        // now
            $this->now=get_now();

        // various arrays of dates and times
            $times=$this->config->item('times');

            if (is_array($times))
            {
                $this->times=$times;
            }
            else
            {
                $this->times=array(
                    array('time'=>'08:00'),array('time'=>'08:30'),array('time'=>'09:00'),array('time'=>'09:30'),
                    array('time'=>'10:00'),array('time'=>'10:30'),array('time'=>'11:00'),array('time'=>'11:30'),
                    array('time'=>'12:00'),array('time'=>'12:30'),array('time'=>'13:00'),array('time'=>'13:30'),
                    array('time'=>'14:00'),array('time'=>'14:30'),array('time'=>'15:00'),array('time'=>'15:30'),
                    array('time'=>'16:00'),array('time'=>'16:30'),array('time'=>'17:00'),array('time'=>'17:30'),
                    array('time'=>'18:00'),array('time'=>'18:30'),array('time'=>'19:00'),array('time'=>'19:30'),
                    array('time'=>'20:00'),array('time'=>'20:30'),array('time'=>'21:00'),array('time'=>'21:30'),
                    array('time'=>'22:00'),array('time'=>'22:30'),array('time'=>'23:00'),array('time'=>'23:30'),
                    array('time'=>'00:00'),array('time'=>'00:30'),array('time'=>'01:00'),array('time'=>'01:30'),
                    array('time'=>'02:00'),array('time'=>'02:30'),array('time'=>'03:00'),array('time'=>'03:30'),
                    array('time'=>'04:00'),array('time'=>'04:30'),array('time'=>'05:00'),array('time'=>'05:30'),
                    array('time'=>'06:00'),array('time'=>'06:30'),array('time'=>'07:00'),array('time'=>'07:30')
                );
            }

            // durations
                $this->durations=array('h'=>'hours','d'=>'days');

            // the day numbers
                $this->days=array(1,2,3,4,5,6,7);

            // the day names in order
                $this->day_names=array('mon','tue','wed','thu','fri','sat','sun');

            // drop down values for monthly repeats
                $this->monthlies=array('on this day','at this position');

            // month numbers convert to names on numeric or string
                $this->month_nums=array(1=>'jan',2=>'feb',3=>'mar',4=>'apr',5=>'may',6=>'jun',7=>'jul',8=>'aug',9=>'sep',10=>'oct',11=>'nov',12=>'dec',
                    '01'=>'jan','02'=>'feb','03'=>'mar','04'=>'apr','05'=>'may','06'=>'jun','07'=>'jul','08'=>'aug','09'=>'sep','10'=>'oct','11'=>'nov','12'=>'dec',
                    'f01'=>'january','f02'=>'february','f03'=>'march','f04'=>'april','f05'=>'may','f06'=>'june','f07'=>'july','f08'=>'august','f09'=>'september','f10'=>'october','f11'=>'november','f12'=>'december'
                );

            // month names convert to strings
                $this->month_names=array('jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06',
                    'jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12'
                );

        // date config
            $this->date_formats=$this->config->item('date_format');

        // calendar
            $this->calendar=array();
            $this->colours=array();
            $this->event_nodes=array();
    }

    /* *************************************************************************
         event_form() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function event_form($calendar_id=0,$event_id='')
    {
        /* BENCHMARK */ $this->benchmark->mark('func_event_form_start');

        $this->load->helper('form_helper');

        // calendar for details
            $this->calendar=$this->node_model->get_node($calendar_id,'calendar');

        // user event default or the event details if this is an add more to sequence
            if (is_numeric($event_id))
            {
                $event=$this->node_model->get_node($event_id,'event');
                $event_default=array_merge($event,json_decode($event['repetition'],true));
                $event_default['colours']=array();
                $event_durations=$this->events_model->get_duration($event_default['duration']);
                $event_default['duration_value']=$event_durations['count'];
                $event_default['duration']=substr($event_durations['unit'],0,1);
                $event_default['repeat']=$event_default['show_repeat'];
            }
            else
            {
                $event_default=$this->config->item('event_default');
            }

        // open form
            $ef=form_open('events/save',array('id'=>'event_form'));

        // hidden calendar and event meta ids etc.
            $ef.="<input id='calendar_id' type='hidden' name='calendar_id' value='".$calendar_id."'/>";
            $ef.="<input id='event_id' type='hidden' name='event_id' value='".$event_id."'/>";

        // basic details
            $ef.="<div id='new_event_header'>";
            $ef.="<div id='balance_spacer'></div>";
            $ef.="<div id='chos_date' class='event_date'></div>";
            $ef.="<div id='close_button' onclick='hide_slider(\"slide\",\"#event\")'></div>";
            $ef.="</div>";
            $ef.="<div id='basic_details_heading' class='evdef_heading'>basic details</div>";

            // name
                $ef.="<div class='basic_heading'>name (a-z, 0-9 or spaces):</div>";
                $ef.="<div class='basic_field'>";
                if (isset($event_default['name']) ? $name=$event_default['name'] : $name='' );
                $ef.="<input id='name' class='form_field' name='name' type='text' value='".$name."' onkeyup='check_alpha_num(\"#name\")'/>";
                $ef.="</div>";

            // price
                $ef.="<div id='event_price'>";
                $ef.="<div class='basic_heading'>price (&pound;&pound;.pp or &pound;&pound;):</div>";
                $ef.="<div class='basic_field'>";
                if (isset($event_default['price']) ? $price=$event_default['price'] : $price=0.00 );
                $ef.="<input id='price' class='form_field' name='price' type='text' value='".$price."' onkeyup='check_numeric(\"#price\")'/>";
                $ef.="</div>";
                $ef.="</div>";

            // spaces
                $ef.="<div class='basic_heading'>spaces:</div>";
                $ef.="<div class='basic_field'>";
                if (isset($event_default['spaces']) ? $spaces=$event_default['spaces'] : $spaces=0 );
                $ef.="<input id='spaces' class='form_field' name='spaces' type='text' value='".$spaces."' onkeyup='check_numeric(\"#spaces\")'/>";
                $ef.="</div>";

            // duration
                $ef.="<div class='basic_heading'>duration:</div>";
                $ef.="<div class='basic_field'>";

                // first the numeric input
                    if (!isset($event_default['duration_value'])
                        or !is_numeric($event_default['duration_value']))
                    {
                        $event_default['duration_value']=1;
                    }

                    $ef.="<input id='duration_value' class='form_field' name='duration_value' type='text' value='".$event_default['duration_value']."' onkeyup='check_numeric(\"#duration_value\")'/>";

                // open select
                    $ef.="<select id='duration' name='duration' class='form_field'>";

                // set duration selection
                    if (!isset($event_default['duration']))
                    {
                        $event_default['duration']='days';
                    }

                // output options
                    foreach ($this->durations as $k=>$v)
                    {
                        if ($event_default['duration']==$k ? $selected=" selected='selected' " : $selected="" );
                        $ef.="<option value='".$k."' ".$selected.">".$v."</option>";
                    }

                // close select and div
                    $ef.="</select>";
                    $ef.="</div>";

                // daily repeat options
                    $ef.="<div id='time_heading' class='basic_heading'>what times (blank for 'all day'):</div>";
                    $ef.="<div id='time_field' class='basic_field'>";
                    $ef.="<div id='daily_repeats' class='repeat_panel'>";
                    $ef.="<div id='daily_times'>";
                    $c=0;
                    foreach ($this->times as $t)
                    {
                        $time=(is_array($t)) ? $t['time'] : $t;

                        if (0==$c%12)
                        {
                            $ef.="<div class='daily_column'>";
                        }
                        if (isset($event_default["daily_".str_replace(':','',$time)]) && 'on'==$event_default["daily_".str_replace(':','',$time)] ? $checked=" checked='checked' " : $checked="" );
                        $ef.="<div class='daily_check'><input id='daily_".str_replace(":","",$time)."' class='daily_repeat_field' name='daily_".str_replace(":","",$time)."' type='checkbox' ".$checked."/>";

                        $label=(isset($t['label'])) ? $t['label'] : $time;

                        $ef.="<label for='daily_".str_replace(":","",$time)."'>".$label."</label></div>";
                        if (11==$c%12 or
                            $c==count($this->times)-1)
                        {
                            $ef.="</div>";
                        }
                        $c++;
                    }
                    $ef.="</div>";
                    $ef.="</div>";
                    $ef.="</div>";

            // colour
                if (count($event_default['colours'])>0)
                {
                    $ef.="<div id='admin_colour'>";
                    $ef.="<div id='colour_heading' class='basic_heading'>admin display colour:</div>";
                    $ef.="<div id='colour_field' class='basic_field'>";
                    $ef.="<div id='colour_strip'>";
                    $ecs=$event_default['colours'];
                    for ($x=0;$x<count($ecs);$x++)
                    {
                        if (isset($ecs[$x]['default']) ? $sel=' checked ' : $sel='' );
                        $ef.="<div class='colour_choice' style='border-color:".$ecs[$x]['border'].";background-color:".$ecs[$x]['fill']."'>";
                        $ef.="<input type='radio' name='colour_choice' value='".$x."' ".$sel."/>";
                        $ef.="</div>";
                    }
                    $ef.="</div>";
                    $ef.="</div>";
                    $ef.="</div>";
                }

            // exclusivity
                $ef.="<div id='exclusivity'>";
                $ef.="<div class='basic_heading'>exclusive:</div>";
                $ef.="<div class='basic_field'>";
                if (isset($event_default["exclusive"]) && 1==$event_default["exclusive"] ? $checked=" checked='checked' " : $checked="" );
                $ef.="<span id='exclusive'><input id='exclusive' type='checkbox' name='exclusive' ".$checked."/><label for='exclusive'>does this event exclude other concurrent bookings if it is booked ?</label></span>";
                $ef.="</div>";
                $ef.="</div>";

        // repetition
            // repeat heading
                $ef.="<div id='repetition_details_heading' class='evdef_heading'>repetition details</div>";
                if (isset($event_default["repeat"]) &&
                    strlen($event_default['repeat']))
                {
                    $checked=" checked='checked' ";
                    $opacity=1;
                    $height=195;
                }
                else
                {
                    $checked="";
                    $opacity=0;
                    $height=0;
                }
                $ef.="<span id='repeat_choice'><input id='show_repeat' type='checkbox' name='show_repeat' onclick='show_repeater()' ".$checked."/><label for='show_repeat'>show repeat panel ?</label></span>";
                $ef.="<div id='repeat_panel_hider' style='float:left;height:".$height."px;opacity:".$opacity.";'>";

            // repeat how often
                $ef.="<div id='repeat_til'>";

                    $udate=reverse_date($this->calendar['until_date'],'-');
                    $ef.="<span class='left'><input id='from_date' class='form_field' name='from_date' type='text' value=''/><span id='date_seperator'> ... to ... </span></span><input id='until_date' class='form_field' name='until_date' type='text' value='".$udate."'/>";

                $ef.="</div>";

            // month date for month repeat
                $ef.="<input id='month_date' type='hidden' name='month_date' value=''/>";

                // repeat or not checkbox
                    if (isset($event_default['repeat']) &&
                        strlen($event_default['repeat']))
                    {
                        $checked=" checked='checked' ";
                        $opacity="opacity:1";
                    }
                    else
                    {
                        $checked="";
                        $opacity="opacity:0";
                    }

                    $ef.="<div id='repeat_panel'>";

                    /* if (!isset($event_default['repeat_type']))
                    {
                        $event_default['repeat_type']='weekly';
                    } */

                // repeat definition
                    // weekly repeat options
                        if (isset($event_default['weekly']) &&
                            1==$event_default['weekly'])
                        {
                            $checked=' checked ';
                            $opacity='opacity:1';
                            $disabled="";
                        }
                        else
                        {
                            $checked='';
                            $opacity='opacity:0.6';
                            $disabled=" disabled='disabled' ";
                        }
                        $ef.="<div id='weekly_repeats' class='repeat_panel' style='".$opacity."'>";
                        $ef.="<input id='weekly' name='weekly' type='checkbox' value='weekly' ".$checked." onclick='focus_repeat_panel(\"weekly\")'/><label  class='strong' for='weekly'>weekly</label>";
                        foreach ($this->days as $d)
                        {
                            if (isset($event_default["weekly_".$d]) && 'on'==$event_default["weekly_".$d] ? $checked=" checked='checked' " : $checked="" );
                            $ef.="<div class='weekly_check'><input id='weekly_".$d."' class='weekly_repeat_field' name='weekly_".$d."' type='checkbox' ".$checked." ".$disabled." onclick='count_events()'/><label for='weekly_".$d."'>".$this->day_names[$d-1]."</label></div>";
                        }
                        $ef.="</div>";

                    // fortnightly repeat options
                        /* if (isset($event_default['fortnightly']))
                        {
                            $checked=' checked ';
                            $opacity='opacity:1';
                            $disabled="";
                        }
                        else
                        {
                            $checked='';
                            $opacity='opacity:0.3';
                            $disabled=" disabled='disabled' ";
                        }
                        $ef.="<div id='fortnightly_repeats' class='repeat_panel' style='".$opacity."'>";
                        $ef.="<input id='fortnightly' name='fortnightly' type='checkbox' value='fortnightly' ".$checked." onclick='focus_repeat_panel(\"fortnightly\")'/><label for='fortnightly'>fortnightly</label>";
                        foreach ($this->days as $d)
                        {
                            if (isset($event_default["fortnightly1_".$d]) && 'on'==$event_default["fortnightly1_".$d] ? $checked=" checked='checked' " : $checked="" );
                            $ef.="<div class='fortnightly_check'><input id='fortnightly1_".$d."' class='fortnightly_repeat_field' name='fortnightly1_".$d."' type='checkbox' ".$checked." ".$disabled." onclick='count_events()'/><label for='fortnightly1_".$d."'>w1 ".$this->day_names[$d-1]."</label></div>";
                        }
                        foreach ($this->days as $d)
                        {
                            if (isset($event_default["fortnightly2_".$d]) && 'on'==$event_default["fortnightly2_".$d] ? $checked=" checked='checked' " : $checked="" );
                            $ef.="<div class='fortnightly_check'><input id='fortnightly2_".$d."' class='fortnightly_repeat_field' name='fortnightly2_".$d."' type='checkbox' ".$checked." ".$disabled." onclick='count_events()'/><label for='fortnightly2_".$d."'>w2 ".$this->day_names[$d-1]."</label></div>";
                        }
                        $ef.="</div>"; */

                    // monthly repeat options
                        if (isset($event_default['monthly']) &&
                            1==$event_default['monthly'])
                        {
                            $checked=' checked ';
                            $opacity='opacity:1';
                            $disabled="";
                        }
                        else
                        {
                            $checked='';
                            $opacity='opacity:0.3';
                            $disabled=" disabled='disabled' ";
                        }
                        $ef.="<div id='monthly_repeats' class='repeat_panel' style='".$opacity."'>";
                        $ef.="<input id='monthly' name='monthly' type='checkbox' value='monthly' ".$checked."onclick='focus_repeat_panel(\"monthly\")'/><label class='strong' for='monthly'>monthly</label>";
                        foreach ($this->monthlies as $m)
                        {
                            if (isset($event_default["monthly_".$m]) && 'on'==$event_default["monthly_".$m] ? $checked=" checked='checked' " : $checked="" );
                            $ef.="<div class='monthly_check'><input id='monthly_".$m."' class='monthly_repeat_field' name='monthly_".$m."' type='checkbox' ".$checked." ".$disabled." onclick='count_events()'/><label for='monthly_".$m."'>".$m."</label></div>";
                        }
                        $ef.="</div>";

                    $ef.="</div>";

                $ef.="</div>";

        // submit button
            $ef.="<span class='full_width'><input id='event_submit' class='submit' name='submit' type='submit' value='save event(s)'/></span>";

        // close form
            $ef.="</form>";

        return $ef;

        /* BENCHMARK */ $this->benchmark->mark('func_event_form_end');
    }

    /* *************************************************************************
         event_sequence_form() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function event_sequence_form($event_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_event_sequence_form_start');

        // get event meta
            $event=$this->node_model->get_node($event_id,'event');

        // get calendar details
            $calendar_basic=$this->node_model->get_node($event['calendar_id'],'calendar');

            $query=$this->db->select('*')->from('calendar')->where(array('node_id'=>$event['calendar_id']));
            $res=$query->get();
            $calendar=$res->row_array();

            $dates=json_decode($calendar['event_json'],true);

        // output variable
            $esf='';
            //$esf.="<span id='current_admin'>... editing event '".$event['name']."' on calendar '".$calendar_basic['name']."' ...</span>";

        // open form
            $esf.="<div id='event_sequence_form'>";
            $esf.=form_open('events/save_sequence');
            $esf.="<input type='hidden' name='event_id' value='".$event_id."'/>";

            $day_breaker=0;
            $last_day=0;
			$last_month=0;
			$last_year=0;

        // a top row of boxes for the mass editing of events
            // warning message for mass edit
                $esf.="<div id='eheading_boxes'>";
                $esf.="<span id='ewarning'>the mass edit boxes (dark bar) at the top of this form will effect every event in the sequence DO NOT use the 'spaces' box unless you are correcting an event sequence directly after creation as this may over write spaces at events for which bookings have already been processed</span>";
                $esf.="<span id='ewarning'>only the open months (those that are showing all their events on the screen) will be saved when save is clicked</span>";
                $esf.="<span id='ewarning'>if you want to open several months it is easier to start at the bottom of the list and work up opening them than the other way round</span>";
                $esf.="</div>";

            // button for add new sequence
                $esf.="<script src='/js/calendar_events.js'></script>";
                $new_sequence_start=explode('-',date('Y-m-d',strtotime($event['last_in_sequence'])+(60*60*24)));
                $esf.="<span id='add_more_to_sequence' onclick='show_event_panel(".$new_sequence_start[0].",".$new_sequence_start[1].",".$new_sequence_start[2].",".$event['node_id'].",".$event['calendar_id'].")'>add more events to sequence</span>";

            // show delete buttons
                $esf.="<span id='show_deletes' onclick='show_deletes()'>show delete buttons</span>";
                $esf.="<script type='text/javascript'>";
                $esf.="if (window.focus)";
                $esf.="{";
                $esf.="     function show_deletes()";
                $esf.="     {";
                $esf.="          $('.erow_delete').css('visibility','visible');";
                $esf.="          $('#show_deletes').html('hide delete buttons').attr('onclick','').bind('click',function(){ hide_deletes(); });";
                $esf.="     }";
                $esf.="     function hide_deletes()";
                $esf.="     {";
                $esf.="          $('.erow_delete').css('visibility','hidden');";
                $esf.="          $('#show_deletes').html('show delete buttons').attr('onclick','').bind('click',function(){ show_deletes(); });";
                $esf.="     }";
                $esf.="}";
                $esf.="</script>";

            // mass edit boxes
                $esf.="<div id='master_erow' class='erow'>";
                $esf.="<span class='erow_date'>&nbsp;</span>";
                $esf.="<span class='erow_time'>&nbsp;</span>";
                $esf.="<span class='erow_name'><input id='ename' class='ename_master form_field' type='text' value='".$event['name']."' onkeyup='update_all(\"ename\",\"master\")'/></span>";
                $esf.="<span class='erow_price'><span class='epound'>&pound;</span><input id='eprice' class='eprice_master form_field' type='text' value='' onkeyup='update_all(\"eprice\",\"master\")'/></span>";
                $esf.="<span class='erow_spaces'><input id='espaces' class='espaces_master form_field' type='text' value='' onkeyup='update_all(\"espaces\",\"master\")'/></span>";
                $esf.="<a href='/events/delete_sequence/".$event_id."'><span class='erow_delete'>delete sequence</span></a>";
                $esf.="</div>";

        // all the nvars in one array accesible by nvar_id as the key
            // get a where in array by going over all events
                $where_in=array();
                foreach ($dates as $date=>$events)
                {
                    foreach ($events['e'] as $eid=>$edetails)
                    {
                        if ($eid==$event_id)
                        {
                            foreach ($edetails['t'] as $t)
                            {
                                $where_in[]=$t['nv'];
                            }
                        }
                    }
                }

            // make the query
                if (count($where_in)>0)
                {
                    $query=$this->db->select('*')->from('nvar')->where_in('nvar_id',$where_in);
                    $res=$query->get();
                    $nvars=$res->result_array();
                }
                else
                {
                    $nvars=array();
                }

            // iterate over and set as keys
                $nvar_array=array();
                foreach ($nvars as $nvar)
                {
                    $nvar_array[$nvar['nvar_id']]=$nvar;
                }

        // the node id for this event
            foreach ($dates as $date=>$events)
            {
                foreach ($events['e'] as $eid=>$edetails)
                {
                    if ($eid==$event_id)
                    {
                        $esf.="<input type='hidden' name='node_id' value='".$edetails['id']."'/>";
                        break 2;
                    }
                }
            }

        // the dates and events
            foreach ($dates as $date=>$events)
            {
				// break current cell key into numerics
					$key_date[0]=substr($date,0,4);
					$key_date[1]=substr($date,4,2);
					$key_date[2]=substr($date,6,2);

                    if ($key_date[1]==$this->now['month'] &&
                        $key_date[0]==$this->now['year'])
                    {
                        $current_month=1;
                    }
                    else
                    {
                        $current_month=0;
                    }

				// a new year means a new panel div
					if ($last_year!=$key_date[0])
					{
                        // open year
                            $esf.="<div id='eedit_year_".$key_date[0]."' class='eedit_year'>";

						// heading
							$esf.="<span class='eedit_yheading'>".$key_date[0]."</span>";
                            // <span class='sum_data'>".$yspaces." total spaces [".$ysold." sold]</span>

                        // counters
                            $yspaces=0;
                            $ysold=0;

                        // all months
                            $all_months='';
					}

				// a new month means a new month div
					if ($last_month!=$key_date[1])
					{
                        // stops the day breaker from appearing just after a month
                            $day_breaker=1;

                        // counters
                            $month_events=0;
                            $spaces=0;
                            $sold=0;

                        // used to remove the submit button from past months
                            $month_live=0;

                        // month event string
                            $mon='';
					}

                // output the events for this month
                    foreach ($events['e'] as $eid=>$edetails)
                    {
                        if ($eid==$event_id)
                        {
                            // add a day break if necessary
                                if ($last_day!=$key_date[2] &&
                                    count($edetails['t']>1))
                                {
                                    if (0==$day_breaker)
                                    {
                                        $mon.="<div class='erow_break'>";
                                        $mon.="</div>";
                                    }
                                    $day_breaker=0;
                                }

                            // output row for each actual event by time
                                $tc=0;

                                foreach ($edetails['t'] as $t)
                                {
                                    // counters
                                        $month_events++;
                                        $spaces+=$t['s'];
                                        $sold+=$nvar_array[$t['nv']]['sold'];

                                    // only output changable stuff if they are beyond the current date
                                    // past events are not editable but should be viewable
                                        if ($date<$this->now['numeric'])
                                        {
                                            // open row
                                                $mon.="<div class='row_past'>";

                                            // date and time
                                                $mon.="<span class='erow_date'>".format_date($key_date[0].'-'.$key_date[1].'-'.$key_date[2],'D d-m-Y')."</span>";
                                                if ('d'==$t['t'] ? $tm='all day' : $tm=substr($t['t'],0,2).':'.substr($t['t'],2,2) );
                                                $mon.="<span class='erow_time'>".$tm."</span>";

                                            // date key for skipping this lot on save
                                                $mon.="<input type='hidden' name='".$t['nv']."_date_key' value='".$date."'/>";

                                            // all these values are just next output once time has passed
                                                $mon.="<span class='ent'/>".$edetails['n']."</span>";
                                                $mon.="<span class='epound'>&pound;</span><span class='ept erp_past'/>".$edetails['p']."</span>";
                                                $mon.="<span class='est erow_spaces ersp_past'>".$t['s']."</span>";
                                                $mon.="<span class='erow_sold ers_past'>".$nvar_array[$t['nv']]['sold']." sold</span>";
                                        }
                                        else
                                        {
                                            // open row
                                                $mon.="<div class='erow'>";

                                            // hidden fields hold the array key sequence for this item
                                                $mon.="<input type='hidden' name='".$t['nv']."_date_key' value='".$date."'/>";
                                                $mon.="<input type='hidden' name='".$t['nv']."_time_key' value='".$tc."'/>";

                                            // visible fields, editable and for information
                                                // date and time
                                                    $mon.="<span class='erow_date'>".format_date($key_date[0].'-'.$key_date[1].'-'.$key_date[2],'D d-m-Y')."</span>";
                                                    if ('d'==$t['t'] ? $tm='all day' : $tm=substr($t['t'],0,2).':'.substr($t['t'],2,2) );
                                                    $mon.="<span class='erow_time'>".$tm."</span>";

                                                // master field for this day then all the time fields
                                                    if (0==$tc)
                                                    {
                                                        // master edit
                                                            $mon.="<span class='erow_name'><input id='".$date."_name' class='ename form_field' name='".$t['nv']."_name' type='text' value='".$edetails['n']."' onkeyup='update_all(\"".$date."_name\",\"sub\")'/></span>";
                                                            $mon.="<span class='erow_price'><span class='epound'>&pound;</span><input id='".$date."_price' class='eprice form_field' name='".$t['nv']."_price' type='text' value='".$edetails['p']."' onkeyup='update_all(\"".$date."_price\",\"sub\")'/></span>";
                                                    }
                                                    else
                                                    {
                                                        // text output when not the master for this day
                                                            $mon.="<span class='ent ename ".$date."_name'/>".$edetails['n']."</span>";
                                                            $mon.="<span class='epound'>&pound;</span><span class='ept eprice ".$date."_price erp_past'/>".$edetails['p']."</span>";

                                                        // hiddens for saving the data
                                                            $mon.="<input class='ename ".$date."_name' name='".$t['nv']."_name' type='hidden' value='".$edetails['n']."'/>";
                                                            $mon.="<input class='eprice ".$date."_price' name='".$t['nv']."_price' type='hidden' value='".$edetails['p']."'/>";
                                                    }

                                                // spaces are always displayed as inputs as they are individually editable
                                                    $mon.="<span class='erow_spaces'><input id='".$t['nv']."_spaces' class='espaces form_field' name='".$t['nv']."_spaces' type='text' value='".$t['s']."' onkeyup='check_numeric(\"#".$t['nv']."_spaces\")'/></span>";
                                                    $mon.="<span class='erow_sold'>".$nvar_array[$t['nv']]['sold']." sold</span>";

                                                // delete button
                                                    $mon.="<a href='/events/delete_single/".$date."/".$event_id."/".$t['nv']."'><span class='erow_delete'>delete</span></a>";

                                            // only increment for visible events
                                                $tc++;

                                            // month live
                                                $month_live=1;
                                        }

                                    $mon.="</div>";
                                }
                        }
                    }

				// output the whole month on the last day of the month when we reach it
					if ($key_date[2]==days_in_month($key_date[1],$key_date[0]))
					{
                        // year counters
                            $ysold+=$sold;
                            $yspaces+=$spaces;

                        // only output if something is happening
                            if ($month_events>0)
                            {
                                // month append for ids
                                    $append=$key_date[0]."-".$key_date[1];
                                    $append_num=str_replace('-','',$append);

                                // current month or not
                                    if (1==$current_month)
                                    {
                                        $style='';
                                        $oc_class='month_close';
                                        $message='edits will be saved';
                                        $start_colour=' green ';
                                    }
                                    else
                                    {
                                        $style='display:none;';
                                        $oc_class='month_open';
                                        $message='events will not be edited';
                                        $start_colour=' red ';
                                    }

                                // open month
                                    $esf.="<div id='eedit_month_".$append."' class='eedit_month'>";

                                // process value will only save if set
                                    $esf.="<input id='".$append_num."_process' class='process' type='hidden' name='".$append_num."_process' value='".$current_month."'/>";

                                // heading
                                    $move_amount=$month_events*34;
                                    $esf.="<span class='eedit_mheading'><span id='mo_".$append_num."' class='".$oc_class."' onclick='open_close(".$move_amount.",".$append_num.")'></span>".$this->month_nums[$key_date[1]]."<span id='".$append_num."_mes' class='mes ".$start_colour."'>[".$message."]</span><span class='sum_data'>".$spaces." total spaces [".$sold." sold]</span></span>";

                                // month events
                                    $esf.="<div id='".$append_num."_month_hide' class='month_hide' style='".$style."'>";
                                    $esf.=$mon;

                                // also put a submit button here for convenience
                                    if ($month_events>0 &&
                                        1==$month_live)
                                    {
                                        $esf.="<input id='event_sequence_submit' class='submit' type='submit' name='submit' value='save ALL events VISIBLE on the screen'/>";
                                    }

                                // close month hide div
                                    $esf.="</div>";

                                // close month
                                    $esf.="</div>";
                            }
					}

				// end of the year means close the year div
					if (($key_date[2]==days_in_month(12,$key_date[0]) && 12==$key_date[1]) or
						$date==$calendar['until_date'])
					{
						$esf.="</div>";
					}

				// new month and year checkers set
					$last_day=$key_date[2];
					$last_month=$key_date[1];
					$last_year=$key_date[0];
            }

        // close form
            $esf.="</form>";
            $esf.="</div>";

        return $esf;

        /* BENCHMARK */ $this->benchmark->mark('func_event_sequence_form_end');
    }

    /* *************************************************************************
         delete_sequence_event() - removes one event from a sequence
         @param int $date_key
         @param int $event_id - keys for the array
         @param int $nvar_id - the nvar to remove from the nvar table (and used to check we delete the correct t)
         @return
    */
    public function delete_sequence_event($date_key,$event_id,$nvar_id)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_event_start');

        // get event
            $event=$this->node_model->get_node($event_id,'event');

        // get calendar
            $calendar=$this->node_model->get_node($event['calendar_id'],'calendar');

        // save undo, put the date of the deleted event into the event name
            $this->save_undo_calendar($calendar['id'],'delete sequence event '.date_key_to_human($date_key),$event['name']);

        // get json into php array
            $events_json=json_decode($calendar['event_json'],true);

        // find and remove the event
            $day_event_array=array();
            if (isset($events_json[$date_key]['e'][$event_id]['t']))
            {
                $day_event_array=$events_json[$date_key]['e'][$event_id]['t'];
            }

            foreach ($day_event_array as $time_key=>$details)
            {
                if ($details['nv']==$nvar_id)
                {
                    unset($events_json[$date_key]['e'][$event_id]['t'][$time_key]);
                    array_values($events_json[$date_key]['e'][$event_id]['t']);
                }
            }

        // remove the entire event if there are no more time left
            if (0==count($events_json[$date_key]['e'][$event_id]['t']))
            {
                unset($events_json[$date_key]['e'][$event_id]);
            }

        // remove the nvar
            $this->db->delete('nvar',array('nvar_id'=>$nvar_id));

        // save the calendar
            $update_data = array(
                'event_json' =>json_encode($events_json)
            );

            $this->db->where('node_id', $calendar['id']);
            $this->db->update('calendar', $update_data);


        /* BENCHMARK */ $this->benchmark->mark('func_delete_event_end');
    }

    /* *************************************************************************
         delete_sequence() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function delete_sequence($event)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_delete_sequence_start');

        // get calendar
            $calendar=$this->node_model->get_node($event['calendar_id'],'calendar');

        // save undo, put the date of the deleted event into the event name
            $this->save_undo_calendar($calendar['id'],"delete sequence",$event['name']);

        // get json into php array
            $events_json=json_decode($calendar['event_json'],true);

        // remove from json
            foreach ($events_json as $date=>$events)
            {
                if (isset($events_json[$date]['e'][$event['id']]))
                {
                    unset($events_json[$date]['e'][$event['id']]);
                }
            }

        // delete also the nvar that is associated with it
            $this->db->delete('nvar',array('node_id'=>$event['id']));

        // save the calendar
            $update_data = array(
                'event_json' =>json_encode($events_json)
            );

            $this->db->where('node_id', $calendar['id']);
            $this->db->update('calendar', $update_data);

        /* BENCHMARK */ $this->benchmark->mark('func_delete_sequence_end');
    }

    /* *************************************************************************
         save_undo_calendar() - saves the calendar event sequence into the undo table in case retrieval in required
         @param int $calendar_id - the calendar whose undo history is being edited
         @param string $trigger - the function / user operation that triggered the save
         @param string $event_name - the name of the event, defaults to NA in case it isn't applicable, such as if it is an undo itself that triggers
         @return
    */
    public function save_undo_calendar($calendar_id,$trigger,$event_name='NA')
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_undo_calendar_start');

        // the time of the trigger to save an undo operation
            $df=$this->config->item('date_format');
            $now=date($df['date'].' '.$df['time'],time());

        // the sequence number
            $query=$this->db->select('step_sequence')->from('calendar_undo')->where(array('calendar_id'=>$calendar_id));
            $res=$query->get();
            $undo_sequence=$res->result_array();

            $step=count($undo_sequence)+1;

        // get the current calendar event list
            $query=$this->db->select('*')->from('calendar')->where(array('node_id'=>$calendar_id));
            $res=$query->get();
            $calendar=$res->row_array();

            $events=$calendar['event_json'];

        // trigger description, so user can see what they are undoing back to
            $description=$now.' -- '.$trigger.' -- '.$event_name;

        // save this into the undo
            $insert_data=array(
                'calendar_id'=>$calendar_id,
                'step_sequence'=>$step,
                'description'=>$description,
                'undo_string'=>$events
                );
            $this->db->insert('calendar_undo',$insert_data);

        // now delete any in the undo history older than config number of steps
            $this->db->delete('calendar_undo',array('calendar_id'=>$calendar_id,'step_sequence <'=>($step-$this->config->item('undo_steps'))));

        /* BENCHMARK */ $this->benchmark->mark('func_save_undo_calendar_end');
    }

    /* *************************************************************************
         retrieve_undo_calendar() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function retrieve_undo_calendar()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_retrieve_undo_calendar_start');

        // save undo

        // local variable for the current sequence (the one to be over-written)

        // retrieve the chosen undo position event sequence

        /*

          CALENDAR BASED ITERATION !!!!

            // iterate over the retrieved sequence comparing it to the current sequence
            // !!!! NB all this iteration is to fix nvars

            // events being re-instated, present in retrieved, not in current
            //  if we find an event in the retrieved sequence which is not present in the current:
            //  then create a new nvar, using the sequence nvar_id (not auto-increment)
            // events being reset (they exist in both calendars)
            //  update the nvars that we find in the retrieved with the retrieved details

            // iterate over the current sequence comparing it to the retrieved sequence

            // events being removed, present in current, but not in retrieved
            // if the event in the current sequence is not in the retrieved sequence:
            //  remove the corresponsing nvar based on the nvar_id found in the current sequence

            // end iteration

        */

        /* BENCHMARK */ $this->benchmark->mark('func_retrieve_undo_calendar_end');
    }

    /* *************************************************************************
         save_new_event_sequence() - saves a new sequence from the event definition form
         @param array $post - the contents of the form which defined the event
         @return $node_id - the node id of the created event for the reload and continuation to the next stage
    */
    public function save_new_event_sequence($post)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_save_new_event_sequence_start');

        // get the calendar and days
            $calendar=$this->node_model->get_node($post['calendar_id'],'calendar');
            $calendar_days=json_decode($calendar['event_json'],true);

        // save the calendar undo
            $this->save_undo_calendar($calendar['id'],'new sequence',$post['name']);

        // do the dates
            // from date
                if (''==$post['from_date'])
                {
                    $from_date="10000000";
                }
                else
                {
                    $from_date=str_replace('-','',reverse_date($post['from_date'],'-'));
                }

            // until date
                if (''==$post['until_date'])
                {
                    // default end of the calendar
                        $year=date('Y',time())+$this->config->item('years_ahead')-1;
                        $until_date=$year."1231";
                }
                else
                {
                    // else get the until date from post
                        $until_date=str_replace('-','',reverse_date($post['until_date'],'-'));
                }

            // until date is equal to from date if there is no repeat set
                if (!isset($post['show_repeat']) or
                    (isset($post['show_repeat']) && !isset($post['weekly']) &&
                                                    !isset($post['fortnightly']) &&
                                                    !isset($post['monthly'])))
                {
                    $until_date=$from_date;
                }

        // process
            // save new node if applicable
                if (is_numeric($post['event_id']))
                {
                    $node_id=$post['event_id'];
                }
                else
                {
                    // save the event as a new node
                        $vals=$post;
                        $vals['id']='';
                        $vals['node_html']='';
                        unset($vals['event_id']);

                    // wrap up the repetition data as json and remove from the $vals array
                        $repetition=array();
                        foreach ($post as $k=>$v)
                        {
                            if ("daily_"==substr($k,0,6) or
                                "weekly"==substr($k,0,6) or
                                "fortnightly"==substr($k,0,11) or
                                "monthly"==substr($k,0,7))
                            {
                                $repetition[$k]=$v;
                                unset($vals[$k]);
                            }
                        }

                        $vals['repetition']=json_encode($repetition);

                    // set some values for the db table
                        $vals['duration']=$vals['duration_value'].$vals['duration'];
                        $vals['from_date']=substr($from_date,0,4).'-'.substr($from_date,4,2).'-'.substr($from_date,6,2);
                        $vals['until_date']=substr($until_date,0,4).'-'.substr($until_date,4,2).'-'.substr($until_date,6,2);

                    // unset some $vals that dont have a matching db column
                        unset($vals['duration_value']);
                        unset($vals['month_date']);

                        if (isset($vals['show_repeat']) ? $vals['show_repeat']=1 : $vals['show_repeat']=0 );
                        if (isset($vals['exclusive']) ? $vals['exclusive']=1 : $vals['exclusive']=0 );

                    // actually save
                        $node_id=$this->node_admin_model->node_save($vals,'event');
                }

            // get the day set array
                $day_events=array();
                $c=0;
                foreach ($post as $k=>$v)
                {
                    if ("daily_"==substr($k,0,6))
                    {
                        $day_events[$c]=array(
                            't'=>substr($k,6,2).substr($k,8,2),
                            's'=>$post['spaces']
                        );
                        $c++;
                    }
                }

            // create a default day event if no time(s) selected
                if (0==count($day_events))
                {
                    $day_events[]=array(
                        't'=>'d',
                        's'=>$post['spaces']
                    );
                }

            // counters - used by fortnightly
                $day_count=1;
                $week_count=1;

            // month position to match up with the right cell value for the month day position, ie 3rd wednesday etc.
                $month_date=substr($post['month_date'],6,2);
                $month_position="";
                if ($month_date<=7) $month_position="1";
                elseif ($month_date>=8 && $month_date<=14) $month_position="2";
                elseif ($month_date>=15 && $month_date<=21) $month_position="3";
                elseif ($month_date>=22 && $month_date<=28) $month_position="4";
                elseif ($month_date>=29) $month_position="5";

                $rform_mdate=substr($post['month_date'],0,4).'-'.substr($post['month_date'],4,2).'-'.substr($post['month_date'],6,2);

                $month_position.=" ".format_date($rform_mdate,'N');

            // exclusivity
                if (isset($post['exclusive']) ? $exclusive=1 : $exclusive=0 );

            // event durations array used to store current events at any point in the loop
            // this is only used by the exclusive event logic though it is run for all event adds
            // as we need to ensure that any existing exclusives effect the added event
                $event_durations=array();

            foreach ($calendar_days as $date=>$day_details)
            {
                // add the key array to the value
                    $day_details['x']=array();

                if ($date>=$from_date &&
                    $date<=$until_date)
                {

                    // now we need to check that the cell we are looking at fits the repeat data
                        // always add events on the chosen date, otherwise don't add unless the date passes repeat checks
                        // also we don't add on this day if any of the repeats are set because the repeats define the days to place it
                            if (!isset($post['weekly']) &&
                                !isset($post['fortnightly']) &&
                                !isset($post['monthly']))
                            {
                                $add_events=1;
                            }
                            else
                            {
                                $add_events=0;
                            }

                        // repeat check the post values against the date of this iteration
                            if (isset($post['weekly']))
                            {
                                if (isset($post['weekly_'.$day_details['dn']]))
                                {
                                    $add_events=1;
                                }
                            }
                            elseif (isset($post['fortnightly']))
                            {
                                // only if the day name is correct every other week
                                // week count
                                    if (0==$day_count%7)
                                    {
                                        $week_count++;
                                    }

                                // check which week
                                    if (1==$week_count%2)
                                    {
                                        if (isset($post['fortnightly1_'.$day_details['dn']]))
                                        {
                                            $add_events=1;
                                        }
                                    }
                                    else
                                    {
                                        if (isset($post['fortnightly2_'.$day_details['dn']]))
                                        {
                                            $add_events=1;
                                        }
                                    }

                                // increment day count
                                    $day_count++;
                            }
                            elseif (isset($post['monthly']))
                            {
                                if (isset($post['monthly_on_this_day']))
                                {
                                    if (substr($date,6,2)==substr($from_date,6,2))
                                    {
                                        $add_events=1;
                                    }
                                }
                                elseif (isset($post['monthly_at_this_position']))
                                {
                                    if ($day_details['md']==$month_position)
                                    {
                                        $add_events=1;
                                    }
                                }
                            }

                    // only add events if the above checks have returned true
                        if (1==$add_events)
                        {
                            // for each day event from above add a variation and store the product ids in the day event
                            for ($x=0;$x<count($day_events);$x++)
                            {
                                if ('d'==$day_events[$x]['t'] ? $time_for_stamp='09:00' : $time_for_stamp=$day_events[$x]['t'] );
                                $date_for_stamp=substr($date,0,4).'-'.substr($date,4,2).'-'.substr($date,6,2);

                                // create a variation of this meta event product
                                    $insert_data=array(
                                        'node_id'=>$node_id,
                                        'price'=>$post['price'],
                                        'stock_level'=>$post['spaces'],
                                        'event_timestamp'=>strtotime($date_for_stamp.' '.$time_for_stamp)
                                        );
                                    $this->db->insert('nvar',$insert_data);
                                    $nvar_id=$this->db->insert_id();

                                // add product and variation id to the day event
                                    $day_events[$x]['nv']=$nvar_id;
                            }

                            // store the day events accesible by iteration or key
                                $day_details['e'][$node_id]['t']=$day_events;

                            // add the event meta id as a keyed value for easier access than if it is just the key
                                $day_details['e'][$node_id]['id']=$node_id;
                                $day_details['e'][$node_id]['p']=$post['price'];
                                $day_details['e'][$node_id]['n']=$post['name'];
                                $day_details['e'][$node_id]['dr']=$post['duration_value'].$post['duration'];
                                if (1==$exclusive)
                                {
                                    $day_details['e'][$node_id]['x']=1;
                                }

                            // save the date as the last in the sequence
                            // this will be stored
                                $last_in_sequence=reverse_date(date_key_to_human($date),'-');
                        }
                }

                // get all over the events from this day into the event durations array
                // done right the way through the calendar to catch the trailing events
                    foreach ($day_details['e'] as $day_event)
                    {
                        $dur=$this->events_model->get_duration($day_event['dr']);
                        if ('days'==$dur['unit'] ? $elength=$dur['count'] : $elength=ceil($dur['count']/24) );
                        if (isset($day_event['x']) ? $ex=1 : $ex=0 );
                        $event_durations[]=array('length'=>$elength,'exclusive'=>$ex,'key'=>$date.'-'.$day_event['id']);
                    }

                //dev_dump($event_durations);

                // now decrement each event duration record to mark the passing of the day so only those over lapping will be effected
                    for ($x=0;$x<count($event_durations);$x++)
                    {
                        // add the key for this event to the x array at the date level shows all the currently live events
                        // but not if they have counted down to 0
                            if ($event_durations[$x]['length']>0)
                            {
                                $day_details['x'][$event_durations[$x]['key']]=$event_durations[$x]['exclusive'];
                            }

                        // decrement first
                            $event_durations[$x]['length']-=1;

                        // get rid of the zeros for ease and efficiency - finished events won't be tagged
                           /* if ($event_durations[$x]['length']<1)
                            {
                                array_splice($event_durations,$x,1);
                            } */
                    }

                // finally add the whole array to the master event array
                    $calendar_days[$date]=$day_details;
            }

        // save the event sequence
            $update_data = array(
                'event_json' => json_encode($calendar_days)
            );

            $this->db->where('node_id', $post['calendar_id']);
            $this->db->update('calendar', $update_data);

        // save the last sequence date of this event
            if (!isset($last_in_sequence))
            {
                // last in this sequence is this date
                    $last_in_sequence=$post['from_date'];
            }
            $update_data = array(
                'last_in_sequence' =>$last_in_sequence
            );

            $this->db->where('node_id', $node_id);
            $this->db->update('event', $update_data);

        return $node_id;

        /* BENCHMARK */ $this->benchmark->mark('func_save_new_event_sequence_end');
    }

    /* *************************************************************************
         edit_event_sequence() - saves any edits to the event sequence
         @param array $post - the array of post variables from the form
         @return
    */
    public function edit_event_sequence($post)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_edit_event_sequence_start');

        // get event node
            $event=$this->node_model->get_node($post['event_id'],'event');

        // get calendar details
            $query=$this->db->select('*')->from('calendar')->where(array('node_id'=>$event['calendar_id']));
            $res=$query->get();
            $calendar=$res->row_array();

            $events=json_decode($calendar['event_json'],true);

        // save the calendar undo
            $this->save_undo_calendar($calendar['node_id'],'edit sequence',$event['name']);

        // get all the nvars for this node
            $query=$this->db->select('*')->from('nvar')->where(array('node_id'=>$post['node_id']));
            $res=$query->get();
            $nvars=$res->result_array();

        // iterate over them adding to an update batch array
            $update_array=array();
            foreach ($nvars as $n)
            {
                // only update the ones now and in the future
                    if ($post[$n['nvar_id'].'_date_key']>=$this->now['numeric'])
                    {
                        // only if process is set which represents the month being open for editing
                            if (1==$post[substr($post[$n['nvar_id'].'_date_key'],0,6).'_process'])
                            {
                                // skip if not numeric
                                    if (is_numeric($post[$n['nvar_id'].'_price']) ?  $price=$post[$n['nvar_id'].'_price'] : $price=$n['price'] );
                                    if (is_numeric($post[$n['nvar_id'].'_spaces']) ?  $spaces=$post[$n['nvar_id'].'_spaces'] : $spaces=$n['stock_level'] );

                                // update batch
                                    $update_array[]=array(
                                        'nvar_id'=>$n['nvar_id'],
                                        'price'=>$price,
                                        'stock_level'=>$spaces
                                    );

                                // events json value
                                    $events[$post[$n['nvar_id'].'_date_key']]['e'][$post['event_id']]['t'][$post[$n['nvar_id'].'_time_key']]['s']=$spaces;
                                    $events[$post[$n['nvar_id'].'_date_key']]['e'][$post['event_id']]['p']=$price;
                                    $events[$post[$n['nvar_id'].'_date_key']]['e'][$post['event_id']]['n']=$post[$n['nvar_id'].'_name'];
                            }
                    }
            }

        //   in iteration use keys to reach right json element and update

        // use update batch array
            $this->db->update_batch('nvar',$update_array,'nvar_id');

        // save the json array
            $update_data = array(
                'event_json' =>json_encode($events)
            );

            $this->db->where('node_id', $calendar['node_id']);
            $this->db->update('calendar', $update_data);

		/*$update_data[]=array('image_id'=>$img["image_id"],'main'=>0,'removed'=>1);

		$this->db->update_batch('image',$update_data,'image_id');*/

        /* BENCHMARK */ $this->benchmark->mark('func_edit_event_sequence_end');
    }

    /* *************************************************************************
         admin_calendar() - creates an admin interface to the calendar, arranged in months
            with clickable days for creating new events
         @param array $calendar - the calendar
         @return $ms - an html string with the calendar in it
    */
    public function admin_calendar($calendar)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_admin_calendar_start');

		$this->load->helper('date_helper');

        // get the array of date cells
			$cells=json_decode($calendar['event_json'],true);

        // get the default event
            $event_default=$this->config->item('event_default');

        // we need the colours from the event default
			$colours=$event_default['colours'];

		// mark today
			$now=get_now();

		// for breaking the calendar panels up
			$last_month=0;
			$last_year=0;

		// get the events sequences
			$events=$this->node_model->get_nodes(array('event.calendar_id'=>$calendar['id'],'type'=>'event'),'joined');

			// order this array
			$es=array();
			foreach ($events as $e)
			{
                // count the number of nvars and infer the number of events in the sequence, skipping if there are none
                    $query=$this->db->select('*')->from('nvar')->where(array('node_id'=>$e['id']))->order_by('event_timestamp desc');
                    $res=$query->get();
                    $nvars=$res->result_array();

                if (count($nvars)>0)
                {
                    // get the first in the result and compare to now, if it's less then all this events
                    // instances have passed
                        $still_active=($nvars[0]['event_timestamp']>$now['timestamp']) ? 1 : 0;

                    // lol at the hack, array uses ids to differentiate between two events with the same name but hides it with css
                        $es[$e['name']." <span style='display:none;'>[".$e['id']."]</span>"]=array(
                            'id'=>$e['id'],
                            'details'=>$e,
                            'still_active'=>$still_active
                        );
                }
			}
			ksort($es);

		// output event sequences
            $acal="<div class='panel events_list_panels'>";
            $acal."<div class='calendar_events_list'>";
            $acal.="<h2>";
            $acal.="<span id='cal_events_heading' class='ad_heading_text noselect' onclick='open_height(\"cal_events\")'>Events On ".$calendar['name']."</span>";
            $acal.="<span id='cal_events_show' class='sprite panel_open noselect' onclick='open_height(\"cal_events\")'></span>";
            $acal.="</h2>";
            $acal.="<div id='cal_events_panel' class='panel_details panel_closed'>";
            $acal.="<span class='js_show_all_events all_event_link'>[show passed events too]</span>";
            $acal.="<div class='cal_events_panel_list'>";
			foreach ($es as $name=>$details)
			{
                $is_hidden=(0==$details['still_active']) ? "class='passed hidden'" : "";
				$acal.="<a ".$is_hidden." href='/event/sequence/".$details['id']."'>";
				$acal.="<span id='hl_".$details['id']."' class='event_meta_link' style='background-color:".$colours[$details['details']['colour_choice']]['fill'].";border:1px solid ".$colours[$details['details']['colour_choice']]['border']."' onmouseover='event_hl(".$details['id'].",\"".$colours[$details['details']['colour_choice']]['fill']."\")' onmouseout='event_uhl(".$details['id'].")'>";
				$acal.=$name." [".$details['details']['duration']."]";
				$acal.="</span>";
				$acal.="</a>";
			}
            $acal.="</div>";
			$acal.="</div>";
            $acal.="</div>";

		// jquery function for highlight
			$js='';
			$js.="<script type='text/javascript'>";
			$js.="function event_hl(id,colour)";
			$js.="{";
			$js.="$('.hl_'+id).css({'opacity':'1'});";
            $js.="$('.hl_'+id+' span').css({'background-color':'#181818','color':'#fff','outline':'2px solid #181818'});";
			$js.="}";
			$js.="function event_uhl(id)";
			$js.="{";
            if (strlen($this->config->item('admin_marked_colour'))>0 ? $colour=$this->config->item('admin_marked_colour') : $colour='#f7f7f7' );
			$js.="$('.hl_'+id).css({'opacity':'0.75'});";
            $js.="$('.hl_'+id+' span').css({'background-color':'rgba(0,0,0,0)','color':'#181818','outline':'none'});";
			$js.="}";
			$js.="</script>";

			$acal.=$js;

        // build the form for the undoing of recent calendar updates
            $uf='';
            /* $query=$this->db->select('step_sequence,description')->from('calendar_undo')->where(array('calendar_id'=>$calendar['id']))->order_by('step_sequence desc');
            $res=$query->get();
            $undos=$res->result_array();

           //$this->load->helper('form_helper');
            $uf=form_open('/events/undo');
            $uf.="<input type='hidden' name='calendar_id' value='".$calendar['id']."'/>";
            $uf.="<select id='event_undos' class='form_field' name='undo'>";
            foreach ($undos as $u)
            {
                $uf.="<option value='".$u['step_sequence']."'>".$u['description']."</option>";
            }
            $uf.="</select>";
            $uf.="<input id='undo_submit' class='undo_submit' type='submit' name='submit' value='undo'/>";
            $uf.="</form>"; */

            // open the panel divs
                $acal.="<div class='panel all_previous_panel js_event_sweep'>";
                $acal.="<h2>";
                $acal.="<span id='previous_heading' class='ad_heading_text noselect' onclick='open_height(\"previous\")'>All Previous</span>";
                $acal.="<span id='previous_show' class='sprite panel_open noselect' onclick='open_height(\"previous\")'></span>";
                $acal.="</h2>";
                $acal.="<div class='sweep_events'></div>";
                $acal.="<div id='previous_panel' class='panel_details panel_closed'>";

		// build the calendar as an array of months and use the html slider
			$ps=array();

            $skip_first_close=true;

			//dev_dump($cells);
			foreach ( $cells as $k=>$v )
			{
				// break current cell key into numerics
					$key_date[0]=substr($k,0,4);
					$key_date[1]=substr($k,4,2);
					$key_date[2]=substr($k,6,2);
                    $knum=$k; // keep the full numeric key for now calc

                    $md=$key_date[1].'-'.$key_date[2];

					$k=$key_date[0]."-".$key_date[1]."-".$key_date[2];

				// a new panel every three months
					if (in_array($md,array('01-01','04-01','07-01','10-01')))
					{

                        // this panel is open
                            $state='open';
                            $extra_class='panel_closed';

                        $now=date('Ymd',time());
                        $key_plus=$key_date[0].str_pad(($key_date[1]+2), 2, '0', STR_PAD_LEFT).'31';
                        if ($now>str_replace("-", "", $k) &&
                            $now<$key_plus)
                        {
                            //$acal.=$now."|".$k."|".$key_plus."</div>";
                            $acal.="</div>";
                            $acal.="</div>";

                            $state='close';
                            $extra_class='';
                        }

                        // open the panel divs
                            $acal.="<div class='panel js_event_sweep'>";
                            $acal.="<h2>";
                            $acal.="<span id='calendar_panel_".$md."-".$key_date[0]."_heading' class='ad_heading_text noselect' onclick='".$state."_height(\"calendar_panel_".$md."-".$key_date[0]."\")'>".$this->month_nums[$key_date[1]]." - ".$this->month_nums[$key_date[1]+2]." ".$key_date[0]."</span>";
                            $acal.="<span id='calendar_panel_".$md."-".$key_date[0]."_show' class='sprite panel_".$state." noselect' onclick='".$state."_height(\"calendar_panel_".$md."-".$key_date[0]."\")'></span>";
                            $acal.="</h2>";
                            $acal.="<div class='sweep_events'></div>";
                            $acal.="<div id='calendar_panel_".$md."-".$key_date[0]."_panel' class='panel_details ".$extra_class."'>";
					}

				// a new month means a new month div
					if ('01'==$key_date[2])
					{
						// open month
							$acal.="<div id='month_".$key_date[0]."-".$key_date[1]."' class='month'>";

						// heading
							$acal.="<span class='mheading'>".$this->month_nums[$key_date[1]]."</span>";

						// add a row of day names
							$acal.="<div id='day_headings'>";
							$acal.="<span class='cal_cell day_heading'>mon</span>";
							$acal.="<span class='cal_cell day_heading'>tue</span>";
							$acal.="<span class='cal_cell day_heading'>wed</span>";
							$acal.="<span class='cal_cell day_heading'>thu</span>";
							$acal.="<span class='cal_cell day_heading'>fri</span>";
							$acal.="<span class='cal_cell day_heading'>sat</span>";
							$acal.="<span class='cal_cell day_heading'>sun</span>";
							$acal.="</div>";


                        // fill up the appropriate number of days with blanks so day columns are always the same
                            $empty_cells=format_date($k,'N')-1;
                            for ( $x=1; $x<=$empty_cells; $x++ )
                            {
                                $acal.="<div class='empty_cell'>";
                                $acal.="</div>";
                            }
					}

				// today css
					if ( $now['string']==$k ? $today_cell=" now " : $today_cell="" );

                // passed css
                    if ( $knum<$now['numeric'] ? $passed='admincal_date_passed' : $passed='' );

				// get the day name for output
					$day_name=format_date($k,'D');

				// now output the cell
					// add classes to date panel for highlight
						$extra_classes='';
						$basic_mark_cell=0;
						foreach ($v['e'] as $ek=>$ev)
						{
							$basic_mark_cell++;
							$extra_classes.=" hl_".$ek;
						}

					// mark all cells that have any number of events
						if ($basic_mark_cell>0 ? $mark_class=' marked ' : $mark_class='' );

					// open
						//$acal.="<div id='day_".$k."' class='cal_cell ".$today_cell.$extra_classes.$mark_class.$passed."' ";
                        $acal.="<div id='day_".$k."' class='cal_cell ".$today_cell.$extra_classes.$mark_class.$passed."' ";

					// new event link
                        if ($knum>=$now['numeric'])
                        {
                            $acal.="onclick='show_event_panel(".$key_date[0].",".$key_date[1].",".$key_date[2].",\"\",".$calendar['id'].")'";
                        }

                    // close, with or without onclick
                        $acal.=">";

                    // date
                        $acal.="<span>".$key_date[2]."</span>";

					// close
						$acal.="</div>";

				// close the month div
					if ($key_date[2]==days_in_month($key_date[1],$key_date[0]))
					{
						$acal.="</div>";
					}

				// end of the year means close the month div and add to panels array
					if (in_array($md,array('03-31','06-30','09-30','12-31')) or
						$k==$calendar['until_date'])
					{
						$acal.="</div>";
						$acal.="</div>";
					}
			}

            // set the admin colours on the days
                foreach ($es as $name=>$details)
                {
                    $acal.="<script>";
                    $acal.="$('.hl_".$details['id']."').css({'background-color':'".$colours[$details['details']['colour_choice']]['fill']."','border':'1px solid ".$colours[$details['details']['colour_choice']]['border']."','opacity':'0.75'});";
                    $acal.="</script>";
                }

            // a bit naughty, but this js moves the 'all previous' from the top to out of the way at the bottom
                $acal.="<script>";
                $acal.="$('.all_previous_panel').appendTo($('.all_previous_panel').parent());";
                $acal.="</script>";

            $event_list['calendar']=$acal;

            $event_list['undo']=$uf;

        return $event_list;

        /* BENCHMARK */ $this->benchmark->mark('func_admin_calendar_end');
    }

    /* *************************************************************************
         add_cells() - takes an array of date cells and adds to them between and inclusively of the dates given
         @param array $cells - the current array of cells (empty on create)
         @param string $start - the cell key for the first cell to be added
         @param string $end - the cell key for the last cell to be added
         @return $cells - the cells array with the new date cells added
    */
    public function add_cells($cells,$start_year,$end)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_add_cells_start');

        for ( $y=$start_year; $y<2100; $y++ )
        {
            for ( $m=1; $m<13; $m++ )
            {
                // tell the array which month day it is
                    $month_day_count=array(
                        '1'=>0,
                        '2'=>0,
                        '3'=>0,
                        '4'=>0,
                        '5'=>0,
                        '6'=>0,
                        '7'=>0,
                    );

                if ( $y>=$start_year )
                {
                    for ( $d=1; $d<=(days_in_month($m,$y)); $d++ )
                    {
                        // format with leading zeros
                            if ($d<10 ? $day='0'.$d : $day=$d );
                            if ($m<10 ? $month='0'.$m : $month=$m );

                        // count the days in the month
                            $day_num=format_date($y."-".$month."-".$day,'N');
                            $month_day_count[$day_num]++;

                        // add new cell to calendar - only if one doesn't already exist
                            if (!isset($cells[$y.$month.$day]))
                            {
                                $cells[$y.$month.$day]=array(
                                    'e'=>array(), // the event array for this cells
                                    'dn'=>$day_num, // the number of the day, mon=1
                                    'md'=>$month_day_count[$day_num]." ".$day_num, // month day values, i.e. 1st mon, 3rd fri
                                    'x'=>array() // exclusivity array defined
                                );
                            }

                        // break if we have reached the last day
                            if ($y.$month.$day==$end)
                            {
                                break 3;
                            }
                    }
                }
            }
        }

        return $cells;

        /* BENCHMARK */ $this->benchmark->mark('func_add_cells_end');
    }

    /* *************************************************************************
         count_events() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function count_events()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_count_events_start');

        return "[ start making selections ... ]";

        /* BENCHMARK */ $this->benchmark->mark('func_count_events_end');
    }

    /* *************************************************************************
         build_preview_calendar() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function build_preview_calendar()
    {
        /* BENCHMARK */ $this->benchmark->mark('func_build_preview_calendar_start');

        return "<div id='preview_calendar'>preview calendar here</div>";

        /* BENCHMARK */ $this->benchmark->mark('func_build_preview_calendar_end');
    }
}
