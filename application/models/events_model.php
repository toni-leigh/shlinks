<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Events_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
*/
    class Events_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        // helpers
            $this->load->helper('date_convert_helper');
            $this->load->helper('date_helper');

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
            $this->event_nodes=array();
    }

    /* *************************************************************************
         get_calendar() - this function returns a portion of a calendars event set around a focus date governed by the granularity
            the resulting html can be sent to a remote site and implanted
         @param int $calendar_id - to get the calendar
         @param string $focus - the date around which to output the calendar - could be an actual day or the first day of a period
         @return
    */
    public function get_calendar($calendar,$reload_url,$focus=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_calendar_start');
            $this->load->helper('data_helper');
            $this->load->model('slider_model');

        // default focus if not set
            if (null==$focus)
            {
                $focus=date('m-Y',time());
            }

        // get event array
            $events=json_decode($calendar['event_json'],true);

        // event nodes
            $event_nodes=$this->node_model->get_nodes(array('event.calendar_id'=>$calendar['node_id'],'type'=>'event'),1);
            $this->event_nodes=$this->node_model->nodes_by_id($event_nodes);

        // set calendar into object and user, event default, colours etc
            $this->calendar=$calendar;
            $this->calendar['user']=$this->node_model->get_node($this->calendar['user_id'],'user');
            $event_default=$this->config->item('event_default');
            $this->colours=$event_default['colours'];

        // now
            $this->now=get_now();

        // break date
            $fdate_bits=date_bits($focus,'-');

        // get focus day and start of chunk
            $limit=array();
            if (is_numeric($focus))
            {
                $cgran='year';

                $focus.="0101";

                $limit['start']=$focus;
                $sel_year=substr($limit['start'],0,4);
                $limit['end']=$sel_year."1232"; // one day more than December this year will ensure only this year is output
            }
            elseif (8==strlen($focus) or
                    7==strlen($focus))
            {
                $cgran='month';

                $month=(is_numeric($fdate_bits[0])) ? $fdate_bits[0] : $this->month_names[strtolower($fdate_bits[0])];

                $focus=$fdate_bits[1].$month."01";

                $limit['start']=$focus;
                $limit['end']=substr($limit['start'],0,6)."32"; // one day more than any month will ensure only this month is output
            }
            elseif (10==strlen($focus) or
                    11==strlen($focus))
            {
                $cgran='day';

                $month=(is_numeric($fdate_bits[1])) ? $fdate_bits[1] : $this->month_names[strtolower($fdate_bits[1])];

                $focus=$fdate_bits[2].$month.$fdate_bits[0];

                $limit['start']=$focus;
                $limit['end']=$limit['start'];
            }
            else
            {
                $cgran='month';

                $focus=str_replace('-','',$this->now['string']);

                $limit['start']=substr($focus,0,6)."01";
                $limit['end']=substr($focus,0,6)."32"; // one day more than any month will ensure only this month is output

            }

        // open the calendar
            $cale='';
            $cal['meta_data']=$calendar;

            $cal['granularity']=$cgran;

        // back links
            $cal['back_links']=array();

            if ('month'==$cgran)
            {
                // get bits
                    $yf=$fdate_bits[1];

                // granularity links
                    $cal['back_links']['year']="/".$reload_url."/".$yf;
            }
            elseif ('day'==$cgran)
            {
                // get bits
                    $yf=$fdate_bits[2];
                    $mf=$fdate_bits[1].'-'.$fdate_bits[2];

                // granularity links
                    $cal['back_links']['year']="/".$reload_url."/".$yf;
                    $cal['back_links']['month']="/".$reload_url."/".$mf;
            }

        // end limit
        // open the main calendar div
        // scroller
        // next and previous links
            $cal['next']=array();
            $cal['previous']=array();
            if ('day'==$cgran)
            {
                // open calendar
                    $cale.="<div id='day_chunk'>";

                // next and previous
                    $y=substr($limit['start'],0,4);
                    $m=substr($limit['start'],4,2);
                    $d=substr($limit['start'],6,2);

                    if ('01'==$d &&
                        '01'==$m)
                    {
                        $n='02-01-'.$y;
                        $l='31-12-'.($y-1);
                    }
                    elseif ('31'==$d &&
                            '12'==$m)
                    {
                        $n='01-01-'.($y+1);
                        $l='30-12-'.$y;
                    }
                    else
                    {
                        if ('01'==$d)
                        {
                            $lm=($m-1);
                            if ($lm<10 ? $lm='0'.$lm : $lm=$lm );
                            $n='02-'.$m.'-'.$y;
                            $l=days_in_month($m-1,$y).'-'.$lm.'-'.$y;
                        }
                        elseif ($d==days_in_month($m,$y))
                        {
                            $nm=($m+1);
                            if ($nm<10 ? $nm='0'.$nm : $nm=$nm );
                            $n='01-'.$nm.'-'.$y;
                            $l=(days_in_month($m,$y)-1).'-'.$m.'-'.$y;
                        }
                        else
                        {
                            $nd=$d+1;
                            $ld=$d-1;
                            if ($nd<10 ? $nd='0'.$nd : $nd=$nd );
                            if ($ld<10 ? $ld='0'.$ld : $ld=$ld );
                            $n=$nd.'-'.$m.'-'.$y;
                            $l=$ld.'-'.$m.'-'.$y;
                        }
                    }

                    // add next and previous
                        $cal['next']=array(
                            'link'=>"/".$reload_url."/".$n,
                            'label'=>'tomorrow'
                        );
                        $cal['previous']=array(
                            'link'=>"/".$reload_url."/".$l,
                            'label'=>'yesterday'
                        );
            }
            elseif ('month'==$cgran)
            {
                // open calendar
                    $cale.="<div id='month_chunk'>";

                // next and previous
                    $y=substr($limit['start'],0,4);
                    $m=substr($limit['start'],4,2);

                    if (1==$m)
                    {
                        // month name or num
                            if (is_numeric($fdate_bits[0]))
                            {
                                $nm='02';
                                $lm='12';
                            }
                            else
                            {
                                $nm='feb';
                                $lm='dec';
                            }

                        $n=$nm.'-'.$y;
                        $l=$lm.'-'.($y-1);
                    }
                    elseif (12==$m)
                    {
                        // month name or num
                            if (is_numeric($fdate_bits[0]))
                            {
                                $nm='01';
                                $lm='11';
                            }
                            else
                            {
                                $nm='jan';
                                $lm='nov';
                            }

                        $n=$nm.'-'.($y+1);
                        $l=$lm.'-'.$y;
                    }
                    else
                    {
                        // month name or num
                            if (is_numeric($fdate_bits[0]))
                            {
                                $nm=$m+1;
                                $lm=$m-1;
                                if ($nm<10 ? $nm='0'.$nm : $nm=$nm );
                                if ($lm<10 ? $lm='0'.$lm : $lm=$lm );
                            }
                            else
                            {
                                $nm=$this->month_nums[$m+1];
                                $lm=$this->month_nums[$m-1];
                            }

                        $n=$nm.'-'.$y;
                        $l=$lm.'-'.$y;
                    }

                    // add next and previous
                        $cal['next']=array(
                            'link'=>"/".$reload_url."/".$n,
                            'label'=>'next month'
                        );
                        $cal['previous']=array(
                            'link'=>"/".$reload_url."/".$l,
                            'label'=>'last month'
                        );
            }
            elseif ('year'==$cgran)
            {
                // open calendar
                    $cale.="<div id='year_chunk'>";

                // next and previous
                    $cal['next']=array(
                        'link'=>"/".$reload_url."/".($sel_year+1),
                        'label'=>'next year'
                    );
                    $cal['previous']=array(
                        'link'=>"/".$reload_url."/".($sel_year-1),
                        'label'=>'last year'
                    );
            }

        // day names in convenient array
            $this->day_names=array('skip_zero','mon','tue','wed','thu','fri','sat','sun');

        // output chunk
            $cal['cells']=array();
            foreach ($events as $k=>$v)
            {
                if ($k>=$limit['start'] &&
                    $k<=$limit['end'])
                {
                    $cal['cells'][$k]=$v;

                    /*dev_dump($k);
                    dev_dump($v);*/

                    // date details
                    /*    $y=substr($k,0,4);
                        $m=substr($k,4,2);
                        $d=substr($k,6,2);
                        $ym=substr($k,0,6);
                        $ymd=$k;
                        $day_name=strtolower(format_date($y."-".$m."-".$d,'D'));
                        $day_num=format_date($y."-".$m."-".$d,'N');
                        $date=format_date($y."-".$m."-".$d,'d m Y');
                        $link_date=$d."-".$m."-".$y;

                    // open month
                        if (in_array($cgran,array('year','month')) &&
                            '01'==$d)
                        {
                            // open the month div
                                $cale.="<div class='m'>";

                            // if this is a year calendar then show a month name and link
                                if ('year'==$cgran)
                                {
                                    if (1==$this->config->item('full_month_names') ? $mname=$this->month_nums['f'.$m] : $mname=$this->month_nums[$m] );
                                    $cale.="<span class='mh'><a href='/".$reload_url."/".$this->month_nums[$m]."-".$y."'>".$mname."</a></span>";
                                }

                            // add a row of day names
                                $cale.="<div class='dhs'>";
                                $cale.="<span class='d dh mon'>mon</span>";
                                $cale.="<span class='d dh tue'>tue</span>";
                                $cale.="<span class='d dh wed'>wed</span>";
                                $cale.="<span class='d dh thu'>thu</span>";
                                $cale.="<span class='d dh fri'>fri</span>";
                                $cale.="<span class='d dh sat'>sat</span>";
                                $cale.="<span class='d dh sun'>sun</span>";
                                $cale.="</div>";

                            // fill the month output with dead cells
                                for ($x=1;$x<$day_num;$x++)
                                {
                                    $cale.="<span class='d dc ".$this->day_names[$x]."'></span>";
                                }
                        }

                    // open day and mark now (but not if its a day granularity, then we mark the half hour)
                        if ( $k==$this->now['numeric'] && $cgran!='day' ? $extra_class=' now ' : $extra_class='' );

                        // set a class if the day has visible events
                            if (count($v['e'])>0)
                            {
                                foreach ($v['e'] as $check_id=>$val)
                                {
                                    if (1==$this->event_nodes[$check_id]['visible'])
                                    {
                                        $extra_class.='has_e';
                                        break;
                                    }
                                }
                            }

                        $cale.="<div id='d".$ymd."' class='d ".$day_name." ".$extra_class."'>";

                    // add exclusivity field
                        $exs='';
                        if (is_array($v['x']))
                        {
                            foreach ($v['x'] as $xk=>$xv)
                            {
                                $exs.=$xk.'-'.$xv.'_';
                            }
                        }
                        $cale.="<input id='x".$ymd."' type='hidden' value='".$exs."'/>";

                    // date
                        if (isset($this->date_formats['calendar']) ? $date_text=date($this->date_formats['calendar'],strtotime($y."-".$m."-".$d)) : $date_text=$day_name." ".$date );
                        if ('day'==$cgran)
                        {
                            $cale.="<span id='date_".$ymd."' class='master_date'>".$date_text."</span>";
                        }
                        elseif ('month'==$cgran)
                        {
                            if (0==$this->calendar['gran_link_through'])
                            {
                                $cale.="<span class='date'>".$date_text."</span>";
                            }
                            else
                            {
                                $cale.="<span class='date'><a href='/".$reload_url."/".$link_date."'>".$date_text."</a></span>";
                            }
                        }
                        else
                        {
                            if (0==$this->calendar['gran_link_through'])
                            {
                                $cale.="<span class='date'>".day_suffix($d)."</span>";
                            }
                            else
                            {
                                $cale.="<span class='date'><a href='/".$reload_url."/".$link_date."'>".$d."</a></span>";
                            }
                        }

                    // if the granularity is day by day then we break the day cell down further into half hours
                    // we then iterate over the events inside this but the day by day calendar only hits the outer loop once
                    // high detail calendars will be unusual and maybe a bit heavier to handle
                        if ('day'==$cgran or
                            1==$calendar['high_detail'])
                        {
                            // if marked as 'd' then all day - this may be the case for more than one event on this day so we must check all
                                foreach ($v['e'] as $meta_id=>$day_event)
                                {
                                    // use zero to get the first array element - if it is 'd' (today) then there will only be one
                                        if ('d'==$day_event['t'][0]['t'])
                                        {
                                            // output a cell
                                                $cale.="<div id='allday_".$meta_id."' class='half_hour all_day'>";
                                                $cale.="<span class='time'>all day</span>";
                                                $cale.="</div>";
                                        }
                                }

                            // now do all the half hour slots for the day

                                foreach ($this->times as $t)
                                {
                                    // time as number
                                        $tnum=str_replace(':','',$t['time']);

                                    // highlight now
                                        $extra_class=
                                        (   $this->now['time_numeric']>=$tnum &&
                                            $this->now['time_numeric']<=$tnum+29 &&
                                            $this->now['numeric']==$k) ? ' now ' : '';

                                    // open the half hour output slot - only if there is an event or its high detail
                                        $cale.="<div id='time_".$tnum."' class='".$extra_class." time_".$tnum."'>";
                                        $cale.="<span class='time'>".$t['label']."</span>";
                                        $cale.="</div>";
                                }
                        }
                        else
                        {
                            // events - just normal output for months
                                if ('month'==$cgran)
                                {
                                    $view_link="";
                                    $event_counter=0;
                                }
                                else
                                {
                                    $cale.="<a class='bview' href='/".$reload_url."/".$link_date."'>vw</a>";
                                }
                        }

                    // close day
                        $cale.="</div>";

                    // close month
                        if ($d==days_in_month($m,$y))
                        {
                            $cale.="</div>";
                        }*/
                }
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_calendar_end');

        return $cal;
    }

    /* *************************************************************************
         get_duration() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function get_duration($dr)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_duration_start');

        $dur['unit']=$this->durations[substr(strrev($dr),0,1)];
        $dur['count']=substr($dr,0,strlen($dr)-1);

        if (1==$dur['count'])
        {
            $dur['unit']=substr($dur['unit'],0,strlen($dur['unit'])-1);
        }

        return $dur;

        /* BENCHMARK */ $this->benchmark->mark('func_get_duration_end');
    }
}
