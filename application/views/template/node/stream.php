<?php
    foreach ($stream as $s)
    {
        if ($s['c']>0)
        {
            if (1==$s['c'])
            {
                $s=$s['vals'][0];

                $viewed_class=(1==$s['actor_viewed']) ? "viewed" : "";

                if ($s['actor_id']==$node['id'])
                {
                    $user_prefix="";
                    $actor_image="";
                }
                else
                {
                    $user_prefix="<a href='/".$s['actor_url']."/stream'>".$s['actor_name']."</a>&nbsp;";
                    $actor_image="<a href='/".$s['actor_url']."/stream'><img class='actor_image' src='".$s['actor_image']."' width='40' height='40'/></a>";
                }

                if ($s['target_id']==$node['id'])
                {
                    $target_image='';
                }
                else
                {
                    $target_image="<a href='/".$s['target_url']."/stream'><img class='target_image' src='".$s['target_image']."' width='40' height='40'/></a>";
                }
                $heading=$user_prefix.$actions[$s['action_code']]['user']['1']."&nbsp;<a href='/".$s['target_url']."/stream'>".$s['target_name']."</a>";

                ?>
                <article class='stream_panel <?=$viewed_class; ?>'>
                    <?=$actor_image; ?>
                    <h2><?=$heading; ?>[<?="a-s: <strong>".$s['actor_score']."</strong>;to-s: <strong>".$s['target_owner_score']."</strong>"; ?>]</h2>
                    <div>
                        <?=$target_image; ?>
                    </div>
                </article>
                <?php
            }
            elseif ($s['c']>1)
            {
                $details=$s['vals'][0];
                $viewed=1;
                $actor_score=0;
                $target_owner_score=0;
                foreach ($s['vals'] as $sv)
                {
                    if (0==$sv['actor_viewed'])
                    {
                        $viewed=0;
                    }

                    $actor_score+=$sv['actor_score'];
                    $target_owner_score+=$sv['target_owner_score'];
                }
                $viewed_class=(1==$viewed) ? "viewed" : "";

                if ($details['actor_id']==$node['id'])
                {
                    $user_prefix="";
                    $actor_image="";
                }
                else
                {
                    $user_prefix="<a href='/".$details['actor_url']."/stream'>".$details['actor_name']."</a>";
                    $actor_image="<a href='/".$details['actor_url']."/stream'><img class='actor_image' src='".$details['actor_image']."' width='40' height='40'/></a>";
                }

                if ($details['target_id']==$node['id'])
                {
                    $target_image='';
                }
                else
                {
                    $target_image="<a href='/".$details['target_url']."/stream'><img class='target_image' src='".$details['target_image']."' width='40' height='40'/></a>";
                }

                $heading=$user_prefix.str_replace("%_COUNT", $s['c'], $actions[$details['action_code']]['user']['n'])."&nbsp;<a href='/".$details['target_url']."/stream'>".$details['target_name']."</a>";

                ?>
                <article class='stream_panel <?=$viewed_class; ?>'>
                    <?=$actor_image; ?>
                    <h2>
                        <?=$heading; ?>[<?="a-s: <strong>".$actor_score."</strong>;to-s: <strong>".$target_owner_score."</strong>"; ?>]
                    </h2>
                    <div>
                    <?php 
                        if (2==$details['action_code'])
                        {
                            // images, do something clever
                                echo "<a href='/".$details['target_url']."/stream'>";
                                $c=0;
                                foreach ($s['vals'] as $sv)
                                {
                                    if (0==$c)
                                    {
                                        $right=($s['c']-1)*5;
                                    }
                                    else
                                    {
                                        $right=0-(40+($c*5));
                                    }
                                    $top=$c*5; 
                                    echo "<img style='margin:".$top."px ".$right."px 0px 0px;' class='target_image' src='".$sv['target_image']."' width='40' height='40'/>";
                                    $c++;
                                }
                                echo "</a>";
                        }
                        else
                        {
                            echo $target_image; 
                        }
                    ?>
                    </div>
                </article>
                <?php
            }
        }
    }
?>