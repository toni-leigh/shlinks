<?php
    $sets = array(
        array(
            'name' => 'front end frameworks',
            'cats' => array(
            ),
            'parent'=>1748
        ),
        array(
            'name' => 'front end dev',
            'cats' => array(
            ),
            'parent'=>1746
        ),
        array(
            'name' => 'back end dev',
            'cats' => array(
            ),
            'parent'=>1745
        ),
        array(
            'name' => 'general development',
            'cats' => array(
                'artificial intelligence',
                'big data',
                'scientific data processing'
            ),
            'parent'=>1019
        ),
        array(
            'name' => 'testing',
            'cats' => array(
            ),
            'parent'=>1749
        ),
        array(
            'name' => 'project management',
            'cats' => array(
            ),
            'parent'=>1750
        ),
        array(
            'name' => 'user experience',
            'cats' => array(
            ),
            'parent'=>1055
        ),
        array(
            'name' => 'design',
            'cats' => array(
            ),
            'parent'=>1004
        ),
        array(
            'name' => 'internet marketing',
            'cats' => array(
            ),
            'parent'=>1045
        ),
        array(
            'name' => 'physical world',
            'cats' => array(
            ),
            'parent'=>1798
        )
    );

                // '3d printing',
                // 'biology and biomechanics',
                // 'cars',
                // 'chemistry',
                // 'physics',
                // 'robotics'

    $counter = 0;

    foreach ($sets as $s) {

        $p=$s['parent'];

        foreach ($s['cats'] as $n)
        {
            # $counter = $counter + 100;
            $insert_data=array(
                    'json'=>''
                );

            $this->db->insert('stream',$insert_data);

            $sid=$this->db->insert_id();

            $insert_data=array(
                'name'=>$n,
                'url'=>str_replace(' ', '-', $n),
                'type'=>'category',
                'stream_id'=>$sid,
                'user_id'=>1,
                'user_name'=>'Toni-Leigh Sharpe',
                'visible'=>1
            );
            $this->db->insert('node',$insert_data);

            $nid=$this->db->insert_id();


            $insert_data=array(
                    'node_id'=>$nid
                );
            $this->db->insert('category',$insert_data);


            $insert_data=array(
                'node_id'=>$nid,
                'parent_id' => $p,
                'lft' => $counter,
                'rght' => $counter
            );
            $this->db->insert('hierarchy',$insert_data);
        }
    }


    die();
