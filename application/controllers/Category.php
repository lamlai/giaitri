<?php
/**
 *
 */
class Category extends CI_Controller {

	function __construct() {
		parent::__construct();
		session_start();
		date_default_timezone_set('Asia/Bangkok');
	}

	function index() {
		$this -> load -> model('Categories_model');
		$where = array();
		$like = array();
		if ($this -> input -> get('action')) {
			$action = $this -> input -> get('action');
			switch ($action) {
				case 'delete' :
				$id = intval($this -> input -> get('id'));
				if ($id) {
					$this -> load -> model('User_model');
					$this -> M_user -> remove_user_by_id($id);
					$data['alert_state'] = ALERT_STATE_SUCCESS;
					$data['alert_msg'] = DEL_SUCCESS_MSG;
				}
				break;

				default :
				break;
			}
		}

		if ($this -> input -> get('show')) {
			$show = $this -> input -> get('show');
			switch ($show) {
				case 'actived' :
				$where['state'] = ACTIVED_STATE;
				break;

				case 'disabled' :
				$where['state'] = DISABLED_STATE;
				break;

				case 'reg_today' :
				$where['date(last_login)'] = date('Y-m-d', time());
				break;

				case 'sig_today' :
				$where['date(date_join)'] = date('Y-m-d', time());
				break;

				default :
				break;
			}
		}

		if ($this -> input -> get('key_q') && $this -> input -> get('q')) {
			$key_q = $this -> input -> get('key_q');
			$q = $this -> input -> get('q');
			switch ($key_q) {
				case 'id' :
				$where['id'] = $q;
				break;

				case 'name' :
				$like['name'] = $q;
				break;

				default :
				break;
			}
		}
		//config and init pagination
		$config = array();
		$config['total_rows'] = $this -> Categories_model -> total($where, $like);
		//end config and init pagination

		$page = $this -> input -> get('page') ? $this -> input -> get('page') : 1;
		$order = $this -> input -> get('order') ? $this -> input -> get('order') : 'DESC';
		$per_page = $this -> input -> get('per_page') ? $this -> input -> get('per_page') : 5;

		$data['cat_list'] = $this -> Categories_model -> get_categories("*", $where, $like, ($page - 1) * $per_page, $per_page, array('id' => $order));
		$data['base_url'] = base_url() . 'admin/category/?order=' . $order;
		$data['sort'] = $order;
		$data['next_sort'] = $order == 'ASC' ? 'DESC' : 'ASC';

		$config['base_url'] = $data['base_url'];
		$config['per_page'] = $per_page;
		$this -> pagination -> initialize($config);
		$data['page_link'] = $this -> pagination -> create_links();
		$data['add_link'] = base_url() . 'category/add';
		$data['edit_link']=base_url().'category/edit/';
		$data['back_link']=base_url().'category';
		$data['title'] = 'Quản lí chuyên mục';
		$this -> load -> view('back_end/main_category', $data);
	}

	function getAjaxCategory(){
		$this->load->model('Categories_model');
		$cat_list = $this -> Categories_model -> get_categories_availabel(0, 100);
		foreach ($cat_list as $r) {
			echo '<option value="'.$r->id.'">'.$r->name.'</option>';
		}
	}


