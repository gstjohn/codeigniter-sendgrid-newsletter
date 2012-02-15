<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SendGrid Newsletter Library for CodeIgniter
 *
 * Wrapper for working with the SendGrid Newsletter API
 *
 * @package CodeIgniter
 * @version 0.0.1
 * @author Bold
 * @link http://hellobold.com
 */
class Sendgrid
{

	protected $api_endpoint  = 'https://sendgrid.com/api/';
	protected $error_message = '';
	protected $api_user      = '';
	protected $api_key       = '';
	protected $api_format    = 'json';
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
		$this->ci->load->spark('restclient/2.0.0');

		// initialize parameters
		$this->initialize($params);

		log_message('debug', 'SendGrid Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize settings
	 *
	 * @access public
	 * @param array $params Settings parameters
	 **/
	public function initialize($params = array())
	{
		if (is_array($params) && ! empty($params))
		{
			foreach($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
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
		return $this->_send('newsletter/add.' . $this->api_format, array(
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
		return $this->_send('newsletter/edit.' . $this->api_format, array(
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
		return $this->_send('newsletter/get.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * List newsletters in account
	 *
	 * @access public
	 * @param string $name Newsletter name (optional)
	 * @return array
	 **/
	public function list_newsletters($name = NULL)
	{
		if (is_null($name))
		{
			return $this->_send('newsletter/list.' . $this->api_format);
		}

		return $this->_send('newsletter/list.' . $this->api_format, array('name' => $name));
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
		return $this->_send('newsletter/delete.' . $this->api_format, array('name' => $name));
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

		return $this->_send('newsletter/lists/add.' . $this->api_format, $data);
	}

	// --------------------------------------------------------------------

	// stub for edit_list()

	// --------------------------------------------------------------------

	/**
	 * Get email lists in account
	 *
	 * @access public
	 * @param string $list List name (optiona)
	 * @return array
	 **/
	public function get_lists($list = NULL)
	{
		if (is_null($list))
		{
			return $this->_send('newsletter/lists/get.' . $this->api_format);
		}

		return $this->_send('newsletter/lists/get.' . $this->api_format, array('list' => $list));
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

	/**
	 * Add a list to a newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @param string $list Newsletter list name
	 * @return bool
	 **/
	public function add_recipients($name, $list)
	{
		return $this->_send('newsletter/recipients/add.' . $this->api_format, array('name' => $name, 'list' => $list));
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve the lists assigned to a particular newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 **/
	public function get_recipients($name)
	{
		return $this->_send('newsletter/recipients/get.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Remove a list from a newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @param string $list Newsletter list name
	 * @return bool
	 **/
	public function delete_recipients($name, $list)
	{
		return $this->_send('newsletter/recipients/delete.' . $this->api_format, array('name' => $name, 'list' => $list));
	}

	// --------------------------------------------------------------------

	/**
	 * Schedule a delivery time for an existing newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @param string $at Date/time to deliver the newsletter (optional)
	 * @param int $after Number of minutes in the future to schedule delivery (optional)
	 * @return bool
	 **/
	public function add_schedule($name, $at = NULL, $after = NULL)
	{
		$data = array('name' => $name);

		if ( ! is_null($at))
		{
			$data['at'] = date(DATE_ISO8601, strtotime($at));
		}
		elseif ( ! is_null($after))
		{
			$data['after'] = $after;
		}

		return $this->_send('newsletter/schedule/add.' . $this->api_format, $data) !== FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve the scheduled delivery time for a particular newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 **/
	public function get_schedule($name)
	{
		return $this->_send('newsletter/schedule/get.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Cancel a scheduled send for a newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return bool
	 **/
	public function delete_schedule($name)
	{
		return $this->_send('newsletter/schedule/delete.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	// stub for add_identity()

	// --------------------------------------------------------------------

	// stub for edit_identity()

	// --------------------------------------------------------------------

	// stub for get_identity()

	// --------------------------------------------------------------------

	/**
	 * Retrieve a list of all identities or check if an identity exists
	 *
	 * @access public
	 * @param string $identity Identity name (optional)
	 * @return mixed
	 **/
	public function list_identities($identity = NULL)
	{
		if (is_null($identity))
		{
			return $this->_send('newsletter/identity/list.' . $this->api_format);
		}

		return $this->_send('newsletter/identity/list.' . $this->api_format, array('identity' => $identity));
	}

	// --------------------------------------------------------------------

	// stub for delete_identity()

	// --------------------------------------------------------------------

	/**
	 * Get error message
	 *
	 * @access public
	 * @return string
	 **/
	public function error_message()
	{
		return $this->error_message;
	}

	// --------------------------------------------------------------------

	/**
	 * Send the request to SendGrid
	 *
	 * @access private
	 * @param string $url The portion of the URL after the API endpoint
	 * @param array $data The data to be sent along with the request (optional)
	 * @return mixed
	 **/
	private function _send($url, $data = array())
	{
		// set credentials
		$creds = array(
			'api_user' => $this->api_user,
			'api_key'  => $this->api_key
		);

		// initialize rest library
		$this->ci->rest->initialize(array('server' => $this->api_endpoint));
		$this->ci->rest->format($this->api_format);

		// merge credentials into data
		$data = array_merge($creds, $data);

		// post request
		$response = $this->ci->rest->post($url, $data);

		// check for 4xx reponse code
		if (substr($this->ci->rest->status(), 0, 1) == 4)
		{
			$this->error_message = $response->error . '.';
			return FALSE;
		}
		// check for 5xx response codes
        elseif (substr($this->ci->rest->status(), 0, 1) == 5)
        {
            $this->error_message = 'Access to SendGrid failed. Please try again later.';
            return FALSE;
        }
		// check for an error message response
		elseif (isset($response->error))
		{
			$this->error_message = $response->error . '.';
			return FALSE;
		}
		// check for a success message response
		elseif (isset($response->success))
		{
			return TRUE;
		}

		// return the response data
		return $response;
	}

}
