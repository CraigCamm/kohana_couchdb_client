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

	// Holds document objects requested and sent during this session
	protected $_documents;

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
		// Determine what the URI for this request should be
		$uri = $this->_build_uri($id);

		// Make the HTTP request out to the database and get the result
		$json = $this->_http(self::HTTP_GET, $uri);

		// Attempt to parse the response text into an object
		$document = $this->_parse_document($json);

		// Store the requested document
		$this->_documents[$id] = $document;

		// Return the document
		return $document;
	}

	/**
	 * Builds the URI for the request using the configuration data and passed document id
	 *
	 * @param   string   the document id we are requesting
	 * @param   boolean  if we should add the database onto the end of the uri or not. Defaults to TRUE
	 * @return  string   the URI where the requested document can be located
	 */
	protected function _build_uri($id, $database = TRUE)
	{
		$uri = $this->_config['host'];

		// If we should add the database part onto the end
		if ($database) {
			$uri .= $this->_config['database'];
		}

		// Return the finished URI
		return $uri;
	}

	/**
	 * Gets the document that was requested from the database
	 *
	 * @param   string  the HTTP method (GET, PUT, POST, DELETE)
	 * @param   string  the URI that we are requesting
	 * @return  mixed   the response text from the remote URI
	 */
	protected function _http($method, $uri)
	{
		// Determine what the CURL options are for this request
		$options = $this->_get_curl_options($method);

		// Make the HTTP request out to the database and get the result
		$response_text = Remote::get($uri, $options);

		// Return the document result
		return $response_text;
	}

	/**
	 * Returns an array of CURL options required to make the HTTP request work
	 *
	 * @param   string  the HTTP method (GET, PUT, POST, DELETE)
	 * @return  array   a list of CURL options for the desired request
	 */
	protected function _get_curl_options($method)
	{
		// If we do not understand the desired method
		if ( ! in_array($method, array(self::HTTP_GET/*, self::HTTP_PUT, self::HTTP_POST, self::HTTP_DELETE*/)))
		{
			throw new Kohana_Exception('Unknown HTTP method :method requested',
				array(':method' => $method));
		}

		// Determine what the CURL options should be for this request (see http://us.php.net/curl_setopt)
		switch ($method)
		{
			case self::HTTP_GET:
				return array(); // No options
		}
	}

	/**
	 * Tries to parse the response text returned from an HTTP request into a document
	 *
	 * @param   string  the string returned from the HTTP request
	 * @return  mixed   the parsed document
	 */
	protected function _parse_document($json)
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
		$this->_handle_error($document);

		// Return the parsed document
		return $document;
	}

	/**
	 * Throws an exception if there is an error member in the document
	 *
	 * @return  void
	 */
	protected function _handle_error($document)
	{
		// If the document has no _id but has error and reason members
		if (is_object($document) AND ! isset($document->_id) AND isset($document->error) AND isset($document->reason))
		{
			throw new Kohana_Exception('Database server returned Error: :error with Reason: :reason',
				array(':error' => $document->error, ':reason' => $document->reason));
		}
	}

}
