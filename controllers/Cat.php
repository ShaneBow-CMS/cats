<?php defined('BASEPATH') OR exit('No direct script access allowed');
/********************************************************************
* @(#)Cat.php 20190219
* Copyright © 2019 by Richard T. Salamone, Jr. All rights reserved.
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

	public function index() {
		$this->lang->load('shop', $this->session->userdata('lang'));
		$data['title'] = 'Content Categories';
		$data['nodes'] = $this->mcats->get_cat_tree();
		$data['cats'] = $data['nodes'];
		$this->load->view('admin/cat-tree', $data);
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
			$this->mcats->write_cattree_div();
			}
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $id);
		}

	public function fetch($id) {
		$this->load->helper('ajax');
		try { $data = $this->mcats->get($id); }
		catch (Exception $e) {db_error($e->getMessage());}
		respond(0, "SUCCESS", $data);
		}

	public function seed() {
		$this->require_role(ROLE_ADMIN);
		$this->load->model('mseeder');
		$this->mseeder->reseed_tables('Create Category Tables', [
			'cats'
			]);
		$this->mcats->write_cattree_div();
		}
	}
/* _lib/cms/cats/controllers/Cat.php */
