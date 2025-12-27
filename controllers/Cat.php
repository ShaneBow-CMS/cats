<?php defined('BASEPATH') OR exit('No direct script access allowed');
/********************************************************************
* @(#)Cat.php 20190219
* Copyright � 2019 by Richard T. Salamone, Jr. All rights reserved.
*
* CI Controller for dynamic cat content in UBOW web sites.
*
* @author Rick Salamone
* @version 20190219
* 20190327 rts builds cattree.div
* 20190219 rts created from Blog controller
* 20190315 rts ability to update category
*******************************************************/

class Cat extends MY_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('mcats');
		}

	private function writeTree($fileSpec, $nodes) {
		$this->tree->write_full($fileSpec, $nodes); // $nodes WITH root
		}

	public function index() {
		redirect('/sitemap', 'refresh');
		}

	public function manage() {
		$this->require_min_level(MIN_ADMIN_LEVEL);
		$this->lang->load('shop', $this->session->userdata('lang'));
		$data['title'] = 'Content Categories';
		$nodes = $this->mcats->get_cat_tree();
		$data['nodes'] = $nodes;
		$data['cats'] = $data['nodes'];

		// Write Nav tree - for future reference
	//	$nav_file_name = 'testtree3.div';
	//	$data['nav_tree'] = $this->mcats->fwrite_nav_tree($nav_file_name);

		$this->load->view('admin/cms-cats-manager', $data);
		}

	/**
	* add or update a cat - AJAX
	*/
	public function post() {
		$this->load->helper('ajax');
		$usr = $this->session->userdata('usr');
		if (!$this->_usr)
			respond(EXIT_ERR_LOGIN, "Log in required", '/user/login');
		$this->load->library('form_validation');
		if ($this->form_validation->run('cat-post') == FALSE)
			dieInvalidFields();

		try {
			$id = $_POST['id'];
			unset($_POST['id']);
			if( !$id )
				$id = $this->mcats->add_node($_POST);
			else
				$this->mcats->update_info($id, $_POST);
			$tree = $this->mcats->get_cat_tree(); // all nodes (for admin)
			$this->mcats->fwrite_nav_tree('cattree2.div'); // only published nodes!! (for users)
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $tree);
		}

	public function set_mid() {
		$this->load->helper('ajax');
		$usr = $this->session->userdata('usr');
		if (!$this->_usr)
			respond(EXIT_ERR_LOGIN, "Log in required", '/user/login');

		try {
			$id = $_POST['id'];
			unset($_POST['id']);
			if( !$id ) respond(EXIT_ERROR, "missing id", '');
			$this->mcats->update_info($id, $_POST);
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", '');
		}

	public function fetch($id) {
		$this->load->helper('ajax');
		try {
			switch ($id) {
				case '0': // all nodes (with root)
					$data = $this->mcats->get_cat_tree();
					break;
				case 'pub':
					$data = $this->mcats->get_published_cats(); // with root
					break;
				default:
					$data = $this->mcats->get($id);
					break;
					}
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $data);
		}

	public function save_struct() {
		$this->load->helper('ajax');
		try { $data = $this->mcats->bulk_update_struct($_POST['struct']); }
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $data);
		}

	/**
	* force update of the sidebar nav-tree
	***************************************/
	public function fwrite_nav_tree() {
		$this->load->helper('ajax');
		try { $filespec = $this->mcats->fwrite_nav_tree(); }
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "Updated", $filespec);
		}

	/**
	* cat tree for DrillDrop select
	*********************************/
	public function tree() {
		die($this->mcats->build_ddrop_tree());
		}

	public function seed() {
		$this->require_role(ROLE_ADMIN);
		$this->load->model('mseeder');
		$this->mseeder->reseed_tables('Create Category Tables', [
			'cats'
			]);
		$this->mcats->fwrite_nav_tree('cattree2.div');
		}
	}
/* _lib/cms/cats/controllers/Cat.php */
