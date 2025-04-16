<?php (defined('BASEPATH')) OR exit('No direct script access allowed');
/********************************************************************
* @(#)Mcats.php
* Copyright (c) 2017-2022 by Richard T. Salamone, Jr. All rights reserved.
*
* Model for dynamic category tree in UBOW web sites.
*
* @author Rick Salamone
* @version 20190220
* 2017     rts created
* 20190220 rts added UDB_PREFIX to 'users' table sql calls
* 20220907 rts handles absolute path name for slug
*******************************************************/

class Mcats extends MY_Model {
	protected $table_name = 'cats';

	public function __construct() {
		parent::__construct();
		}

	private function _url($slug) {
		return $slug[0] == '/'? $slug : "/page/category/$slug";
		}

	/**
	* append a node as last child
	* $post must include the parent_id
	***********************************/
	public function add_node($post) {
		$tree = 'cats';
		$id_parent = $post['parent_id'];
		$parent = $this->db
			->get_where($tree, ['id' => $id_parent])
			->row_array();
		if (!$parent)
			throw new Exception("parent node not found");
		$id_page = $post['id_page'];

		$lft = $parent['rgt'];
		$node = [
			'lft'  => $lft,
			'rgt'  => $lft + 1,
			'title' => $post['title'],
			'slug' => $post['slug'],
			'lead'  => $post['lead'],
			'icon' => $post['icon'],
			'id_page' => $id_page
			];
		// update the cat's page's parent
		if ($id_page > 0)
			$this->db->query("UPDATE `pages` SET cid=$id_parent WHERE id=$id_page");

		// Make room in the tree for the new node
		$pos = $lft - 1;
		if ($this->db->query("UPDATE `$tree` SET rgt=rgt+2 WHERE rgt>$pos")
		&&  $this->db->query("UPDATE `$tree` SET lft=lft+2 WHERE lft>$pos")) {
			$id = $this->ins_id($tree, $node, FALSE); // ...then add the new node
			if ($id) return $id;
			}
		$this->_throwError();
		}

	/**
	* Get one node
	***************/
	public function get_node($where) {
		return $this->db
			->get_where('cats', $where)
			->row_array();
		}

	/**
	* Get entire tree
	* including the root
	* and a count of pages in each category
	****************************************/
//	public function get_cat_tree($flds='cats.id,cats.slug,cats.title,icon,cats.lead,lft,rgt') {
	public function get_cat_tree($flds='cats.*') {
		return $this->db->select($flds.',COUNT(pages.cid) as numpages')
			->join('pages', 'pages.cid = cats.id','left outer')
			->group_by('cats.id')
			->order_by('lft')
			->get('cats')
			->result_array();
		}

	/**
	* Get all descendants of a node
	* @param $parent either a node id or a node
	********************************************/
	public function between($lft, $rgt, $and_where=FALSE) {
		$where = 'lft BETWEEN '.$lft.' AND '.$rgt;
		if ($and_where) $where .= ' AND '.$and_where;
		return $this->db->select('cats.*,COUNT(pages.cid) as numpages')
			->join('pages', 'pages.cid = cats.id','left outer')
			->group_by('cats.id')
	//		->order_by('cats.lft')
			->get_where('cats', $where)
			->result_array();
		}

	public function get_descendants($parent, $and_where=FALSE) {
		if (gettype($parent) != 'array') { // passed in id
			$parent = $this->db
				->get_where('cats', ['id' => $parent])
				->row_array();
			if (!$parent)
				throw new Exception("parent node not found");
			}
		return $this->between($parent['lft']+1, $parent['rgt'], $and_where);
		}

	public function get_subtree($slug) {
		$parent = $this->db
			->get_where('cats', ['slug' => $slug])
			->row_array();
		if (!$parent)
			throw new Exception("parent node not found");
		return $this->between($parent['lft']+1, $parent['rgt']);
		}

