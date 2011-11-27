<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {

function __construct() {
	parent::__construct();
	$this->load->database();

	$this->load->model('user_model');


}

/**
 *
 *
 * @version $Id$
 * @copyright 2011
 */

	function index()
	{
		echo "Hello!";
		$this->db->where('userId', $this->authentica->logged_in_id());
		$query = $this->db->get('domain_xref_users_contacts');
		$num_rows = $query->num_rows();
		if($num_rows == 0)
		{
			echo "Whoa, there.  You need to register a contact before you can register a domain.";
		} else {
			echo "YOu have a contact!  You're good to go!";
		}
	}


function register()
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

	function new_contact()
	{
		if(! $this->authentica->is_logged_in() )
		{
			echo "You must be logged in!";
			return false;
		}
		$this->load->helper('form');
		$this->load->library('form_validation');

		$this->form_validation->set_rules('referenceName', 'Contact Reference Name', 'required');
		
		$this->form_validation->set_rules('firstName', 'First Name', 'trim|required');
		$this->form_validation->set_rules('lastName', 'Last Name', 'trim|required');
		$this->form_validation->set_rules('emailAddr', 'Email Address', 'required|valid_email');
		$this->form_validation->set_rules('address1', 'Address', 'required');
		$this->form_validation->set_rules('state', 'State/Provice', 'required');
		$this->form_validation->set_rules('zipcode', 'Postal Code', 'required');
		$this->form_validation->set_rules('country', 'Country', 'required');
		$this->form_validation->set_rules('phone', 'Phone Number', 'required');

		if( ! $this->form_validation->run() )
		{
			$this->load->view('user/form_newcontact');
		} else {
			$contact_record = array(
				"nameFirst"=>$this->input->post('firstName'),
				"nameLast"=>$this->input->post('lastName'),
				"nameCompany"=>$this->input->post('companyName'),
				"email"=>$this->input->post('emailAddr'),
				"address1"=>$this->input->post('address1'),
				"address2"=>$this->input->post('address2'),
				"city"=>$this->input->post('city'),
				"state"=>$this->input->post('state'),
				"postCode"=>$this->input->post('zipcode'),
				"country"=>$this->input->post('country'),
				"phoneNumber"=>$this->input->post('phone'),
				"date"=>date('Y-m-d H:i:s')
			);
			
			$this->db->insert('domain_contacts', $contact_record);
			
			$contactId = $this->db->insert_id();
			$userId = $this->authentica->logged_in_id();
			$refName = $this->input->post('referenceName');
			
			$xref_rec = array(
				'userId'=>$userId,
				'contactId'=>$contactId,
				'referenceName'=>$refName
			);
			
			$this->db->insert('domain_xref_users_contacts', $xref_rec);
			
			echo "Pass.";
		}
	}
}
