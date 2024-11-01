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
		$parent = $this->db
			->get_where($tree, ['id' => $post['parent_id']])
			->row_array();
		if (!$parent)
			throw new Exception("parent node not found");

		$lft = $parent['rgt'];
		$node = [
			'lft'  => $lft,
			'rgt'  => $lft + 1,
			'title' => $post['title'],
			'slug' => $post['slug'],
			'lead'  => $post['lead'],
			'icon' => $post['icon'],
			'content' => $post['content']
			];
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
	********************/
	public function get_cat_tree($flds='cats.id,cats.slug,cats.title,icon,cats.lead,lft,rgt') {
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
	public function between($lft, $rgt) {
		$where = 'lft BETWEEN '.$lft.' AND '.$rgt;
		return $this->db->select('cats.*,COUNT(pages.cid) as numpages')
			->join('pages', 'pages.cid = cats.id','left outer')
			->group_by('cats.id')
	//		->order_by('cats.lft')
			->get_where('cats', $where)
			->result_array();
		}

	public function get_descendants($parent) {
		if (gettype($parent) != 'array') { // passed in id
			$parent = $this->db
				->get_where('cats', ['id' => $parent])
				->row_array();
			if (!$parent)
				throw new Exception("parent node not found");
			}
		return $this->between($parent['lft']+1, $parent['rgt']);
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
	* update one node's info
	* does NOT change tree hierarchy
	*********************************/
	public function update_info($id, $info) {
		$this->db->where('id', $id);
		return $this->db->update('cats', $info);
		}

	/**
	* format one category title/icon
	* for the _cattree function below
	*********************************/
	private function _entry($node) {
		$icon = $node['icon']? '<i class="icon-'.$node['icon'].'"></i> ' : '';
		return $icon
		//	.'<i class="lft">'.$node['lft'].'</i>'
			.'<b>'.$node['title'].'</b>';
		//	.'<i class="rgt">'.$node['rgt'].'</i>';
		}

	/*
	* @deprecated write_cattree_div
	* create /public_html/assets/files/cattree.div
	* which contains categories as nested <ul>
	* It is loaded into site pages as a 'view fragment'
	* Avoids need to access/build 'all cats' for each page
	*********************************/
	function write_cattree_div() {
		$nodes = $this->get_cat_tree(); // with root
		$this->fwrite_nav_tree($nodes, 'cattree2.div')
		}

	function build_editor_tree($nodes) {
		$this->load->library('Tree');
		$htm = '<ul>';
		$write = function($str) use(&$htm) {
			$htm .= $str;
			};
		$get_attrs = function($row) {
			return 'lft="'.$row['lft'].'" rgt="'.$row['rgt'].'"'
				.' node-id="'.$row['id'].'"'
				.' title="'.$row['lead'].'"';
			};
		$format = function($row, $has_children) {
			$icon = '<i class="icon-'.$row['icon'].'"></i> ';
			return $icon.'<i class="lft">'.$row['lft'].'</i> <b>'.$row['title'].'</b> <i class="rgt">'.$row['rgt'].'</i>';
			};
		$this->tree->build($nodes, $format, $get_attrs, $write);
		return $htm.'</ul>';
		}

	function fwrite_nav_tree($nodes = null, $file_name = 'cattree2.div') {
		if (!$nodes) {
			$nodes = $this->get_cat_tree(); // with root
			}
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
		}

	}
/* ~~cms/cats/models/Mcats.php */