<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->helper('form');
		$this->load->helper('url');
		$this->load->view('welcome_message');
	}
	/*

	   */

	function login(){
		$this->load->database();

		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$this->load->model('user_model', 'user_m');
		$user = $this->user_m->get_by_credentials($username, $password);

		if(!$user)
		{
			echo "Fail";
			return true;
		}
		Echo "Login Accepted";
		$this->load->library('authentica');
		print_r($user);
		$this->authentica->login($user->id);
	}

	function create()
	{


			//form
			$this->load->helper('form');
			$this->load->library('form_validation');
			//recaptcha
			//$this->load->helper('recaptcha');

			$this->form_validation->set_rules('username', 'User name', 'trim|required|callback_username_check');
			$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required|matches[confirm]');
			$this->form_validation->set_rules('confirm', 'Password Confirmation', 'required');
			//$this->form_validation->set_rules('recaptcha_response_field', 'Captcha Response', 'required|callback_captcha_check');

			if( $this->form_validation->run() == false ) {
				//load all of the views
				//$challenge = recaptcha_get_challenge();
				//$this->load->view('common/login/header');
				$this->load->view('user/form_register');
			//	$this->load->view('common/login/footer');
				return true;
			}

			$username = $this->input->post('username', TRUE);
			$email = $this->input->post('email', TRUE);
			$password = $this->input->post('password', TRUE);

			if($this->user_model->save($username, $password, $email) == false){
				$this->load->view('common/registration_error');

				return true;
			}
			//registration completed, send the user to the jail house
			//get the last user created
			$user_id = $this->user_model->insert_id();
			//set jail
			//$this->user_model->jail($user_id, 'creation');


			//echo "Registration Completed";

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */