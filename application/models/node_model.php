<?php
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    require_once (APPPATH.'models/universal_model.php');
/*
 class Node_model

 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
*/
    class Node_model extends Universal_model {

    public function __construct()
    {
        parent::__construct();
    }

    /* *************************************************************************
         get_node () - retrieves node details from the database
         @param string $id - the node url identifier
         @return array - the node from the node table
    */
    public function get_node($id,$type=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func__start');

        if (is_numeric($id) ? $where=array('id' => $id) : $where=array('url' => $id) );

        if ($type==null)
        {
            // just get the basic node details
            $node=$this->db->get_where('node',$where)->row_array();
        }
        else
        {
            // id key (we use user_id for user table, node_id for everything else)
                if ($type=='user' ? $id_key='user_id' : $id_key='node_id' );

            // get from the details table also
                $this->db->select('*');
                $this->db->from('node');
                $this->db->where($where);
                $this->db->join($type, "node.id = ".$type.".".$id_key);
                $node=$this->db->get()->row_array();
        }

        $node['flag_id']=$node['type'].'_'.$node['id'];

        return $node;

        /* BENCHMARK */ $this->benchmark->mark('func__end');
    }

    /* *************************************************************************
         get_node_details() - function to retrieve the html text from the appropriate node table (page, user, product etc.)
         @param array $node - the node whose html should be retrieved
         @return string $node_html - html details of the node
    */
    public function get_node_details($node)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_node_details_start');

        if (!is_array($node))
        {
            echo "node is not set in node model, probably because a bad URL has been entered";
            die();
        }
        else
        {
            // id key (we use user_id for user table, node_id for everything else)
                if ($node['type']=='user' ? $id_key='user_id' : $id_key='node_id' );

            // database query to get the details from the individual node type table
                $node_details=$this->db->select('*')->from($node['type'])->where(array($id_key=>$node['id']))->get()->row_array();

            return $node_details;
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_node_details_end');
    }

    /* *************************************************************************
         get_nodes() - gets zero to many nodes from the node table
         @param array $where_clauses - an array of where clauses
         @param $joined - will get from the type table too - used by admin
         @param $order_by - set an order by for this node group
         @return array $nodes -  a set of nodes that match the where clauses
    */
    public function get_nodes($where_clauses=array(),$joined=null,$order_by=null,$limit=null)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_nodes_start');

        // get the type of node to retrieve, could be null
            if (isset($where_clauses['type']))
            {
                $type=$where_clauses['type'];
            }
            else
            {
                $type='';
                if ($joined!=null)
                {
                    dev_dump('type must be set in where array if a joined record set is required');
                }
            }

        // default the order by if it isn't explicitly set
            if (null==$order_by)
            {
                // set order by to node_order, then name as master default
                // this way name will come into play if node_orders are all 0s which is how they default
                    $order_by='node_order,name';

                // get config order bys and set if present
                    $order_bys=$this->config->item('default_list_order');
                    if (isset($order_bys[$type]))
                    {
                        $order_by=$order_bys[$type];
                    }
            }

        // get the nodes, either with a join or without
            if (null==$joined)
            {
                if (is_array($where_clauses))
                {
                    $nodes=$this->db->select('*')->from('node')->where($where_clauses);
                }
                else
                {
                    $nodes=$this->db->select('*')->from('node')->where($where_clauses,null,false);
                }
                $nodes=$nodes->order_by($order_by);
                if ($limit!=null &&
                    is_numeric($limit))
                {
                    $node=$nodes->limit($limit);
                }
                $nodes=$nodes->get()->result_array();
            }
            else
            {
                if ('user'==$type ? $id_key='user_id' : $id_key='node_id' );
                if (is_array($where_clauses))
                {
                    $nodes=$this->db->select('*')->from('node')->where($where_clauses)->join($type,'node.id='.$type.'.'.$id_key);
                }
                else
                {
                    $nodes=$this->db->select('*')->from('node')->where($where_clauses,null,false)->join($type,'node.id='.$type.'.'.$id_key);
                }
                $nodes=$nodes->order_by($order_by);
                if ($limit!=null &&
                    is_numeric($limit))
                {
                    $node=$nodes->limit($limit);
                }
                $nodes=$nodes->get()->result_array();
            }

        // add human type and flag
            for ($x=0;$x<count($nodes);$x++)
            {
                $nodes[$x]['human_type']=$this->get_human_type($nodes[$x]['type']);
                $nodes[$x]['flag_id']=$nodes[$x]['type']."_".$nodes[$x]['id'];
            }

        /* BENCHMARK */ $this->benchmark->mark('func_get_nodes_end');

        return $nodes;
    }

    /* *************************************************************************
         nodes_by_id() - returns the nodes as an array keyed by their ids, used for easier referencing
         @param string
         @param numeric
         @param array
         @return
    */
    public function nodes_by_id($nodes)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_nodes_by_id_start');

            $return_nodes=array();

            foreach ($nodes as $n)
            {
                $return_nodes[$n['id']]=$n;
            }

        /* BENCHMARK */ $this->benchmark->mark('func_nodes_by_id_end');

        return $return_nodes;
    }

    /* *************************************************************************
         get_template_views() - gets sets of views for building the frame that sits around node specific data
         @param string $key - the key on which to retrieve view lists from the db, 'head' or 'foot' usually
         @return array $views - an array of views
    */
    public function get_template_views($key)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_template_views_start');

        $tviews=$this->config->item('template_views');

        if (is_array($tviews))
        {
            // does some stuff to mimic the old db way of doing things
                $views=$tviews[$key];

                $tviews=array();

                for($x=0;$x<count($views);$x++)
                {
                    $tviews[$x]['view']=$views[$x];
                }

                return $tviews;
        }
        else
        {
            // supports old sites
                $query=$this->db->select('*')->from('template_fragment')->where(array('tf_type'=>$key))->order_by('tf_order');
                $result=$query->get();
                return $result->result_array();
        }

        /* BENCHMARK */ $this->benchmark->mark('func_get_template_views_end');
    }

    /* *************************************************************************
         get_human_type() - gets the type in a human readable format
         @param string
         @param numeric
         @param array
         @return
    */
    public function get_human_type($type)
    {
        /* BENCHMARK */ $this->benchmark->mark('func_get_human_type_start');

        $this->load->model('project_data_array_model');

        // basic nodes
            switch ($type)
            {
                case 'blog':
                    $human_type='blog post';
                    break;
                case 'mediaset':
                    $human_type='media set';
                    break;
                case 'groupnode':
                    $human_type='group';
                    break;
                case 'seoarticle':
                    $human_type='seo article';
                    break;
                default:
                    $human_type=$type;
                    break;
            }

        // then specific nodes to this site
            $human_type=$this->project_data_array_model->set_specific_type_format($type,$human_type);

        return $human_type;

        /* BENCHMARK */ $this->benchmark->mark('func_get_human_type_end');
    }

	/* *************************************************************************
		get_category() - retrieves a category, used to see if a url portion refers to
			a category
		@param string $category - the category to check for
		@return query array the category
	*/
	public function get_category($category)
	{
		$query=$this->db->select('*')->from('node')->where(array('url'=>$category,'type'=>'category'));
		$res=$query->get();
		return $res->row_array();
	}

    /* *************************************************************************
         get_test_nodes() -
         @return
    */
    public function get_latest_nodes()
    {
        return $this->get_nodes(array('type'=>'product','visible'=>1),null,'created desc');
    }

    /* *************************************************************************
         get_sale_nodes() -
         @return
    */
    public function get_sale_nodes()
    {
        $now=date('Y-m-d',time());

        return $this->get_nodes(array('type'=>'product','visible'=>1,'sale_start <='=>$now,'sale_end >='=>$now),null,'created desc');
    }

    /* *************************************************************************
         get_blog_posts() -
         @return
    */
    public function get_blog_posts()
    {
        $query=$this->db->select('*')->from('node')->where(array('type'=>'blog','visible'=>1))->order_by('created desc');
        $res=$query->get();
        $result=$res->result_array();

        return $result;
    }
}
