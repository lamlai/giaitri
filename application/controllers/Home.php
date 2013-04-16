<?php
/**
 *
 */
class Home extends CI_Controller {

	function __construct() {
		parent::__construct();
		session_start();
		date_default_timezone_set('Asia/Bangkok');
		$this -> load -> database();
	}

	function login_by_oau(){
		$this->config->load('facebook');
		$config=$this->config->item('facebook');
		$this->load->library('facebook', $config);
		$user_id=$this->facebook->getUser();
		if ($user_id != 0) {
	        //if user has login
			$this->load->model('User_model');
			$user_profile=$this->User_model->get_user_by_oau_id($user_id, 0, 1);
			if($user_profile==null){
				$user_profile=$this->facebook->api('/me',
					'GET',
					array('access_token'=>$this->facebook->getAccessToken())
					);
				if(isset($_POST['password']) && isset($_POST['cfpassword'])){
					$this->load->library('ultils');
					$password=$this->ultils->_encrypt_password($this->input->post('password'));
					$data_array = array(
						'full_name' => $user_profile['name'],
						'user_name' => $user_profile['username'],
						'avts'=> 'http://graph.facebook.com/'.$user_profile['id'].'/picture?type=normal',
						'oau_id'=>$user_profile['id'],
						'email' => $user_profile['email'],
						'pass'=>$password,
						'ip_address' => $this -> input -> ip_address(),
						'date_join' => date('Y-m-d H:i:s', time()),
						'state'=>ACTIVED_STATE
						);
					$inserted_id=$this->User_model->insert_user($data_array); 
					$data_array=array(
						'user_id'=>$inserted_id,
						'role_id'=>LOWEST_ROLE_ID);
					$this->User_model->insert_users_in_roles($data_array);
					redirect(base_url().'home');
				}
				$data['name']=$user_profile['name'];
				$data['title']='Nhập mật khẩu của bạn';
				$this->load->view('front_end/get_pass',$data);
			}else{
				redirect(base_url().'home');
			}
		} else {
			$login_url_params = array(
				'scope' => 'email',
				'fbconnect' => 1, 
				'redirect_uri' => 'http://localhost/giaitri/home/login_by_oau');
			$login_url = $this->facebook-> getLoginUrl($login_url_params);
            //redirect to the login URL on facebook
			redirect($login_url);
			exit();
		}
	}

	function index(){
		$this->load->model('Categories_model');
		$data['menu'] = $this -> Categories_model ->get_top_menu(0,20);
		$this -> load -> view('front_end/includes/header', $data);

		$this->load->model('Article_model');
		$data['new_img'] = $this -> Article_model -> get_article_cat_id(2,0,9);
		$data['xu_huong'] = $this -> Article_model -> get_article_cat_id(1,0,4);
		$data['funny'] = $this -> Article_model ->get_article_cat_id(4,0,6);
		$this->load->view('front_end/content',$data);
		
		$this->load->model('Article_model');
		$data['new']= $this -> Article_model ->get_article_by_new(0,6);
		$this->load->view('front_end/includes/sidebar',$data);

		$this->load->view('front_end/includes/slider');
		$this->load->view('front_end/includes/footer');
	}
}
?>