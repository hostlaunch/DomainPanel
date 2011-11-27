<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Authentica {

	var $CI;

	function __construct()
	{
		//get the CI
		$this->CI = &get_instance();
	}

	function login($userid)
	{
		$this->CI->session->set_userdata('userid', $userid);
		return true;
	}

	function is_logged_in()
	{
		$id = $this->CI->session->userdata('userid');
		//check to make sure the userid is an actual user
		if($id > 0)
		{
			//yes!
			return true;
		}
		//not a user or not logged in.
		return false;
	}

	function logged_in_id()
	{
		$id = $this->CI->session->userdata('userid');
		//check to make sure the userid is an actual user
		if($id > 0)
		{
			//yes!
			return $id;
		}
		//not a user or not logged in.
		return false;
	}

	public function gate()
	{
		if( ! $this->is_logged_in())
		{
			//get the current URI
			$current_uri = $this->CI->uri->uri_string();
			//save it the session
			$this->CI->session->set_userdata('login-redirect', $current_uri);
			//redirect to the login page
			redirect('user/login/secure-msg');
		}

		return true;
	}

	function logout()
	{
		//destroy the session information
		$this->CI->session->unset_userdata('userid');
		return true;
	}

}