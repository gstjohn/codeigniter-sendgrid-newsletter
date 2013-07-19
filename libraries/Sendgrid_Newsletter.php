<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SendGrid Newsletter Library for CodeIgniter
 *
 * Wrapper for working with the SendGrid Newsletter API
 *
 * @package CodeIgniter
 * @version 0.1.1
 * @author Bold
 * @link http://hellobold.com
 */
class Sendgrid_Newsletter
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
	 */
	public function __construct($params = array())
	{
		$this->ci =& get_instance();

		// load sparks
		$this->ci->load->spark('restclient/2.0.0');

		// initialize parameters
		$this->initialize($params);

		log_message('debug', 'SendGrid Newsletter Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize settings
	 *
	 * @access public
	 * @param array $params Settings parameters
	 */
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

		// set format to json if an invalid format was provided
		if ($this->api_format != 'xml' && $this->api_format != 'json')
		{
			$this->api_format = 'json';
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Create a newsletter
	 *
	 * @access public
	 * @param string $identity SendGrid identity
	 * @param string $name Newsletter name
	 * @param string $subject Newsletter subject
	 * @param string $html HTML version of newsletter body
	 * @param string $text Text-only version of newsletter body
	 * @return bool
	 */
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
	 * Edit a newsletter
	 *
	 * @access public
	 * @param string $identity SendGrid identity
	 * @param string $name Newsletter old name
	 * @param string $new_name Newsletter new name
	 * @param string $subject Newsletter subject
	 * @param string $html HTML version of newsletter body
	 * @param string $text Text-only version of newsletter body
	 * @return bool
	 */
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
	 * Get the contents of an existing newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 */
	public function get_newsletter($name)
	{
		return $this->_send('newsletter/get.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve a list of all newsletters
	 *
	 * @access public
	 * @param string $name Newsletter name (optional)
	 * @return array
	 */
	public function list_newsletters($name=NULL)
	{
		if (is_null($name))
		{
			return $this->_send('newsletter/list.' . $this->api_format);
		}

		return $this->_send('newsletter/list.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @return mixed
	 */
	public function delete_newsletter($name)
	{
		return $this->_send('newsletter/delete.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Create a recipient list
	 *
	 * @access public
	 * @param string $list Name of the list to create
	 * @param string $name Column name to associate with the email address (optional)
	 * @return bool
	 */
	public function add_list($list, $name=NULL)
	{
		$data = array('list' => $list);

		// if a column name is provided, include it in the request
		if (is_null($name))
		{
			$data['name'] = $name;
		}

		return $this->_send('newsletter/lists/add.' . $this->api_format, $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Rename a list
	 *
	 * @access public
	 * @param string $list Name of the list to rename
	 * @param string $newlist The new name for the list
	 * @return bool
	 */
	public function edit_list($list, $newlist)
	{
		return $this->_send('newsletter/lists/edit.' . $this->api_format, array('list' => $list, 'newlist' => $newlist));
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve all recipient lists or check if a particular list exists
	 *
	 * @access public
	 * @param string $list List name (optiona)
	 * @return array
	 */
	public function get_lists($list=NULL)
	{
		if (is_null($list))
		{
			return $this->_send('newsletter/lists/get.' . $this->api_format);
		}

		return $this->_send('newsletter/lists/get.' . $this->api_format, array('list' => $list));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete a list
	 *
	 * @access public
	 * @param string $list The name of the list to remove
	 * @return bool
	 */
	public function delete_list($list)
	{
		return $this->_send('newsletter/lists/delete.' . $this->api_format, array('list' => $list));
	}

	// --------------------------------------------------------------------

	/**
	 * Add one or more emails (recipients) to a list
	 *
	 * @access public
	 * @param string $list The list to which recipients will be added
	 * @param array $data A collection of recipients (see _add_list_emails)
	 * @return bool
	 */
	public function add_list_emails($list, $data)
	{
		return $this->_add_list_emails($list, $data);
	}

	/**
	 * Add one or more emails (recipients) to a list
	 * (Required due to the format SendGrid expects recipients to be added to recipient lists)
	 * 
	 * @access private
	 * @param string $list The list to which recipients will be added
	 * @param array $recipient_data A collection of recipients e.g.:
	 * [0] => Array
     * (
     *      [email] => address1@domain.com
     *      [name] => contactName
     *      [custom1] => customProperty
	 * 		...
     * )
     * [1] => Array
     * (
     *      [email] => address2@domain.com
     *      [name] => contactName
     *      [custom1] => customProperty
	 * 		...
     * )
	 * ...
	 * @return bool/string Result
	 */
	private function _add_list_emails($list, $recipient_data)
	{
		// Create recipient object(s) that conform to the format the SendGrid API is expecting e.g. {"email":"address@domain.com","name":"contactName"}.
		$recipients = array();
		foreach ($recipient_data as $recipient_data_item)
		{
			$recipients[] = json_encode((object) $recipient_data_item);
		}

		// Create cURL session.
		$curl = curl_init($this->api_endpoint . 'newsletter/lists/email/add.' . $this->api_format);
		
		// Create URL encoded query string that conforms to the format SendGrid expects i.e. array elements unindexed.
		$query_string_args = array('api_user' => $this->api_user, 'api_key' => $this->api_key, 'list' => $list, 'data' => $recipients);
		$query_string = http_build_query($query_string_args, NULL, '&');
		$query_string = preg_replace('/\%5B\d+\%5D/', '%5B%5D', $query_string);
		
		// Set default cURL session options.
		$options = array(
			CURLOPT_FAILONERROR => FALSE,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POST => TRUE,
			CURLOPT_POSTFIELDS => $query_string,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_HTTPHEADER => array('Accept: application/' . $this->api_format));
		curl_setopt_array($curl, $options);
		
		// Execute & close cURL session.
		$response = curl_exec($curl);
		$info = curl_getinfo($curl);
		$response_code = $info['http_code'];
		curl_close($curl);
		
		// Format response.
		if ($this->api_format == 'json')
		{
			$response = json_decode(trim($response));
		}
		else if ($this->api_format == 'xml')
		{
			$response = unserialize(trim($response));
		}
		
		// check for 4xx reponse code
		if (substr($response_code, 0, 1) == 4)
		{
			$this->error_message = $response->error . '.';
			return FALSE;
		}
		// check for 5xx response codes
        elseif (substr($response_code, 0, 1) == 5)
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
		
		return $response;
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve the email addresses and associated fields for a particular list
	 *
	 * @access public
	 * @param string $list The list to retrieve
	 * @param string $email Optional email address or list of address to search for
	 * @return string
	 **/
	public function get_list_emails($list, $email=NULL)
	{
		$data = array('list' => $list);

		// if emails are provided, include them in the request
		if ( ! is_null($email))
		{
			$data['email'] = $email;
		}

		return $this->_send('newsletter/lists/email/get.' . $this->api_format, $data);
	}

	// --------------------------------------------------------------------

	/**
	 * Remove one or more emails from a list
	 *
	 * @access public
	 * @param string $list The name of the list from which emails will be removed
	 * @param string $email The emaill address or addresses to be removed
	 * @return bool
	 */
	public function delete_list_emails($list, $email)
	{
		return $this->_send('newsletter/lists/email/delete.' . $this->api_format, array('list' => $list, 'email' => $email));
	}

	// --------------------------------------------------------------------

	/**
	 * Add a recipient list to a newsletter
	 *
	 * @access public
	 * @param string $name Newsletter name
	 * @param string $list Newsletter list name
	 * @return bool
	 */
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
	 */
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
	 */
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
	 */
	public function add_schedule($name, $at=NULL, $after=NULL)
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
	 */
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
	 */
	public function delete_schedule($name)
	{
		return $this->_send('newsletter/schedule/delete.' . $this->api_format, array('name' => $name));
	}

	// --------------------------------------------------------------------

	/**
	 * Create a new identity
	 *
	 * @access public
	 * @param string $identity The title of the identity
	 * @param string $name The address name to be used
	 * @param string $email The email address
	 * @param string $address The street address
	 * @param string $city The city
	 * @param string $state The state
	 * @param string $zip The postal code
	 * @param string $country The country
	 * @return bool
	 */
	public function add_identity($identity, $name, $email, $address, $city, $state, $zip, $country)
	{
		return $this->_send('newsletter/identity/add.' . $this->api_format, array(
			'identity' => $identity,
			'name'     => $name,
			'email'    => $email,
			'address'  => $address,
			'city'     => $city,
			'state'    => $state,
			'zip'      => $zip,
			'country'  => $country
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Edit an identity
	 *
	 * @access public
	 * @param string $identity The title of the identity you wish to edit
	 * @param string $newidentity The new title of the identity you are editing
	 * @param string $name The address name to be used
	 * @param string $email The email address
	 * @param string $address The street address
	 * @param string $city The city
	 * @param string $state The state
	 * @param string $zip The postal code
	 * @param string $country The country
	 * @return bool
	 */
	public function edit_identity($identity, $newidentity, $name, $email, $address, $city, $state, $zip, $country)
	{
		return $this->_send('newsletter/identity/edit.' . $this->api_format, array(
			'identity'    => $identity,
			'newidentity' => $newidentity,
			'name'        => $name,
			'email'       => $email,
			'address'     => $address,
			'city'        => $city,
			'state'       => $state,
			'zip'         => $zip,
			'country'     => $country
		));
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve a particular identity
	 *
	 * @access public
	 * @param string $identity
	 * @return mixed
	 */
	public function get_identity($identity)
	{
		return $this->_send('newsletter/identity/get.' . $this->api_format, array('identity' => $identity));
	}

	// --------------------------------------------------------------------

	/**
	 * Retrieve a list of all identities or check if an identity exists
	 *
	 * @access public
	 * @param string $identity Identity name (optional)
	 * @return mixed
	 */
	public function list_identities($identity=NULL)
	{
		if (is_null($identity))
		{
			return $this->_send('newsletter/identity/list.' . $this->api_format);
		}

		return $this->_send('newsletter/identity/list.' . $this->api_format, array('identity' => $identity));
	}

	// --------------------------------------------------------------------

	/**
	 * Delete an identity
	 *
	 * @access public
	 * @param string $identity
	 * @return bool
	 */
	public function delete_identity($identity)
	{
		return $this->_send('newsletter/identity/delete.' . $this->api_format, array('identity' => $identity));
	}

	// --------------------------------------------------------------------

	/**
	 * Get error message
	 *
	 * @access public
	 * @return string
	 */
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
	 */
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
