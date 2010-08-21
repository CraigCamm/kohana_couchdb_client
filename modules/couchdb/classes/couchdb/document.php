<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Manages a single CouchDB Document
 *
 * @package    Kohana/CouchDB
 * @category   Extension
 * @author     Neuroxy
 * @copyright  (c) 2010 Neuroxy
 * @license    FIXME
 */
class CouchDB_Document {

	/**
	 * @var  string  default CouchDB configuration group
	 */
	public static $default = 'default';

	/**
	 * @var  array  holds the name of the CouchDB configuration group this document belongs to
	 */
	protected $_group;

	/**
	 * @var  string  holds the document id
	 */
	protected $_id;

	/**
	 * @var  string  holds the current document revision
	 */
	protected $_rev;

	/**
	 * @var  object  all of the data that is stored in this document
	 */
	protected $_data;

	/**
	 * Manages a single CouchDB document. The document 
	 *
	 * @param   string  the document id that we should load, if assigned
	 * @param   string  the name of the CouchDB configuration group this document belongs to
	 * @return  void
	 */
	public function __construct($name = NULL, $id = NULL)
	{
		// If no group name was passed in
		if ($name === NULL)
		{
			// Use the default group name
			$name = self::$default;
		}

		// Get the configuration data for this group
		$config = Kohana::config('couchdb')->$name;

		// Store a reference to the configuration data locally
		$this->_config = $config;
	}

	/**
	 * Gets the document that was requested, caches its information locally, and returns it
	 *
	 * @param   string  the document id that we are requesting
	 * @return  mixed   the parsed document
	 */
	public function __get($id)
	{
	}

	/**
	 * Saves any changes have been made to this document if any changes have been made at all

}
