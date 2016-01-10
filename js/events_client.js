// function adds when the calendar add is clicked
// needs the extra values for display
    function add_from_calendar(nvar_id,key,name,price,date_time,date_key)
    {
        //alert('add from cal');
        // as long as there are spaces
            if (check_total(nvar_id)>0)
            {
                if (0==$('#row'+nvar_id).length)
                {
                    // get the form to append to
                        var current=$('#form_all').html();
                        
                    // build new row
                        var row=add_brow(nvar_id,key,name,price,date_time,date_key);
                        
                    // append
                        current+=row;
                        $('#form_all').html(current);
                }
            
                // add one
                    plus_one(nvar_id,date_key);
            }
        
        // save
            save_to_local();
    }
   
// adds one to an existing row 
    function plus_one(nvar_id,date_key)
    {
        // alert('plus');
        // if the calendar count value is > 0
            if (parseInt($('#csp'+nvar_id).html())>0)
            {
                $('#bsp'+nvar_id).html(parseInt($('#bsp'+nvar_id).html())+1);
                $('#csp'+nvar_id).html(parseInt($('#csp'+nvar_id).html())-1);
            }
            
        // exclude to make some unavailable
            set_exclude(date_key,'plus',nvar_id);
            
        // now adjust the parent element to show it as basketed
            set_basketed($('#cad'+nvar_id).parent());
            
        // set buttons
                        // alert('y');
            set_buttons(nvar_id);
        
        // save
            save_to_local();
    }
    
// subtracts one from an existing row
    function minus_one(nvar_id,date_key)
    {
        // adjust
            $('#bsp'+nvar_id).html(parseInt($('#bsp'+nvar_id).html())-1);
            $('#csp'+nvar_id).html(parseInt($('#csp'+nvar_id).html())+1);
            
        // unexclude
            set_exclude(date_key,'minus',nvar_id);
            
        // now adjust the parent element to show it as un-booked
            set_avail($('#cad'+nvar_id).parent());
        
        // if basket row count < 1
            if (parseInt($('#bsp'+nvar_id).html())<1)
            {
                // this also sets the buttons
                    remove_brow(nvar_id);
            }
            else
            {            
                // set buttons
                    set_buttons(nvar_id);
            }
        
        // save
            save_to_local();
    }
    
// highlights excludes
    function set_exclude(date_key,set_type,nvar_id)
    {        
        // get the excluded keys from the hidden field in the calendar and split into
        // individual keys
            var exs=$('#x'+date_key).val();            
            var xsplit=exs.split('_');
        
        // iterate over keys performing exclusion
        // this deals with any that are tailing into this event
        // we may not use this as to exclude something tailing over might not be neccessary
        // if we exclude those that an event tails over itself
          /*  for (x=0;x<xsplit.length;x++)
            {
                // get vals
                    var ex=xsplit[x].split('-');
                    var xdate=ex[0];
                    var xeid=ex[1];
                    
                    var target=$('#d'+xdate+' .blink_'+xeid);
                    
                // set to 'unavailable' colour
                    if ('plus'==set_type)
                    {
                        console.log('xplus');
                        set_uavail(target);
                        deactivate(target.find('.bline_add'),'add');
                        //activate(target.find('.bline_sub_deact'),'sub');
                    }
                    else
                    {
                        set_avail(target);
                        deactivate(target.find('.bline_sub'),'sub');
                        activate(target.find('.bline_add_deact'),'add');
                    }
            } */
          
        // exclude on the same day as this one
            
        // now we need to exclude any that this tails over
            // this is how long this event lasts
                var tail=$('#dur'+nvar_id).val()-1;
                
            // this gets an object which covers all the targets apart from ones in the same cell as this one
                var target=$('#cad'+nvar_id).parent().parent().nextAll().slice(0,tail);
                
            // gets an object which includes all the targets in this cell
            // NB - you dont need target this if you intend to have both way exclusion (the block above is uncommented)
                var target_this=$('#cad'+nvar_id).parent().nextAll();
                
            // now go the right way, on or off
                if ('plus'==set_type)
                {
                    // operate on all but this cell
                        set_uavail(target.find('.blink'));
                        deactivate(target.find('.bline_add'),'add');
                        deactivate(target.find('.bline_sub'),'sub');
                    
                    // operate on this cell
                        set_uavail(target_this); // no need to find here as we already at the correct level
                        deactivate(target_this.find('.bline_add'),'add');
                        deactivate(target_this.find('.bline_sub'),'sub');
                        
                    // as we have decided to exclude, we must also check for and remove anything from the basket that is about to be excluded
                        $.each(target,function(i,v)
                        {
                            nv=$(v).find('.blink').attr('nvar');
                            $('#row'+nv).remove();
                            $('#csp'+nv).html(1);
                        });
                        $.each(target_this,function(i,v)
                        {
                            nv=$(v).attr('nvar')
                            $('#row'+nv).remove();
                            $('#csp'+nv).html(1);
                        });
                    
                    // no need to activate anything, excluded means completely unusable
                }
                else
                {
                    // operate on all but this cell
                        set_avail(target.find('.blink'));
                        deactivate(target.find('.bline_sub'),'sub');
                    
                    // operate on this cell
                        set_avail(target_this);
                        deactivate(target_this.find('.bline_sub'),'sub');
                    
                    // activate here as well, as we need to be able to add those that are now unexcluded
                        activate(target.find('.bline_add_deact'),'add');
                        activate(target_this.find('.bline_add_deact'),'add');
                }     
    }
    
