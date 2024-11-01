<?php defined('BASEPATH') OR exit('No direct script access allowed');
/********************************************************************
* @(#)Tree.php	1.00 20241031
* Copyright © 2016 by Richard T. Salamone, Jr. All rights reserved.
*
* Tree: Lightweight tree library
*
* @author Rick Salamone
* @version 1.00, 20241031 rts created
*******************************************************/

class Tree {

	function __construct() {
		log_message('info', 'Loaded tree library');
		// Note: $CI =& get_instance();
		}

	/**
	* tree->build(
	*		$nodes - [] of rows with a lft & rgt , $format, $get_li_attrs, $write
	*		realpath(APPPATH.'../public_html/assets/files/').'/cattree2.div',
	*		$this->get_cat_tree() // $nodes WITH root
	*		);
	*/
	function build($nodes, $format, $get_li_attrs, $write) {

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

	/**
	* @param filespec, relative to web root (e.g. public_html)
	* @return the file contents
	*/
	function read($filespec) {
		return (!is_readable($filespec))?
			'<p style="color:red">tree.read: <i>'.$filespec.'</i> is NOT readable</p>'
		:	('<ul class="tree">'.file_get_contents($filespec).'</ul>');
		}

	}
/* End of file application/library/Tree.php */
