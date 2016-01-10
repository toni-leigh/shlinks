    <body class='<?=$body_classes ?>' <?=$body_map_onload ?>>
        <?=$ie['open'] ?>
            <div class='node_<?=$node['id'] ?> <?=$node['type'] ?>_node'>
                <?=$skip['start'] ?>
                <?=$this->load->view('/template/frame/js_warning') ?>
                <?=$live_warning ?>
            