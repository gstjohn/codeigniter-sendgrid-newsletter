<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter SendGrid Newsletter Class
 *
 * Work with the SendGrid Newsletter API
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author        	Bold
 */
class Sendgrid
{

	protected $api_endpoint = 'https://sendgrid.com/api/';
	protected $error_message = '';
	protected $ci;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param array params Initialization parameters
	 **/
	public function __construct($params = array())
	{
		$this->ci =& get_instance();

		// load sparks
		$this->ci->load->spark('curl/1.2.0');

		log_message('debug', 'SendGrid Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Add a newsletter
	 *
	 * @access public
	 * @param string $identity SendGrid identity
	 * @param string $name Newsletter name
	 * @param string $subject Newsletter subject
	 * @param string $html HTML version of newsletter body
	 * @param string $text Text-only version of newsletter body
	 * @return bool
	 **/
	public function add_newsletter($identity, $name, $subject, $html, $text)
	{
		return $this->_send('newsletter/add.json', array(
			'identity' => $identity,
			'name'     => $name,
			'subject'  => $subject,
			'text'     => $text,
			'html'     => $html
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Edit an existing newsletter
	 *
	 * @access public
	 * @param string $identity SendGrid identity
	 * @param string $name Newsletter old name
	 * @param string $new_name Newsletter new name
	 * @param string $subject Newsletter subject
	 * @param string $html HTML version of newsletter body
	 * @param string $text Text-only version of newsletter body
	 * @return bool
	 **/
	public function edit_newsletter($identity, $name, $new_name, $subject, $text, $html)
	{
		return $this->_send('newsletter/edit.json', array(
			'name'     => $name,
			'newname'  => $new_name,
			'identity' => $identity,
			'subject'  => $subject,
			'text'     => $text,
			'html'     => $html
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Get an existing newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 **/
	public function get_newsletter($name)
	{
		return $this->_send('newsletter/get.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * List newsletters in account
	 *
	 * @access public
	 * @param string $name Newsletter name (optional)
	 * @return mixed
	 **/
	public function list_newsletters($name = NULL)
	{
		if (is_null($name))
		{
			return $this->_send('newsletter/list.json');
		}

		return $this->_send('newsletter/list.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete an individual newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 **/
	public function delete_newsletter($name)
	{
		return $this->_send('newsletter/delete.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Add a new email list
	 *
	 * @access public
	 * @param string $list Name of the list to create
	 * @param string $name Column name to associate with the email address (optional)
	 * @return bool
	 **/
	public function add_list($list, $name = NULL)
	{
		$data = array('list' => $list);

		if (is_null($name))
		{
			$data['name'] = $name;
		}

		return $this->_send('newsletter/lists/add.json', $data);
	}

	// --------------------------------------------------------------------

	// stub for edit_list()

	// --------------------------------------------------------------------

	/**
	 * Get email lists in account
	 *
	 * @access public
	 * @param string $list List name (optiona)
	 * @return mixed
	 **/
	public function get_lists($list=NULL)
	{
		if (is_null($list))
		{
			return $this->_send('newsletter/lists/get.json');
		}

		return $this->_send('newsletter/lists/get.json', array('list' => $list));
	}

	// --------------------------------------------------------------------

	// stub for delete_list()

	// --------------------------------------------------------------------

	// stub for add_email()

	// --------------------------------------------------------------------

	// stub for get_email()

	// --------------------------------------------------------------------

	// stub for delete_email()

	// --------------------------------------------------------------------

	public function add_recipients($name, $list)
	{
		return $this->_send('newsletter/recipients/add.json', array('name' => $name, 'list' => $list));
	}

	// --------------------------------------------------------------------

	public function get_recipients($name)
	{
		return $this->_send('newsletter/recipients/get.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	public function delete_recipients($name, $list)
	{
		return $this->_send('newsletter/recipients/delete.json', array('name' => $name, 'list' => $list));
	}

	// --------------------------------------------------------------------

	public function add_schedule($name, $at=NULL, $after=NULL)
	{
		$data = array('name' => $name);

		if ( ! is_null($at))
		{
			$data['at'] = date(DATE_ISO8601, human_to_unix($at));
		}
		elseif ( ! is_null($after))
		{
			$data['after'] = $after;
		}

		return $this->_send('newsletter/schedule/add.json', $data) !== FALSE;
	}

	// --------------------------------------------------------------------

	public function get_schedule($name)
	{
		return $this->_send('newsletter/schedule/get.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	public function delete_schedule($name)
	{
		return $this->_send('newsletter/schedule/delete.json', array('name' => $name));
	}

	// --------------------------------------------------------------------

	// stub for add_identity()

	// --------------------------------------------------------------------

	// stub for edit_identity()

	// --------------------------------------------------------------------

	// stub for get_identity()

	// --------------------------------------------------------------------

	public function list_identities($identity)
	{
		return $this->_send('newsletter/identity/list.json', array('identity' => $identity));
	}

	// --------------------------------------------------------------------

	// stub for delete_identity()

	// --------------------------------------------------------------------

	private function _send($url, $data = array())
	{
		// load REST client library
		$this->ci->load->spark('restclient/2.0.0');

		// get credentials
		$creds = array(
			'api_user' => config_item('api_user'),
			'api_key'  => config_item('api_key')
		);

		$this->ci->rest->initialize(array('server' => $this->api_endpoint));
		$this->ci->rest->format('json');

		// merge credentials into data
		$data = array_merge($creds, $data);

		$response = $this->ci->rest->post($url, $data);

		// check for non-2XX response codes
		if (substr($this->ci->rest->status(), 0, 1) != 2)
		{
			$this->error_message = 'The response from SendGrid failed';
			return FALSE;
		}
		elseif (isset($response->error))
		{
			$this->error_message = $response->error;
			return FALSE;
		}
		elseif (isset($response->success))
		{
			return TRUE;
		}

		return $response;
	}

}