	/**
	* filter out any cats that have an
	* assigned display page (ie id_page != 0)
	* and that page is NOT published
	*************************************/
	public function get_published_cats() {
		$sql = 'SELECT cats.*, IFNULL(display_page.flags, 0) as pflags'
			.',((IFNULL(display_page.flags, 128) & 128) = 128) as publish'
			.', COUNT(child_pages.cid) as numpages FROM cats'
			.' LEFT OUTER JOIN pages child_pages ON child_pages.cid = cats.id' // only direct, not grandkids...
			.' LEFT OUTER JOIN pages display_page ON display_page.id = cats.id_page'
			.' GROUP BY cats.id ORDER BY lft';

		$cats = $this->db
			->query($sql)
			->result_array();

/**************
		$cats = $this->db->select('cats.*'
				. ',IFNULL(display_page.flags,0) as pflags'
				. ',((IFNULL(display_page.flags,128) & 128) = 128) as publish'
				. ',COUNT(p_in_cat.cid) as numpages'
				)
			->join('pages p_in_cat', 'p_in_cat.cid = cats.id','left outer')
			->join('pages display_page', 'display_page.id = cats.id_page','left outer')
			->group_by('cats.id')
			->order_by('lft')
			->get('cats')
			->result_array();
**************/

		$nodes = [];
		foreach ($cats as $c) {
			if ($c['publish'])
				$nodes[] = $c;
			}
		return $nodes;
		}

	/**
	* update one node's info
	* does NOT change tree hierarchy
	*********************************/
	public function update_info($id, $info) {
		$this->db->where('id', $id);
		return $this->db->update('cats', $info);
		}

	/**
	* format one category title/icon
	* for fwrite_nav_tree() below
	*********************************/
	private function _entry($node) {
		$icon = $node['icon']? '<i class="icon-'.$node['icon'].'"></i> ' : '';
		return $icon.'<b>'.$node['title'].'</b>';
		}

	function fwrite_nav_tree($file_name = 'cattree2.div') {
		$this->load->library('Tree');
		$nodes = $this->get_published_cats(); // with root
		unset($nodes[0]); // remove root node

		$fileSpec = realpath(APPPATH.'../public_html/assets/files/').'/'. $file_name;
		$data['filespec'] = $fileSpec;
		$file = fopen($fileSpec, 'w');
		$write = function($str) use($file) {
			fwrite($file, $str);
			};

		$get_attrs = function($row) { return ''; };

		$format = function($row, $has_children) {
			$slug = $row['slug'];
			$name = $this->_entry($row);
			$html = '';
			if ($has_children) { // if node has kids
				$html .= PHP_EOL.' <input type="checkbox" id="'.$slug.'" />';
				$html .= PHP_EOL.' <label for="'.$slug.'" class="tree_label"><a href="'.$this->_url($slug).'">'.$name.'</a></label>';
				}
			else $html.= '<a href="'.$this->_url($slug).'" class="tree_label">'.$name.'</a>';
			return $html;
			};
		$this->tree->build($nodes, $format, $get_attrs, $write);
		fclose($file);
		return $fileSpec;
		}


	// return an html string containing tree: <ul>...</ul>
	// @TODO only gets published cats
	function build_ddrop_tree() {
		$this->load->library('Tree');
		$nodes = $this->get_published_cats(); // with root
		unset($nodes[0]); // remove root node
		$htm = '<ul>';
		$write = function($str) use(&$htm) {
			$htm .= $str;
			};
		$get_attrs = function($row) {
			return 'val="'.$row['id'].'"';
			};

		$format = function($row, $has_children) {
			$icon = '<i class="icon-'.$row['icon'].'"></i>';
			$name = $row['title'];
			$html = $icon.' '.$name;
			if ($has_children) { // if node has kids
				$html = '<a>'.$html.'</a>'.PHP_EOL;
				}
			return $html;
			};
		$this->tree->build($nodes, $format, $get_attrs, $write);
		return $htm.'</ul>';
		}

	/**
	* Update the tree structure, where $nodes looks like
	*	(1, 1, 8, "Cat 1"),(2, 9, 10, "Cat 2"), ... , (id, lft, rgt, title)
	*/
	function bulk_update_struct($nodes) {
		return $this->db->query(
			"INSERT INTO cats (id, lft, rgt, title) VALUES "
			. $nodes
			. ' ON DUPLICATE KEY UPDATE lft=VALUES(lft), rgt=VALUES(rgt);'
			);

		}

	}
/* ~~cms/cats/models/Mcats.php */