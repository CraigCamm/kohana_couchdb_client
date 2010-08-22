<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Manages communication with a CouchDB instance through an object abstraction
 * API. All CouchDB Client object instances are referenced by a name.
 *
 * @package    Kohana/CouchDB
 * @category   Extension
 * @author     Neuroxy
 * @copyright  (c) 2010 Neuroxy
 * @license    FIXME
 */
class CouchDB_Client {	

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Database instances
	 */
	public static $instances = array();

	/**
	 * Get a singleton CouchDB_Client instance. If configuration is not
	 * specified, it will be loaded from the couchdb configuration file using
	 * the same group as the name.
	 *
	 *     // Load the default client instance
	 *     $client = CouchDB_Client::instance();
	 *
	 *     // Create a custom configured instance
	 *     $client = CouchDB_Client::instance('custom', $config);
	 *
	 * @param   string   instance name
	 * @param   array    configuration parameters
	 * @return  CouchDB_Client
	 */
	public static function instance($name = NULL, $config = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = CouchDB_Client::$default;
		}

		if ( ! isset(CouchDB_Client::$instances[$name]))
		{
			if ($config === NULL)
			{
				// Load the configuration for this client
				$config = Kohana::config('couchdb')->$name;
			}

			// Create the client instance
			new CouchDB_Client($name, $config);
		}

		return CouchDB_Client::$instances[$name];
	}

	// Instance name
	protected $_instance;

	// Configuration array
	protected $_config;

	// Holds on to a reference to the rest client
	protected $_rest_client;

	/**
	 * Stores the client configuration locally and names the instance.
	 *
	 * [!!] This method cannot be accessed directly, you must use [CouchDB_Client::instance].
	 *
	 * @return  void
	 */
	protected function __construct($name, array $config)
	{
		// Set the instance name
		$this->_instance = $name;

		// Store the config locally
		$this->_config = $config;

		// Store this client instance
		self::$instances[$name] = $this;

		// Come up with a name for the rest client
		$rest_client_name = 'couchdb_'.$this->_instance.'_rest_client_'.sha1((string) microtime());

		// Set up the rest client instance
		$this->_rest_client = REST_Client::instance($rest_client_name, array(
			'uri' => $this->_config['host']
		));
	}

	/**
	 * Gets the document that was requested, caches its information locally, and returns it
	 *
	 * @param   string  the document id that we are requesting
	 * @return  mixed   the parsed document
	 */
	public function __get($id)
	{
		// Get the requested document and return it
		return $this->_get_document($id);
	}

	/**
	 * Gets the document that was requested from the database
	 *
	 * @param   string  the document id that we are requesting
	 * @return  mixed   the parsed document
	 */
	protected function _get_document($id)
	{
		// Grab the database name from the config
		$database = $this->_config['database'];

		// Make the HTTP request out to the database using the rest client
		$response = $this->_rest_client->get($database.'/'.$id);

		// Attempt to parse the response text into an object
		$document = $this->_parse_document($response->data, $response->status);

		// Return the document
		return $document;
	}

	/**
	 * Tries to parse the response text returned from an HTTP request into a document
	 *
	 * @param   string  the body returned from the request
	 * @param   string  the status code that was returned by the request
	 * @return  mixed   the parsed document
	 */
	protected function _parse_document($json_text, $status)
	{
		// Attempt to parse the response text as a JSON document
		$document = json_decode($json_text);

		// If the JSON text that was passed in was not null, but the document was parsed as NULL
		if ($json_text !== 'null' AND $document === NULL)
		{
			throw new Kohana_Exception('Invalid JSON returned from :uri',
				array(':uri' => $uri));
		}

		// Make sure that we dont have an error in the response
		$this->_handle_error((int) $status, $document);

		// Return the parsed document
		return $document;
	}

	/**
	 * Throws an exception if there is an error member in the document
	 *
	 * @param   int   the status code that was returned by the request
	 * @param   mixed the parsed document body
	 * @return  void
	 */
	protected function _handle_error($status, $document)
	{
		// If the status code that was returned was HTTP_SUCCESS
		if ($status === REST_Client::HTTP_SUCCESS)
		{
			return;
		}

		// If the document is not an object
		if ( ! is_object($document))
		{
			// Throw a general exception
			throw new Kohana_Exception('Database server returned Error: ":error" with HTTP status ":status"',
				array(':error' => (string) $document, ':status' => $status));
		}

		// Try to grab the error and reason codes if they are available
		$error = isset($document->error) ? $document->error : NULL;
		$reason = isset($document->reason) ? $document->reason : NULL;

		// If the error is that we do not 

		// If we are all the way down here, we arent sure what is going on so we throw a generic exception
		throw new Kohana_Exception('Database server returned Error: :error with Reason: :reason',
			array(':error' => $document->error, ':reason' => $document->reason));
	}

}