	function add() {
		$this -> load -> model('Categories_model');
		if (isset($_POST['txtName']) && isset($_POST['token'])) {
			if(isset($_SESSION['token']) && ($_SESSION['token']==$this->input->post('token'))){
				$txtName = strval($this -> input -> post('txtName'));
				$txtOrderTopMenu = intval($this -> input -> post('txtOrderTopMenu'));
				$txtParentId = intval($this -> input -> post('txtParentID'));
				$txtState = intval($this -> input -> post('txtState'));
				$data_array = array('name' => $txtName, 'order_top_menu' => $txtOrderTopMenu, 'parent_id' => $txtParentId, 'state' => $txtState);
				$this -> Categories_model -> insert_categories($data_array);
				$data['alert_state'] = ALERT_STATE_SUCCESS;
				$data['alert_msg'] = ADD_SUCCESS_MSG;
		}//end if
	}//end if
	//end insert data

	$data['cat_parent_list'] = $this -> Categories_model -> get_categories("*", array('state' => ACTIVED_STATE), array(), 0, 100, array('id' => 'ASC'));
	$data['title'] = "Thêm mới chuyên mục";
	$data['back_link']= base_url().'category';
	$this->load->library('ultils');
	$data['token']=$this->ultils->_generate_unqid_token();
	$_SESSION['token']=$data['token'];
	$this -> load -> view('back_end/form/frmAddCategory', $data);
}

function edit() {
	if ($this -> uri -> segment(3)) {
		$id = intval($this -> uri -> segment(3));
		if ($id) {
			$this -> load -> model('Categories_model');
			if (isset($_POST['txtName'])) {
				$txtName = strval($this -> input -> post('txtName'));
				$txtOrderTopMenu = intval($this -> input -> post('txtOrderTopMenu'));
				$txtParentId = intval($this -> input -> post('txtParentID'));
				$txtState = intval($this -> input -> post('txtState'));
				$data_array = array('name' => $txtName, 'order_top_menu' => $txtOrderTopMenu, 'parent_id' => $txtParentId, 'state' => $txtState);
				$this -> Categories_model -> update_categories($data_array, array('id' => $id));
				$data['alert_state'] = ALERT_STATE_SUCCESS;
				$data['alert_msg'] = EDIT_SUCCESS_MSG;
			}
			$data['category'] = $this -> Categories_model -> get_categories_by_id($id);
			if ($data['category'] != null) {
				$data['title'] = 'Thay đổi thông tin thành viên';
				$data['back_link']= base_url().'category';
				$this -> load -> view('back_end/form/frmEditCategory', $data);
			}
		}
	}
}

function checkNameExist() {
	if (isset($_POST['txtName'])) {
		$this -> load -> model('Categories_model');
		$txtName = $this -> input -> post('txtName');
		$data = $this -> Categories_model -> get_categories('*', array('name' => $txtName), array(), 0, 1, array());
		if ($data != null) {
			echo "false";
		} else {
			echo "true";
		}
	} else {
		echo "false";
	}
}

function checkOrderExist() {
	if (isset($_POST['txtOrderTopMenu'])) {
		$this -> load -> model('Categories_model');
		$txtOrder = $this -> input -> post('txtOrderTopMenu');
		$data = $this -> Categories_model -> get_categories('*', array('order_top_menu' => $txtOrder), array(), 0, 1, array());
		if ($data != null) {
			echo "false";
		} else {
			echo "true";
		}
	} else {
		echo "false";
	}
}

function checkNameExistEdit() {
	if (isset($_POST['txtName'])) {
		$this -> load -> model('Categories_model');
		$txtName = $this -> input -> post('txtName');
		$id = $this -> input -> post('id');
		$data = $this -> Categories_model -> get_categories('*', array('name' => $txtName, 'id <>' => $id), array(), 0, 1, array());
		if ($data != null) {
			echo "false";
		} else {
			echo "true";
		}
	} else {
		echo "false";
	}
}

function checkOrderExistEdit() {
	if (isset($_POST['txtOrderTopMenu'])) {
		$this -> load -> model('Categories_model');
		$txtOrder = $this -> input -> post('txtOrderTopMenu');
		$id = $this -> input -> post('id');
		$data = $this -> Categories_model -> get_categories('*', array('order_top_menu' => $txtOrder, 'id <>' => $id), array(), 0, 1, array());
		if ($data != null) {
			echo "false";
		} else {
			echo "true";
		}
	} else {
		echo "false";
	}
}
function load(){
	$this->load->model('Categories_model');
	$data['menu'] = $this -> Categories_model ->get_top_menu(0,20);
	$this -> load -> view('front_end/includes/header-forum', $data);
	$this->load->view('front_end/includes/frum-menu');

	$this->load->model('Article_model');
	$data['forum_slider'] = $this -> Article_model ->get_article_by_cat_id(3,0,1);
	$data['forum_title'] = $this -> Article_model ->get_article_by_cat_id(3,0,2);
	$data['forum_img']=$this -> Article_model ->get_article_by_cat_id(3,0,4);
	$this->load->view('front_end/includes/forum-slider',$data);

	$this->load->view('front_end/includes/forum-wraper');
	$data['forum_xu'] =$this -> Article_model ->get_article_by_cat_id(1,0,4);
	$this->load->view('front_end/includes/forum-xu-huong',$data);

	$this->load->view('front_end/includes/forum-funny');

	$this->load->model('Article_model');	
	$data['view']=$this-> Article_model->get_focus_new(1);
	$data['top_view']= $this-> Article_model->get_new_view(1,0,6);
	$data['slider_sibar']=$this -> Article_model -> get_article_by_cat_id(1,0,3);
	$this->load->view('front_end/includes/sidebar',$data);
	$this->load->view('front_end/includes/footer');
}
function load_new(){
	if(isset($_POST['first']) && isset($_POST['offset'])){
		$first=$this->input->post('first');
		$offset=$this->input->post('offset');
		$this->load->model('Article_model');
		$data['new']= $this-> Article_model->get_article_by_cat_id(4,$first,$offset);
		$this->load->view('front_end/ajax/frum_wraper',$data);
	}
}
function load_new_funny(){
	if (isset($_POST['first']) && isset($_POST['offset'])) {
		$first=$this->input->post('first');
		$offset=$this->input->post('offset');
		$this->load->model('Article_model');
		$data['forum_funny']= $this-> Article_model->get_article_by_cat_id(3,$first,$offset);
		$this->load->view('front_end/ajax/frum_funny',$data);
	}
}
}
?>
