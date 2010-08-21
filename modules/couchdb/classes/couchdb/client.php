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
	public static function instance($name = NULL)
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

	/**
	 * Constants for HTTP methods
	 */
	const HTTP_GET    = 'GET';
	const HTTP_PUT    = 'PUT';
	const HTTP_POST   = 'POST';
	const HTTP_DELETE = 'DELETE';

	/**
	 * @var  object  the last query that was executed
	 */
	public $last_query;

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
		// Get the requested document
		$this->_documents[$id] = $this->_get_document($id);

		// Return the requested document
		return $this->_documents[$id];
	}

	/**
	 * Gets the document that was requested from the database
	 *
	 * @param   string  the document id that we are requesting
	 * @return  mixed   the parsed document
	 */
	protected function _get_document($id)
	{
		// Make the HTTP request out to the database and get the result
		$this->_http(self::HTTP_GET, $id);
	}

	/**
	 * Gets the document that was requested from the database
	 *
	 * @param   string  the HTTP method (GET, PUT, POST, DELETE)
	 * @param   string  the document id that we are requesting
	 * @return  mixed   the parsed document
	 */
	protected function _http($method, $id)
	{
		// If we do not understand the desired method
		if ( ! in_array($method, array(self::HTTP_GET, self::HTTP_PUT, self::HTTP_POST, self::HTTP_DELETE)))
		{
			throw new Kohana_Exception('Unknown HTTP method requested :method',
				array(':method' => $method));
		}

		// Determine what the CURL options should be for this request (see http://us.php.net/curl_setopt)

		// Make the HTTP request out to the database and get the result

	}

	/**
	 * Throws an exception if there is an error member in the document
	 */
	protected function _handle_error($document)
	{

	}

}
