<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 *
*/
?>
<div id='stream' class='node_panel stream'>
<?php
    $actions=array();
    $save_trigger=0; // used to trigger a stream save at the end

    function time_sort($a, $b)
    {
        return $b['t'] - $a['t'];
    }

    usort($stream, 'time_sort');

    // loop over stream
    for ($x=0;$x<count($stream);$x++)
    {
        // singular and plural for the actions
            if ($stream[$x]['c']>1)
            {
                $count=$stream[$x]['c'];
                $actions[0]="created";
                $actions[1]="created by";
                $actions[2]="updated ".$count." times";
                $actions[3]="updated ".$count." times by";
                $actions[4]="added ".$count." images to";
                $actions[5]=$count." images added by";
                $actions[6]="commented on";
                $actions[7]="commented on by";
                $actions[8]="befriended";
                $actions[9]="is joined by";
                $actions[10]="joins";
            }
            else
            {
                $actions[0]="created";
                $actions[1]="created by";
                $actions[2]="updated";
                $actions[3]="updated by";
                $actions[4]="added image to";
                $actions[5]="image added by";
                $actions[6]="commented on";
                $actions[7]="commented on by";
                $actions[8]="befriended";
                $actions[9]="is joined by";
                $actions[10]="joins";
            }

        // set the stream image (which is the actor or the node)
            $actor_image="<img class='rounded2' src='".$stream[$x]['ai']."' height='30' width='30'/>";

        // format the stream string
            $strstring="<span class='strtext'>";
            if ($stream[$x]['a']==2&&$stream[$x]['c']>1)
            {
                // this special case re-words for better English
                $strstring.="<a href='/".$stream[$x]['al']."'>".$stream[$x]['an']."</a> updated ".$stream[$x]['nt']." <a href='/".$stream[$x]['nl']."'>".$stream[$x]['nn']."</a> ".$count." times";
            }
            else
            {
                $strstring.="<a href='/".$stream[$x]['al']."'>".$stream[$x]['an']."</a> ".$actions[$stream[$x]['a']]." ".$stream[$x]['nt']." <a href='/".$stream[$x]['nl']."'>".$stream[$x]['nn']."</a>";
            }
            $strstring.="</span>";

        // build the image output panel
            $images='';
            if (isset($stream[$x]['il']))
            {
                $images="<div class='strimages'>";
                $imgs=$stream[$x]['il'];
                for ($y=0;$y<count($imgs);$y++)
                {
                    $images.="<img class='rounded2' src='".$imgs[$y]."' height='30' width='30'/>";
                    if ($y%9!=8)
                        $images.=item_spacer(5,30);
                }
                $images.="</div>";
            }

        // sort out the date stamp
            $date=date("d-m-Y H:i:s",$stream[$x]['t']);

        // viewed or not
            if ($user['user_id']==$node['id']) // signed in user viewing own node
            {
                if (isset($stream[$x]['v'])&&0==$stream[$x]['v'])
                {
                    $class='strpanel notify';
                    $txclass='strdate strdate_notify';
                    $stream[$x]['v']=1;
                    $save_trigger=1; // only save the stream if there were actual notifications to view
                }
                else
                {
                    $class='strpanel viewed';
                    $txclass='strdate strdate_viewed';
                }
            }
            else
            {
                $class='strpanel viewed';
                $txclass='strdate_viewed'; // always show as viewed for everyone else
            }

        // output
            ?>
                <div class='<?php echo $class; ?> rounded'>
                <div class='<?php echo $txclass; ?>'><?php echo $date; ?></div>
                    <div class='straimage'><?php echo $actor_image; ?></div>
                    <?php echo $strstring; ?>
                    <?php echo $images; ?>
                </div>
            <?php
    }

    if ($user['user_id']==$node['id'] && // signed in user viewing own node
        1==$save_trigger) // and there is something to save
    {
        // finally put the stream back with the viewed values changed
        // this could be quite a lengthy query, but bear in mind that it is only when a user views there own stream and there are notifications
            $update_data = array(
                'json' => json_encode(array_reverse($stream))
            );

            $this->db->where('stream_id', $user['stream_id']);
            $this->db->update('stream', $update_data);
    }
?>
</div>
