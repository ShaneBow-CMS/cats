# Tree.php

This is a php library for generating *Net Set Model' trees.

You can control the formatting and where the output goes via
 the `build()` method.

Here are a couple examples from the `Mcats` model...

## Navigation Tree

This one is written to disk as a page fragment whenever the
 site categories change (which is very infrequent).

~~~php
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
~~~

## Editor Tree

This version is called to generate DOM markup for backend tree
 editing. (Actually it has now been superceded by a client-side
 Javascript editor).

~~~php
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
~~~
