<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Domain extends CI_Controller {

function __construct() {
	parent::__construct();
	$this->load->database();

	$this->load->library('authentica');


	}

	function index()
	{

	}

	function check()
	{
		$this->load->helper('rest');

		if( ! $this->input->post('domain'))
		{
			echo "No domain specified";

			return false;
		}

		$domain = $this->input->post('domain');

		$domain .= ".tk";


		echo "Checking $domain...";

		$this->load->model('dottk_model');
		$isAvail = $this->dottk_model->checkDomain($domain);

		if($isAvail)
		{
			echo "domain is available";
			echo "Continue to registration?";
			$this->load->model('user_model');
			$this->load->helper('url');
			if($this->user_model->count_all_contacts() == 0)
			{
				echo "Before you can register a domain, you need to have a contact setup first! <br />";
				echo anchor("user/new_contact");
			}

		} else {
			echo "domain is taken";
		}
	}


}
