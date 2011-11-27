<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class user_model extends CI_Model {

	private $_new_id;
	private $userTable;
	private $userColumn;

	function __construct()
	{
		parent::__construct();

		//this model requires the session library, so load it
		$this->load->library('session');

		$this->userTable = "domain_users";
		$this->userColumn = "username";

	}

	/**
	 * Save a user in the database
	 *
	 * @access public
	 * @param $username Username to save
	 * @param $email    Email address of the user
	 * @param $password User's password
	 * @return boolean
	 *
	 */
	function save($username, $password=NULL, $email=NULL)
	{
		//load the security helper
		$this->load->helper('security');
		//first, we must md5 the password
		$password = do_hash($password);
		//create a record array with the data
		$record = array($this->userColumn=>$username, 'password'=>$password, 'email'=>$email);
		//filter the array
		$record = array_filter($record);

		//see if there if there is already a field with $username
		$this->db->from($this->userTable);
		$this->db->where($this->userColumn, $username);

		//we're going to presume this is an update
		$update = true;

		if($this->db->count_all_results() == 0)
		{
			//set the datetime of creation
			$record['date'] = date('Y-m-d H:i:s');
			//no, insert a new row
			$this->db->insert($this->userTable, $record);
			//this is not an update
			$update = false;
		} else {
			//yes, we should update
			//limit to one row
			$this->db->limit(1);
			//limit to just the one row we want
			$this->db->where($this->userColumn, $username);
			//shazam, update
			$this->db->update($this->userTable, $record);
		}


		print_r($this->db->affected_rows());
		//confirm the query has completed
		if($this->db->affected_rows() == 1)
		{
			//the query functioned properly.
			echo "function.";
			//see if this was an inserted row
			if( ! $update)
			{
				echo "not an update";
				//return the insert ID
				return $this->db->insert_id();
			}


			//not an insert, just return true
			return true;
		}

		//for shame
		return false;
	}

	function password($username, $password)
	{
		$this->db->limit(1);
		//
		$this->db->where($this->userColumn, $username);
		//first, we must md5 the password
		$this->load->helper('security');
		$password = dohash($password);
		//
		$pwd_reset = ($reset)?'true':'false';
		//form a record
		$record = array(
			'password'=>$password,
			'pwd_reset'=>$pwd_reset
		);
		//insert into the db
		$this->db->update($this->userTable, $record);
		//see if functioned
		if($this->db->affected_rows() == 1)
		{
			//saved succesfully
			return true;
		}
		//for shame
		return false;
	}

	function delete($userid)
	{
		//limit it to just one user
		$this->db->limit(1);
		//limit to the userid we want
		$this->db->where('id', $userid);
		//delete the row
		$this->db->delete($this->userTable);

		//check to see if the query completed
		if($this->db->affected_rows() == 1)
		{
			//deleted
			return true;
		}
		//failure
		return false;
	}

	function get_by_credentials($username, $password, $plainTxtPwd = true)
	{
		//see if the password has already been hashed
		if($plainTxtPwd)
		{
			//load the security helper
			$this->load->helper('security');
			//hash the $password
			$password = do_hash($password);
		}

		//check to see if there is a record matching both the username and password
		//limit to what we want
		$this->db->where($this->userColumn, $username);
		$this->db->where('password', $password);
		$query = $this->db->get($this->userTable);
		if($query->num_rows() == 0)
		{
			//no users could be found
			return false;
		}

		//return the user information
		return $query->row();
	}

	function get_password_hash($userid = null)
	{
		return $this->_getUserField($userid, 'password');
	}

	//todo, remove field() and info() function references

	function get_user_field($field, $userid = null)
	{
		if($userid == null)
		{
			$userid = $this->authentica->logged_in_id();
		}

		return $this->_getUserField($userid, $field);
	}

	function user_has_identity($userid = null)
	{
		if( $userid == null )
		{
			$userid = $this->authentica->logged_in_id();
		}

		$username = $this->_getUserField($userid, $this->userColumn);

		if( $username == '' )
		{
			return false;
		}

		return true;
	}

	/**
	 * Check to see if a logged in user is an administrator
	 *
	 * @access public
	 * @return boolean Returns true is the user is admin, false otherwise
	 *
	 */
	function is_admin()
	{
		//retrieve session data
		$type = $this->session->userdata('usertype');
		//check
		if($type == 'admin')
		{
			//is admin, return true
			return true;
		}
		//not an admin
		return false;
	}

	function metadata($field, $user=NULL)
	{
		//check to see if the user is null
		if($user == NULL)
		{
			$user = $this->authentica->logged_in_id();
		}
		//limit to one
		$this->db->limit(1);
		//user
		$this->db->where('user', $user);
		//field
		$this->db->where('field', $field);
		//get
		$query = $this->db->get('users_meta');
		//see if it is an empty set
		if($query->num_rows() == 0)
		{
			//empty set
			return false;
		}
		//row
		$row = $query->row();

		return $row->data;
	}

	function set_metadata($field, $data, $user=NULL)
	{
		//check to see if the user is null
		if($user == null)
		{
			$user = $this->authentica->logged_in_id();
		}

		return $this->_doSetMetaData($user, null, $field, $data);
	}

	function set_class_metadata($class, $field, $data, $user=NULL)
	{
		//check to see if the user is null
		if($user == null)
		{
			$user = $this->authentica->logged_in_id();
		}

		return $this->_doSetMetaData($user, $class, $field, $data);
	}

	function unset_metadata($field, $user = null)
	{
		$user = ($user==null) ? $this->authentica->logged_in_id() : $user;

		$this->db->limit(1);
		$this->db->where('user', $user);
		$this->db->where('field', $field);

		//
		$query = $this->db->delete('users_meta');
		if($this->db->affected_rows() == 1)
		{
			return true;
		}
		return false;
	}

	function unset_metaclass($class, $user)
	{
		//check to see if the user is null
		if($user == null)
		{
			$user = $this->authentica->logged_in_id();
		}

		$this->db->where('user', $user);
		$this->db->where('class', $class);

		//
		$query = $this->db->delete('users_meta');
		if($this->db->affected_rows() > 0)
		{
			return true;
		}
		return false;
	}

	function count_all_contacts($user = null)
	{
		$user = ($user==null) ? $this->authentica->logged_in_id() : $user;

		$this->db->where('userid', $user);
		$query = $this->db->get('domain_xref_users_contacts');

		return $query->num_rows();
	}

	/**
	 * Checks to see if a username is available
	 *
	 * @access public
	 * @param $username The username to be checked
	 * @return boolean True: Username is available; False username is taken
	 *
	 */
	function is_identity_available($username)
	{
		$this->db->where($this->userColumn, $username);
		$query = $this->db->get($this->userTable);

		if($query->num_rows() == 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks to see if a given email address has been registered to a user
	 *
	 * @access public
	 * @param $email The email address to be checked
	 * @return boolean True: email address is used
	 * @return boolean False: email address has not been used
	 *
	 */
	function is_email_registered($email)
	{
		$this->db->where("email", $email);
		$query = $this->db->get($this->userTable);

		if($query->num_rows > 0)
		{
			return true;
		}

		return false;
	}


	/**
	 *  Retrieve a list of all the users in the system
	 *
	 *  @access public
	 *  @return array Array of all users
	 *  @return false Returns false if there are no users
	 */
	function get_all_users()
	{
		//get the users
		$query = $this->db->get($this->userTable);
		//check to see the number of users
		if(sizeof($query->result()) == 0)
		{
			//no users, return false
			return false;
		}
		//there are users, return them
		return $query->result();
	}

	/**
	 * Retrieve the information of given user
	 *
	 * @access public
	 * @param $user_id The id of the user to retrieve
	 * @return object user row
	 *
	 */
	function retrieve($userid = null)
	{
		if($userid == null)
		{
			$userid = $this->authentica->logged_in_id();
		}

		return $this->_getUserRow($userid);

		/*//select the id
		   $this->db->where("id", $user_id);
		   //limit to one row
		   $this->db->limit(1);
		   //retrieve
		   $query = $this->db->get($this->userTable);
		   //return
		   return $query->row();*/
	}

	//since a username is no longer immediately set, we need a function to give SOMETHING
	function get_username_by_id($user)
	{
		//limit to 1
		$this->db->limit(1);
		//username
		$this->db->where('id', $user);
		//get!
		$query = $this->db->get($this->userTable);
		//row
		$row = $query->row();
		//username
		if(strlen($row->username) > 0)
		{
			return $row->username;
		}
		//email
		if(strlen($row->email) > 0)
		{
			return $row->email;
		}
		//default
		return "HostLaunch User";
	}

	function get_email_by_username($username)
	{
		//limit to 1
		$this->db->limit(1);
		//username
		$this->db->where($this->userColumn, $username);
		//get!
		$query = $this->db->get($this->userTable);
		//row
		$row = $query->row();
		return $row->email;
	}

	private function _getUserRow($userid)
	{
		$this->db->limit(1);
		$this->db->where('id', $userid);
		$query = $this->db->get($this->userTable);

		if($query->num_rows() == 0)
		{
			return false;
		}

		return $query->row();
	}

	private function _getUserField($userid, $field)
	{
		$userRow = $this->_getUserRow($userid);

		if( ! $userRow )
		{
			return false;
		}

		return $userRow->$field;
	}

	private function _doSetMetaData($user, $class, $field, $data)
	{
		//create a record array with the data
		//include the date, so the date reflects a possible change.
		$record = array('class'=>$class, 'field'=>$field, 'data'=>$data, 'date'=>date('Y-m-d H:i:s'));
		//filter the array
		$record = array_filter($record);
		//see if the user already has the meta, which we'll change instead of creating a new one
		$this->db->from('users_meta');
		//user
		$this->db->where('user', $user);
		//field
		$this->db->where('field', $field);

		if($this->db->count_all_results() == 0)
		{
			//add the user to the record
			$record['user'] = $user;
			//no, insert a new row
			$this->db->insert('users_meta', $record);
		} else {
			//yes, we should update
			//limit to one row
			$this->db->limit(1);
			//limit to just the one row we want
			$this->db->where('user', $user);
			$this->db->where('field', $field);
			//shazam, update
			$this->db->update('users_meta', $record);
		}

		//confirm the query has completed
		if($this->db->affected_rows() == 1)
		{
			//saved succesfully
			return true;
		}
		//for shame
		return false;
	}
}

?>