// sets unavailable
    function set_uavail(target)
    {
        target.removeClass('av').removeClass('bas').addClass('uav');
    }
    
// sets added to basket
    function set_basketed(target)
    {
        target.removeClass('av').removeClass('uav').addClass('bas');
    }
    
// sets available
    function set_avail(target)
    {
        target.removeClass('uav').removeClass('bas').addClass('av');
    }
    
// activates the buttons within target
    function activate(target,type)
    {
        target.removeClass('bline_'+type+'_deact').addClass('bline_'+type).removeAttr('disabled');
        target.removeClass('hide');
    }

// deactivates the buttons within target
    function deactivate(target,type)
    {
        target.removeClass('bline_'+type).addClass('bline_'+type+'_deact').attr('disabled','disabled');
        target.addClass('hide');
    }
    
// checks the total in the calendar
    function check_total(nvar_id)
    {
        // retrieve and return the calendar count value
            return parseInt($('#csp'+nvar_id).html());
    }
    
// adds a new basket row
    function add_brow(nvar_id,key,name,price,date_time,date_key)
    {
        var r='';
        
        // create a new row using values
            r+='<span id="row'+nvar_id+'">';
            r+='<span class="booking_line">';
            r+='<input type="button" id="bad'+nvar_id+'" class="bline_add" onclick="plus_one('+nvar_id+',\''+date_key+'\')"/>';
            r+='<input type="button" id="bsb'+nvar_id+'" class="bline_sub" onclick="minus_one('+nvar_id+',\''+date_key+'\')"/>';
            r+='<input type="hidden" value="'+nvar_id+'" class="bline_nvar"/>';
            r+='<span id="bsp'+nvar_id+'">0</span>';
            r+='&nbsp;X&nbsp;';
            r+='<span class="bline_name">\''+name+'\'</span> on '+date_time+'&nbsp;';
            r+='<span class="bline_remove" onclick="remove_brow('+nvar_id+')"></span>';
            r+='<span class="bline_price">&pound;<span id="pr'+nvar_id+'">'+price+'</span></span>';
            r+='</span>';
        
        // this line passes the data to the basket so the key is used
            r+='<input id="'+key+'" type="hidden" name="'+key+'" value="1"/>';
            
        // close the basket line row
            r+='</span>';
        
        return r;
    }
    
// removes a basket row - either on click of remove or if count is 0    
    function remove_brow(nvar_id)
    {
        // reset the calendar spaces
            var csp=$('#csp'+nvar_id);
            csp.html(parseInt(csp.html())+parseInt($('#bsp'+nvar_id).html()));
        
        // call remove jquery function on id'd row
            $('#row'+nvar_id).remove();
            
        // set buttons
            set_buttons(nvar_id);
        
        // save
            save_to_local();
    }
    
// sets the buttons for this event
    function set_buttons(nvar_id)
    {
        // activate both
            activate($('#cad'+nvar_id+',#bad'+nvar_id),'add');
            activate($('#csb'+nvar_id+',#bsb'+nvar_id),'sub');
            
        // if calendar count < 1 - these are individual events, we might want to use spaces value somehow here
            if (parseInt($('#csp'+nvar_id).html())<1)
            {
                //alert(1);
                // deactivate the add buttons as there are no more to add
                    deactivate($('#cad'+nvar_id+',#bad'+nvar_id),'add');
                    
                // as there are none left in the calendar set to 'unavailable'
                        console.log('setbut');
                    set_uavail($('#cad'+nvar_id).parent());
                    
                // if this is actually booked (a row exists in the basket) then set to 'basketed'
                    if ($('#row'+nvar_id).length>0)
                    {
                        console.log('basketed');
                        set_basketed($('#cad'+nvar_id).parent());
                    }
            }
        
        // if row doesn't exist
            if (0==$('#row'+nvar_id).length)
            {
                //alert(2);
                // deactivate the subtract buttons as the event can be re-added now
                    deactivate($('#csb'+nvar_id+',#bsb'+nvar_id),'sub');
                    
                // and re-colour
                    set_avail($('#cad'+nvar_id).parent());
            }
    }
    
// wrapper function to shorten set_buttons
// also calls set exclude to operate on the reposnse to the basketed items on reload
    function s(nvar_id)
    {
        // do this first so highlighting happens correctly
            //set_exclude(date_key,set_type,nvar_id);
            
        // set the buttons
            set_buttons(nvar_id);
    }

// wrapper function to shorten parseInt
    function p(n)
    {
        return parseInt(n);            
    }
    
// save function
    function save_to_local()
    {                
        if (typeof(Storage)!=='undefined')
        {
            sessionStorage.bagged_html=$('#form_all').html();
        }
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
        