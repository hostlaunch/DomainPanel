<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class dottk_model extends CI_Model {

function __construct()
{
	parent::__construct();

}

	function checkDomain($domain)
	{
		$random = rand(0, 100);
		if($random >25)
			return true;
		return false;
	}
}
?>