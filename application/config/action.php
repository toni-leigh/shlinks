<?php
    $lowest=0;
    $low=0;
    $average=0;
    $high=0;
    $highest=0;

    // 1 = singular
    // n = plural
    $config['stream_actions']=array(
        0=>array(
            'user'=>array(
                '1'=>'created',
                'n'=>'created %_COUNT'
            ),
            'node'=>array(
                '1'=>'created by'
            ),
            'actor_score'=>array(
                'blog'=>$high,
                'calendar'=>$highest,
                'event'=>$average,
                'groupnode'=>$highest,
                'product'=>$average
            )
        ),
        1=>array(
            'user'=>array(
                '1'=>'updated',
                'n'=>'updated %_COUNT times'
            ),
            'node'=>array(
                '1'=>'updated by',
                'n'=>'updated %_COUNT times by'
            )
        ),
        2=>array(
            'user'=>array(
                '1'=>'added image to',
                'n'=>'added %_COUNT images to'
            ),
            'node'=>array(
                '1'=>'added image to by',
                'n'=>'added %_COUNT images to by'
            ),
            'actor_score'=>array(
                'blog'=>$low,
                'calendar'=>$low,
                'event'=>$low,
                'groupnode'=>$low,
                'product'=>$low,
                'user'=>$average
            ),
            'target_owner_score'=>array(
                'blog'=>$lowest,
                'calendar'=>$lowest,
                'event'=>$lowest,
                'groupnode'=>$lowest,
                'product'=>$lowest,
                'user'=>$lowest
            )
        ),
        3=>array(
            'user'=>array(
                '1'=>'commented on',
                'n'=>'%_COUNT comments on '
            ),
            'node'=>array(
                '1'=>'commented on by',
                'n'=>'%_COUNT comments on by '
            ),
            'actor_score'=>array(
                'blog'=>$lowest,
                'calendar'=>$lowest,
                'event'=>$lowest,
                'groupnode'=>$lowest,
                'product'=>$lowest,
                'user'=>$lowest
            ),
            'target_owner_score'=>array(
                'blog'=>$lowest,
                'calendar'=>$lowest,
                'event'=>$lowest,
                'groupnode'=>$lowest,
                'product'=>$lowest,
                'user'=>$lowest
            )
        ),
        4=>array(
            'user'=>array(
                '1'=>'befriended',
                'n'=>'befriended'
            ),
            'node'=>array(
                '1'=>'befriended',
                'n'=>'befriended'
            )
        ),
        5=>array(
            'user'=>array(
                '1'=>'joins',
                'n'=>'joins'
            ),
            'node'=>array(
                '1'=>'joined by',
                'n'=>'joined by'
            ),
            'actor_score'=>array(
                'groupnode'=>$high
            ),
            'target_owner_score'=>array(
                'groupnode'=>$low
            )
        ),
        6=>array(
            'user'=>array(
                '1'=>'voted up',
                'n'=>'voted %_COUNT times up'
            ),
            'node'=>array(
                '1'=>'voted up by',
                'n'=>'voted up %_COUNT times by'
            ),
            'actor_score'=>array(
                'blog'=>$lowest,
                'calendar'=>$lowest,
                'event'=>$lowest,
                'groupnode'=>$lowest,
                'product'=>$lowest,
                'user'=>$lowest
            ),
            'target_owner_score'=>array(
                'blog'=>1,
                'calendar'=>1,
                'event'=>1,
                'groupnode'=>1,
                'link'=>1,
                'product'=>1,
                'user'=>1
            )
        ),
        7=>array(
            'user'=>array(
                '1'=>'voted down',
                'n'=>'voted %_COUNT times down'
            ),
            'node'=>array(
                '1'=>'voted down by',
                'n'=>'voted down %_COUNT times'
            ),
            'actor_score'=>array(
                'blog'=>0-$lowest,
                'calendar'=>0-$lowest,
                'event'=>0-$lowest,
                'groupnode'=>0-$lowest,
                'product'=>0-$lowest,
                'user'=>0-$lowest
            ),
            'target_owner_score'=>array(
                'blog'=>0,
                'calendar'=>0,
                'event'=>0,
                'groupnode'=>0,
                'product'=>0,
                'user'=>0
            )
        ),
        8=>array(
            'user'=>array(
                '1'=>'added video to',
                'n'=>'added %_COUNT videos to'
            ),
            'node'=>array(
                '1'=>'had video added by',
                'n'=>'had %_COUNT videos added by'
            ),
            'actor_score'=>array(
                'blog'=>$average,
                'calendar'=>$average,
                'event'=>$average,
                'groupnode'=>$average,
                'product'=>$average,
                'user'=>$average
            )
        ),
        9=>array(
            'user'=>array(
                '1'=>'updated video for',
                'n'=>'updated video %_COUNT times'
            ),
            'node'=>array(
                '1'=>'video updated by',
                'n'=>'video updated %_COUNT times by'
            )
        ),
        10=>array(
            'user'=>array(
                '1'=>'followed',
                'n'=>'followed %_COUNT'
            ),
            'node'=>array(
                '1'=>'followed by',
                'n'=>'followed by %_COUNT times'
            ),
            'actor_score'=>array(
                'blog'=>$lowest,
                'calendar'=>$lowest,
                'event'=>$lowest,
                'groupnode'=>$lowest,
                'product'=>$lowest,
                'user'=>$lowest
            ),
            'target_owner_score'=>array(
                'blog'=>$average,
                'calendar'=>$average,
                'event'=>$average,
                'groupnode'=>$average,
                'product'=>$average,
                'user'=>$average
            )
        ),
        11=>array(
            'user'=>array(
                '1'=>'added link to',
                'n'=>'added %_COUNT links to'
            ),
            'node'=>array(
                '1'=>'had link added by',
                'n'=>'had %_COUNT links added by'
            ),
            'actor_score'=>array(
                'blog'=>$average,
                'calendar'=>$average,
                'event'=>$average,
                'groupnode'=>$average,
                'product'=>$average,
                'user'=>$average
            )
        ),
    );
?>
