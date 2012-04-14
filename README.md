# CodeIgniter-SendGrid-Newsletter

CodeIgniter-SendGrid-Newsletter is a CodeIgniter wrapper for the [SendGrid Newsletter API](http://docs.sendgrid.com/documentation/api/newsletter-Newsletter/).

## Requirements

1. PHP 5.1+
2. CodeIgniter 2.0.0+
3. cURL
4. CodeIgniter REST Client Library: [http://getsparks.org/packages/restclient/show](http://getsparks.org/packages/restclient/show)

## Included Methods

**Initialization**

1. `initialize()` - Set up the library with API credentials and settings

**Newsletter**

1. `add_newsletter()` - Create a newsletter
3. `edit_newsletter()` - Edit a newsletter
4. `get_newsletter()` - Get the contents of an existing newsletter
5. `list_newsletters()` - Retrieve a list of all newsletters
6. `delete_newsletter()` - Delete a newsletter

**Lists**

1. `add_list()` - Create a recipient list
2. `edit_list()` - Rename a list
3. `get_lists()` - Retrieve all recipient lists or check if a particular list exists
4. `delete_list()` - Delete a list

**List Emails**

1. `add_list_emails()` - Add one or more emails to a list
2. `get_list_emails()` - Retrieve the email addresses and associated fields for a particular list
3. `delete_list_emails()` - Remove one or more emails from a list

**Newsletter Lists**

1. `add_recipients()` - Add a recipient to a newsletter
2. `get_recipients()` - Retrieve the lists assigned to a particular newsletter
3. `delete_recipients()` - Remove a list from a newsletter

**Scheduling**

1. `add_schedule()` - Schedule a delivery time for an existing newsletter
2. `get_schedule()` - Retrieve the scheduled delivery time for a particular newsletter
3. `delete_schedule()` - Cancel a scheduled send for a newsletter

**Identities**

1. `add_identity()` - Create a new identity
2. `edit_identity()` - Edit an identity
3. `get_identity()` - Retrieve a particular identity
4. `list_identities()` - Retrieve a list of all identities or check if an identity exists
5. `delete_identity()` - Delete an identity

**Errors**

1. `error_message()` - Get error message

## Usage

	// Load the SendGrid spark
	$this->load->spark('sendgrid/0.1.1');

	// Initialize (not necessary if set in config)
	$this->sendgrid_newsletter->initialize(array('api_user'   => 'my_username',
	                                			 'api_key'    => 'secret_key',
	                                			 'api_format' => 'json'));

	// Get newsletters
	$newsletters = $this->sendgrid_newsletter->list_newsletters();
