<?php
    function format_date($db_date,$format)
    {
        $year=substr($db_date,0,4);
        $month=substr($db_date,5,2);
        $day=substr($db_date,8,2);
        $hour=substr($db_date,11,2);
        $min=substr($db_date,14,2);
        $second=substr($db_date,17,2);
        
        if (is_numeric($year) &&
            is_numeric($month) &&
            is_numeric($day))
        {
            return date($format,mktime($hour,$min,$second,$month,$day,$year));
        }
        else
        {
            return "malformed ".$db_date;
        }
    }
    
    function date_bits($date,$sep)
    {
        return explode($sep,$date);
    }
    
    function reverse_date($date,$sep)
    {
        // get a date bit array
            if (!is_array($date))
            {
                $date=date_bits($date,$sep);
            }
        
        // then reverse
            if (3==count($date))
            {
                $reversed=$date[2].'-'.$date[1].'-'.$date[0];
            }
            
        return $reversed;
    }
    
    function date_key_to_human($date)
    {
        return substr($date,6,2).'-'.substr($date,4,2).'-'.substr($date,0,4);
    }
    
    function date_zero($val)
    {
        if ($val<10 && 1==strlen($val) ? $v='0'.$val : $v=$val );
        return $v;
    }
    
    function get_now($time_stamp=null)
    {
        if (null==$time_stamp)
        {
            $time_stamp=time();
        }
        
        $now_string=date('Y-m-d_H:i:s',$time_stamp);
        $now_array=explode("_",$now_string);
        
        $date=explode('-',$now_array[0]);
        $time=explode(':',$now_array[1]);
        
        $now['year']=$date[0];
        $now['month']=$date[1];
        $now['month_name']=strtolower(date('M',$time_stamp));
        $now['day']=$date[2];
        $now['string']=$now_string;
        $now['year_month']=substr($now_string,0,7);
        
        $now['time']=$now_array[1];
        $now['hours_mins']=$time[0].":".$time[1];
        $now['hours']=$time[0];
        $now['mins']=$time[1];
        $now['seconds']=$time[2];
        
        $now['numeric']=$now['year'].$now['month'].$now['day'];
        $now['time_numeric']=$now['hours'].$now['mins'];
        $now['time_numeric_full']=$now['hours'].$now['mins'].$now['seconds'];

        $now['timestamp']=$time_stamp;
        
        return $now;
    }
    
    function day_suffix($day)
    {
        if ('0'==substr($day,0,1))
        {
            $day=substr($day,1,1);
        }
        
        if ('1'==substr(strrev($day),0,1) &&
            $day!='11')
        {
            $day.='st';
        }
        elseif ('2'==substr(strrev($day),0,1) &&
            $day!='12')
        {
            $day.='nd';
        }
        elseif ('3'==substr(strrev($day),0,1) &&
            $day!='13')
        {
            $day.='rd';
        }
        else
        {
            $day.='th';
        }
        
        return $day;
    }
?>