<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @author		Toni Leigh Sharpe
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 * @license     granted to be used by COMPANY_NAME only
 *              granted to be used only for PROJECT_NAME at URL
 *              COMPANY_NAME is free to modify and extend
 *              COMPANY_NAME is not permitted to copy, resell or re-use on other projects
 *              this license applies to all code in the root folder and all sub folders of
 *                  PROJECT_NAME that also exists in the corresponding folder(s) in the
 *                  copy of PROJECT_NAME kept by Toni Leigh Sharpe at sign off, even if
 *                  modified by COMPANY_NAME or their third party consultants
 *                  any copy of this code found without a corresponding copy in
 *                  Toni Leigh Sharpe's repository at http://bitbucket.org/Toni Leighsharpe will be
 *                  considered as copied without permission
 *                  (NB - does not apply to code covered GPL or similar, an example being jQuery)
 *              THIS CODE COMMENT MUST REMAIN INTACT IN ITS ENTIRITY
*/
    class Wordcloud_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
     build_cloud() -
     @param string
     @param numeric
     @param array
     @return
    */
    public function build_cloud($cloud_id,$colours,$remove_words,$query,$column,$shuffle=1)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_build_cloud_start');

        // get the words
            $words=$this->word_array($remove_words,$query,$column,$shuffle);

        // find out which word is most common - needed for formating the font size
            $largest_value=0;

            foreach ($words as $k=>$v)
                if ($v>$largest_value)
                    $largest_value=$v;

        // get the colour differences for the rgb cycle calculation
            /* $colour_dif=array(
                'red'=>($colour1['red']-$colour2['red']),
                'green'=>($colour1['green']-$colour2['green']),
                'blue'=>($colour1['blue']-$colour2['blue'])
            ); */

        // build the word cloud html
            $words_html='';
            $c=1;
            foreach ($words as $k=>$v)
            {
                if ($k!="-" &&
                    $k!='')
                {
                    if ($v>2)
                    {
                        // set the rgb values
                            /* $colour=array();
                            $colour['red']=abs(ceil($colour1['red']-($colour_dif['red']/($c%5+1))));
                            $colour['green']=abs(ceil($colour1['green']-($colour_dif['green']/($c%5+1))));
                            $colour['blue']=abs(ceil($colour1['blue']-($colour_dif['blue']/($c%5+1)))); */

                            $colour['red']=$colours['red'][$c%count($colours['red'])];
                            $colour['green']=$colours['green'][$c%count($colours['green'])];
                            $colour['blue']=$colours['blue'][$c%count($colours['blue'])];

                        // set the font size
                            if ($v<=5)
                                $font_size=number_format(1.3-(2/$v),1);
                            else
                                $font_size=number_format(1.3+(($v-5)*0.1),1);

                        // add to our html for this word
                           $words_html.="<span class='cloud_word' style='font-size:".$font_size."em;font-weight:bold;color:rgb(".$colour['red'].",".$colour['green'].",".$colour['blue'].");'>".$k."</span> "; // |".$k."| [".$v."] [F:".$font_size."]

                        $c++;
                    }
                }
            }

        // finally save the word cloud as html to a database table so we aren't building it every time page request
        // we only save the word cloud when a user does something that will effect it (add, edit, delete)
            $update_data = array(
                'wordcloud_html' =>$words_html
            );

            $this->db->where('wordcloud_id', $cloud_id);
            $this->db->update('wordcloud', $update_data);


        /* BENCHMARK */ $this->benchmark->mark('func_build_cloud_end');
    }

    /* *************************************************************************
         word_array() -
         @param array $remove - words to be removed from the cloud word array
         @return
    */
    public function word_array($remove,$query,$column,$shuffle)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_word_array_start');

        // we use the same strip function to get rid of all the words like 'as' etc and some punctuation, returning an array of words
            $this->load->model('search_model');

        // two arrays one for the completed word array, one to remove punctuation
            $word_array=array();
            $punctuation=array(",",".","/",";","!","'","-","&mdash;","&ndash;");

        foreach ($query as $row)
        {
            // gets the array of words, no prepositions etc, removes html
                $words=$this->search_model->term_array(json_sanitise($row[$column]));

            if (1==$shuffle)
            {
                shuffle($words);
            }

            foreach ($words as $word)
            {
                // removes punctuation
                    $word=str_replace($punctuation,"",strtolower($word));

                // concatenates similar words for output
                    if ($word=="thank") $word="thanks";
                    if ($word=="enjoyed"||$word=="enjoyable") $word="enjoy";

                // build up the array counting each instance of each word
                    if (!in_array($word,$remove))
                        if (array_key_exists($word,$word_array))
                            $word_array[$word]++;
                        else
                            $word_array[$word]=1;
            }
        }

        // return the word array
            return $word_array;

        /* BENCHMARK */ $this->benchmark->mark('func_word_array_end');
    }
}
