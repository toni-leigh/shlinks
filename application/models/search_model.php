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
*/
    class Search_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('node_model');
    }

    /* *************************************************************************
     search() - search the node table for the terms(s) - will start by looking for
        the whole phrase, but if that is not found then splits the phrase into an
        array of words and searches for nodes with any of those in
     @param string $term - the user defined search term
     @return query array $result of search results
    */
    public function search($term,$types=array())
    {
        // type restrict
            if (count($types)>0)
            {
                $tc=count($types);
                $c=1;

                // build type restrict string
                    $tr="(";
                    foreach ($types as $k=>$v)
                    {
                        $tr.="type='".$v."'";

                        // only add 'or' if not the last
                            if ($c<$tc)
                            {
                                $tr.=" or ";
                            }
                        $c++;
                    }

                // close
                    $tr.=") and ";
            }
            else
            {
                $tr="";
            }

        // define which node columns to look at
            $where_str="name regexp '[[:<:]]".$term."[[:>:]]' or ";
            $where_str.="tags regexp '[[:<:]]".$term."[[:>:]]' or ";
            $where_str.="short_desc regexp '[[:<:]]".$term."[[:>:]]'";

            $where=$tr."(".$where_str.")";

        // perform an initial search for the full term as a phrase
            $result=$this->node_model->get_nodes($where);

        // return or try less strict search
            if (count($result))
            {
                return $result;
            }
            else
            {
                // split the term into an array of individual words
                    $term_array=$this->term_array($term);

                // iterate over these words searching for each
                    $ac=count($term_array);
                    $c=1;
                    $where=$tr.'(';
                    foreach ($term_array as $term)
                    {
                        // do each where for each search term
                            $where.=$where_str;

                        // drop the last or if this is the last iteration
                            if ($c<$ac)
                            {
                                $where.=" or ";
                            }
                        $c++;
                    }
                    $where.=')';
                // do this query
                    $order=$this->config->item('default_order_by');
                    $result=$this->node_model->get_nodes($where,null,$order['search']);

                return $result;
            }
    }

    /* *************************************************************************
         term_array() - takes a phrase and converts it into an array of words,
            stripping the punctuation, prepositions and conjunctions
         @param string $term - the phrase to be split
         @return array $terms - the array of words
    */
    public function term_array($term)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_term_array_start');

        $terms=array();

        // arrays for stripping
            $prepositions_conjunctions=array("in addition to","in front of","rather than","as if","as long as","as though",
                                             "according to","because of","by way of","in place of","in regard to","in spite of",
                                             "instead of","on account of","even if","even though","if","if only","in order that",
                                             "now that","throughout","whenever","when","wherever","although","whereas","where",
                                             "while","about","above","across","after","against","around","before","behind","below",
                                             "beneath","besides","beside","between","beyond","by","down","during","except","from",
                                             "inside","into","like","near","off","outside","over","since","through","till","toward",
                                             "under","until","upon","with","without","because","before","once","since","than","though",
                                             "unless","until","and","but","or","yet","that","at","for","nor","in","of","for","to",
                                             "so","on","up","out","as");
            $punctuation=array(",",".","/");

        //sets search temrm to lower case for comparison
            $term=strtolower($term);

        //converts punctuation that may be used to separate into spaces
            $term=str_replace($punctuation," ",$term);

        //explodes the string into an array, broken on space between terms
            $terms_unstripped=explode(" ",$term);

        //removes prepositions and conjunctions
            $terms=array_diff($terms_unstripped,$prepositions_conjunctions);

        return $terms;

        /* BENCHMARK */ $this->benchmark->mark('func_term_array_end');
    }

    /* *************************************************************************
         store_search() - does things to store the values of a search
         @param string $term - the term that was searched for
         @param array $nodes - the nodes returned by the search
         @return
    */
    public function store_search($term,$nodes)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_store_search_start');

        // count the results
            $result_count=count($nodes);

        // get the seraching user id
            if (isset($this->user['user_id']) ? $user_id=$this->user['user_id'] : $user_id=0 );

        // store
            $insert_data=array(
                'user_id'=>$user_id,
                'search_terms'=>$term,
                'result_count'=>$result_count
                );
            $this->db->insert('search',$insert_data);

        /* BENCHMARK */ $this->benchmark->mark('func_store_search_end');
    }
}
