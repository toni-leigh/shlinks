<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Slider_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 *
 * this model uses nodes and images to generate sliders for use on the site
 * currently there are panel sliders - mostly for iumage galleries but will alside html too
 * and node content sliders for a naviagtion system that slides the page down
 *
*/
    class Slider_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        // helpers
            $this->load->helper('image_helper');

        // models
            $this->load->model('variation_model');
    }

    /* *************************************************************************
     slider_html() - gets html for a slider of node panels
     @param array $panels - the panels for the slider, array of html
     @param int $dimensions - an array of pixel dimensions for the slider, includes:
        $dimensions['width'] of the viewable area of the slider
        $dimensions['height'] of the viewable area of the slider
        $dimensions['single_panel'] the full size of the panel when all css (padding etc) computed - the actual screen size of the panel when viewed
        $dimensions['css_width_add'] the extra width added by padding, borders or margin
        $dimensions['spacer'] width of the spacer between panels
     @param string $slider_name - a prefix for css styles
     @param string $type - html, video or image only, defaults to images
     @return string $html - the completed html
    */
    public function slider_html($panels,$dimensions,$slider_name,$type='images')
    {
        // initialise values
            // panel count
                $panel_count=count($panels);

            // get the full width of the panel
                $panel_full=$this->panel_full($dimensions);

            // the number of complete visible panels (the width of the row in panels)
                $full_panels_visible=floor( $dimensions['width'] / ( $panel_full + $dimensions['spacer'] ) );

            // the number of panel set that make up the whole row (the plus one is because the the first panel set contains the panel that is just showing)
            // panel set is the set of panels which are wholly visible (doesn't include any portions of panels showing as a hint)
            // NB don't take one off if the panels exactly fit into the width and no hint is left
            // also set the initial panels visible, again effected by whether a hint panel is shown
                if (0==$dimensions['width'] % $panel_full)
                {
                    $panel_sets=ceil( ( $panel_count ) / $full_panels_visible );
                    $initial_panels_visible=$full_panels_visible;
                    $adjuster=0;
                }
                else
                {
                    $panel_sets=ceil( ( $panel_count - 1 ) / $full_panels_visible );
                    $initial_panels_visible=$full_panels_visible+1;
                    $adjuster=1; // gets rid of extra panel
                }

            // width of all the panels in pixels
                $panel_width=( $panel_count * $panel_full ) + ( $panel_count*$dimensions['spacer'] );

            // width for the dead panel that marks that there are no more to slide
                $dead_panel_width=$dimensions['width']-( $full_panels_visible * ( $panel_full + $dimensions['spacer'] ) );

            // add this to the full panel width
                $panel_width+=$dead_panel_width;

            // slide distance derived
                $slide_distance=$full_panels_visible*($panel_full+$dimensions['spacer']);

        // counter
            $ocount="<span class='counter'>";
            $ocount.="<span id='".$slider_name."_count'>1</span> of ".$panel_sets;
            $ocount.="</span>";

        // direction buttons
            $buttons='';
            if ($panel_width>$dimensions['width'])
            {
                $buttons.="<div id='".$slider_name."_slbs' class='slbs'>\n";
                $buttons.="<span class='sprite slld slb'></span>";
                //$buttons.="<span class='sprite slr slb' onclick='slider_move(\"".$slider_name."\",\"right\",".$slide_distance.",1)'>go left one panel</span>";
                $buttons.="<span class='sprite slr slb' onclick='slide_to(\"".$slider_name."\",1)'>go left one panel</span>";
                $buttons.="</div>";
            }
            else
            {
                $buttons.="<div id='".$slider_name."_slbs' class='slbs'>";
                $buttons.="<span class='sprite slld slb'></span>";
                $buttons.="<span class='sprite slrd slb'></span>";
                $buttons.="</div>";
            }

        // the slider
                if ( 'images'==$type ? $schema_type='ImageGallery' : $schema_type='ItemList' );
                $html="<div id='".$slider_name."' class='slider slw' itemscope itemtype='http://schema.org/".$schema_type."'>";

            // sequence buttons
                $seq="<div id='".$slider_name."_sqbs' class='sqbs'>";
                $seq_count=1;

            // open the slider panel
                $html.="<div class='viewer_row slh slw'>";
                $html.="<div class='viewer_window slh slw'>";
                $html.="<div class='list_panel slh' style='width:".$panel_width."px;'>";

            // build the set of panels up into an array of widths
                for ($x=0;$x<$panel_sets;$x++)
                {
                    // set the panel count for this width
                        if (0==$x ? $pc=$initial_panels_visible : $pc=$full_panels_visible );

                    // tip
                        $tip='';

                    // iterate over the panel count extracting panels and adding them to the js array
                        for ($y=0;$y<$pc;$y++)
                        {
                            // get the right panel
                                if (0==$x ? $element=$y : $element=(( $x * $full_panels_visible ) + $adjuster ) + $y );

                            // make the panel
                                if (isset($panels[$element]))
                                {
                                    $html.=$this->slider_panel($panels[$element],$dimensions,$slider_name,$x);
                                    if ($dimensions['spacer']>0)
                                    {
                                        $html.=item_spacer($dimensions['spacer']);
                                    }
                                }
                            //
                                if (isset($panels[$element]['tool_tip']) ? $tip.=$panels[$element]['tool_tip']."; " : $tip.='' );
                        }

                    // create the sequence button
                        if (strlen($tip)>0)
                        {
                            $tip=substr($tip,0,-2);
                        }
                        if (0==$x ? $sel_class='sqb_sel' : $sel_class='' );
                        $seq.="<span id='".$slider_name."_sqb".$x."' class='sqb ".$sel_class."' title='".$tip."' ";
                        $seq.="onclick='slide_to(\"".$slider_name."\",".$x.")'>move to ".($x+1)."</span>";
                }

            // dead panel, let the user know there are no more
                /*if ($panel_width>$dimensions['width'])
                {*/
                    $html.="<span class='dead_panel slh' style='width:".$dead_panel_width."px;'>";
                    $html.="";
                    $html.="</span>";
                /*}*/

            // close the slider panel
                $html.="</div>";
                $html.="</div>";
                $html.="</div>";

            // close whole thing
                $seq.="</div>";
                $html.="</div>";

            // hard code the slide distance in javascript for the functions to use
                $html.="<span class='hide' id='".$slider_name."_slide_distance'>".$slide_distance."</span>";
                $html.="<span class='hide' id='".$slider_name."_tps'>0</span>";
                $html.="<span class='hide' id='".$slider_name."_count_of'>".$panel_sets."</span>";

        // build the return array
            $return_array=array(
                'slider'=>$html,
                'buttons'=>$buttons,
                'sequence'=>$seq,
                'count'=>$ocount,
                'close'=>"<div id='close_slider' class='sprite'>X</div>"
            );

        return $return_array;
    }

    /* *************************************************************************
         slider_panel() -
         @param string
         @param numeric
         @param array
         @return
    */
    public function slider_panel($p,$dimensions,$slider_name,$pcount)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_slider_panel_start');

        $phtml='';

        // get the full width of the panel
            $panel_full=$this->panel_full($dimensions);

        // open panel
            $phtml.="<div class='spnl spnl".$pcount."' style='width:".$dimensions['single_panel']."px;height:".($dimensions['height']-($panel_full-$dimensions['single_panel']))."px;'>";

        // panel contents
            $phtml.=$p['html'];

        // close panel
            $phtml.="</div>";

        return $phtml;

        /* BENCHMARK */ $this->benchmark->mark('func_slider_panel_end');
    }
    /* *************************************************************************
         panel_full() - adds two dimension value to get the actual panel width value
         @param string
         @param numeric
         @param array
         @return
    */
    public function panel_full($dimensions)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_panel_full_start');

        return $dimensions['single_panel']+$dimensions['css_width_add'];

        /* BENCHMARK */ $this->benchmark->mark('func_panel_full_end');
    }
}



























