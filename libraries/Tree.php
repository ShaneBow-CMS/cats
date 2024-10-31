<?php defined('BASEPATH') OR exit('No direct script access allowed');
/********************************************************************
* @(#)Tree.php	1.00 20160501
* Copyright © 2016 by Richard T. Salamone, Jr. All rights reserved.
*
* Tree: Lightweight tree library
*
* @author Rick Salamone
* @version 1.00, 20160501 rts created
*******************************************************/

class Tree {

	function __construct() {
		//	parent::__construct();
		log_message('info', 'Loaded tree library');
		// Note: $CI =& get_instance();
		}

	/*
	* format one category title/icon
	* for the _cattree function below
	private function _entry($node) {
		$icon = $node['icon']? '<i class="icon-'.$node['icon'].'"></i> ' : '';
		return $icon
		//	.'<i class="lft">'.$node['lft'].'</i>'
			.'<b>'.$node['title'].'</b>';
		//	.'<i class="rgt">'.$node['rgt'].'</i>';
		}

	private function _url($slug) {
		return $slug[0] == '/'? $slug : "/page/category/$slug";
		}
	*********************************/

	/**
	* tree->build(
	*		realpath(APPPATH.'../public_html/assets/files/').'/cattree2.div',
	*		$this->get_cat_tree() // $nodes WITH root
	*		);
	*/
	function build($nodes, $format, $get_li_attrs, $write) {

	//	unset($nodes[0]); // remove root node
		$stack = [];

		// display each row
		foreach($nodes as $row) {

			// check if we should remove nodes from the stack
			while (count($stack) && $stack[count($stack)-1] < $row['lft']) {
				array_pop($stack);
				$write('</ul></li>'.PHP_EOL);
				}

			// display indented node
			$has_kids = $row['rgt'] - $row['lft'] > 1;
			$row['hasChildren'] = $has_kids;
			$attrs = $get_li_attrs($row);
			$html = "<li $attrs>".$format($row, $has_kids);
			if ($has_kids) { // if node has kids
				$html .= PHP_EOL.' <ul>';
				$stack[] = $row['rgt']; // add this node to the stack
				}
			else $html.= '</li>';
			$write($html.PHP_EOL);
			}

		while (count($stack)) {
			array_pop($stack);
			$write('</ul></li>'.PHP_EOL);
			}

		}

/************************************
	function write_full($fname, $nodes) {
		$file = fopen($fname, 'w');

	//	$nodes = $this->get_cat_tree(); // with root
		unset($nodes[0]); // remove root node
		$stack = [];

		// display each row
		foreach($nodes as $row) {

			// check if we should remove nodes from the stack
			while (count($stack) && $stack[count($stack)-1] < $row['lft']) {
				array_pop($stack);
				fwrite($file, '</ul></li>'.PHP_EOL);
				}

			// display indented node
			$row['hasChildren'] = $row['rgt'] - $row['lft'] > 1;
			$attrs = 'lft="'.$row['lft'].'" rgt="'.$row['rgt'].'"'
					.' node-id="'.$row['id'].'"'
					.' title="'.$row['lead'].'"';
$attrs = '';
			$slug = $row['slug'];
			$name = $this->_entry($row);
			$html = "<li $attrs>";
			if ($row['hasChildren']) { // if node has kids
				$html .= PHP_EOL.' <input type="checkbox" id="'.$slug.'" />';
//				$html .= PHP_EOL.' <label for="'.$slug.'" class="tree_label">'.$name.'</label>';
				$html .= PHP_EOL.' <label for="'.$slug.'" class="tree_label"><a href="'.$this->_url($slug).'">'.$name.'</a></label>';
				$html .= PHP_EOL.' <ul>';
				$stack[] = $row['rgt']; // add this node to the stack
				}
			else $html.= '<a href="'.$this->_url($slug).'" class="tree_label">'.$name.'</a></li>';
			fwrite($file, $html.PHP_EOL);
			}

		while (count($stack)) {
			array_pop($stack);
			fwrite($file, '</ul></li>'.PHP_EOL);
			}

		fclose($file);
		}
************************************/

	/**
	* @param filespec, relative to web root (e.g. public_html)
	* @return array of associative arrays
	* where each array[row_number] => ['field0' => value0, ... ]
	*/
	function read($filespec) {
		return (!is_readable($filespec))?
			'<p style="color:red">tree.read: <i>'.$filespec.'</i> is NOT readable</p>'
		:	('<ul class="tree">'.file_get_contents($filespec).'</ul>');
		}

	}

/* End of file application/library/Tree.php */
