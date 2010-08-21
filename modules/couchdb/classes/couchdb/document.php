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
	 * @var  string  default configuration group
	 */
	public static $default = 'default';

	/**
	 * @var  array  holds the name of the configuration group this document belongs to
	 */
	protected $_group;

	/**
	 * @var  boolean  holds if this document is new or not
	 */
	protected $_new;

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
	 * Manages a single document
	 *
	 * @param   string  if set, the document id that we should load
	 * @param   string  the name of the configuration group this document should belong to
	 * @return  void
	 */
	public function __construct($id = NULL, $group = NULL)
	{
		if ($group === NULL)
		{
			// Use the default group name
			$group = self::$default;
		}

		// Store the name of the configuration group
		$this->_group = $group;

		// If we were given a document id
		if ($id !== NULL)
		{
			// Attempt to load this document
			$this->load($id);
		}
	}

	/**
	 * Returns a single named member of this document
	 *
	 * @param   string  the name of the member we are requesting
	 * @return  mixed   the parsed document
	 */
	public function __get($name)
	{
		// Return the member name from the data
		return $this->_data->$name;
	}

	/**
	 * Attempts to load this document
	 *
	 * @return  mixed  a reference to this class instance
	 */
	protected function _load($id)
	{
		// The call to load will fail if the document is new
		try {

			// Attempt to load the document data
			$this->_data = CouchDB_Client::instance($this->_group)->$id;

		// Catch all exceptions
		} catch ($exception) {

			// If the exception indicates that the document is unavailable
			if ($exception instanceof CouchDB_Document_Unavailable_Exception)
			{
				// Mark this document as new
				$this->_new = TRUE;
			}
			else
			{
				// We arent sure what to do, so re-throw the exception
				throw $exception;
			}

		}

		// Return a reference to this class instance
		return $this;
	}

	/**
	 * Reloads the 

	/**
	 * Saves any changes that were made to this document
	 *
	 * @param   string  if set, the document id that we should use to save the document
	 * @param   string  the name of the configuration group this document should belong to
	 * @return  object  a reference to this class instance
	 */
	public function save($id = NULL, $group = NULL)
	{
		if ($group === NULL)
		{
			// Use the group name that was set in the constructor
			$group = $this->_group;
		}
	}

}