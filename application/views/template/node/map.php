<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAquh7JVc0aGu5iHF9h7QuNJvDz4h1AnQI&amp;sensor=false"></script>
<?php
    if (count($map_node_list) &&
        0==$admin_page &&
        1==$node['map'])
    {
        ?>
        <div id='map' class='frontstage_map'>
        </div>
        <?php
            $options='';
            $map_options=$this->config->item('map_options');
            $map_centre=$this->config->item('map_centre');
            if (is_array($map_options))
            {
                foreach($map_options as $opt=>$val)
                {
                    $options.=$opt.": ".$val.",";
                }
            }
            $options=substr($options,0,-1);
        ?>                    
        <script type="text/javascript">
            function initialise()
            {
                var mapOptions =
                {
                    center: new google.maps.LatLng(<?=$map_centre['latitude']; ?>, <?=$map_centre['longitude']; ?>),
                    <?=$options; ?>
                };
                
                var map = new google.maps.Map(document.getElementById("map"),mapOptions);

                console.log(map);
                
                var markers=Array();
                
                <?php
                    $c=0;
                    foreach ($map_node_list as $mn)
                    {
                        if (strlen($mn['image']))
                        {
                            //$image=thumbnail_tag(,100);
                            
                            $image=str_replace('t300','t100',$mn['image']);
                        }
                        else
                        {
                            $image="/img/default_image_small.png";
                        }
                        
                        $name=utf8_encode(str_replace("'","",trim( preg_replace( '/\s+/', ' ', $mn['name']))));
                        $short_desc=utf8_encode(str_replace("'","",trim( preg_replace( '/\s+/', ' ', $mn['short_desc']))));
                        
                        $sprite=json_decode($mn['map_sprite']);
                        ?>
                            add_marker(map,markers,<?=$c; ?>,"<?=$name; ?>","<?=$mn['url']; ?>","<?=$short_desc; ?>","<?=$image; ?>",<?=$mn['latitude']; ?>,<?=$mn['longitude']; ?>,<?=$sprite[0]; ?>,<?=$sprite[1]; ?>);
                        <?php
                        
                        $c++;
                    }
                ?>
            }
            
            function add_marker(map,markers,ref,name,url,short_desc,image,lat,long,sprx,spry)
            {
                var point=new google.maps.LatLng(lat,long);
                var marker_image=new google.maps.MarkerImage('/img/sprite.png',new google.maps.Size(24,38),new google.maps.Point(sprx,spry));
                
                markers[ref]=Array();
                    
                markers[ref][0]=new google.maps.Marker({ position:point, map:map, icon:marker_image });
                markers[ref][0].setMap(map);
                
                var boxHTML="<span class='mi'>";
                boxHTML+="<span class='mi_image'>";
                boxHTML+="<a href='/"+url+"'><img src='"+image+"' width='100' height='100'/></a>";
                boxHTML+="</span>";
                boxHTML+="<span class='mi_text'>";
                boxHTML+="<span class='mi_name'><a href='"+url+"'>"+name+"</a></span>";
                boxHTML+="<span class='mi_short_desc'><p>"+short_desc+"</p></span>";
                boxHTML+="</span>";
                boxHTML+="</span>";                
                
                markers[ref][1]=new google.maps.InfoWindow({ content:boxHTML });
                google.maps.event.addListener(markers[ref][0], 'click', function ()
                {
                    for (x in markers)
                        if (typeof(markers[x][1])!="undefined")
                            markers[x][1].close();markers[ref][1].open(map,markers[ref][0]);
                });
            }
            
            if (window.focus)
            {
                google.maps.event.addDomListener(window, 'load', initialise);
            }
            
            /*  */
        </script>
        <?php
    }
?>