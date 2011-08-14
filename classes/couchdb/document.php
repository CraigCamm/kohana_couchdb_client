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
	 * @var  object  holds a reference to the CouchDB Client we are using to connect to the database
	 */
	protected $_couchdb;

	/**
	 * @var  string  holds the document id
	 */
	protected $_id;

	/**
	 * @var  boolean  if this document was loaded
	 */
	public $_loaded;

	/**
	 * @var  boolean  if changes have occurred or not
	 */
	protected $_changed;

	/**
	 * @var  object  holds all of the document data
	 */
    protected $_data;

	/**
	 * @var  object  delete  document
	 */
	protected $delete;

	/**
	 * Manages a single document
	 *
	 * @param   string  if set, the document id that we should load
	 * @param   string  the name of the CouchDB database connection configuration group
	 * @return  void
	 */
	public function __construct($id = NULL, $group = NULL)
	{
		if ($group === NULL)
		{
			// Use the default group name
			$group = self::$default;
		}

		// Assign the id
		$this->_id = $id;

		// Grab a reference to the CouchDB Client class instance
		$this->_couchdb = CouchDB_Client::instance($group);

		// This document has not yet had any changes made to it
		$this->_changed = FALSE;

		// Attempt to load this document
		$this->_loaded = $this->_load();
	}

	/**
	 * Checks to see if a single named member of this document is set or not
	 *
	 * @param   string   the name of the member we are requesting
	 * @return  boolean  if the named member exists
	 */
	public function __isset($name)
	{
		// Return if the named member exists or not
		return isset($this->_data->$name);
	}

	/**
	 * Returns a single named member of this document
	 *
	 * @param   string  the name of the member we are requesting
	 * @return  mixed   the data that was requested
	 */
	public function __get($name)
	{
		// Return the member name from the data
		return $this->_data->$name;
	}

	/**
	 * Sets a single named member of this document
	 *
	 * @param   string  the name of the member we are requesting
	 * @param   mixed   the new value to assign
	 * @return  void
	 */
	public function __set($name, $value)
	{
		// Assume that if we are setting a value the data has changed
		$this->_changed = TRUE;

		// Set the value that was passed in
		$this->_data->$name = $value;
	}

	/**
	 * Deletes a single named member of this document
	 *
	 * @param   string  the name of the member we are deleting
	 * @return  object  a reference to this class instance
	 */
	public function delete($id, $rev)
	{
        // Delete
       return  $this->_couchdb->delete_document($id,
           (object) array('rev' => $rev)
       );

       // return $this;
	}

    /**
     * Checks to see if a document exists and has been loaded
     *
     * @return  boolean  if the document is loaded
     */
    public function loaded()
    {
        // Return the loaded status
        return $this->_loaded;
    }

	/**
	 * Attempts to load this document
	 *
	 * @return  boolean  if we were able to load the document or not
	 */
	protected function _load()
	{
		// If we do not have a document id
		if ($this->_id === NULL)
		{
			// We were unable to load the document
			return FALSE;
		}

		// The call to load will fail if the document is new
		try {

			// Grab the id of this document
			$id = $this->_id;

			// Attempt to load the document data
			$this->_data = $this->_couchdb->$id;

			// The document was successfully loaded
			return TRUE;

		// Catch all exceptions
		} catch (Exception $exception) {

			// If the exception indicates that the document is unavailable
			if ($exception instanceof CouchDB_Unavailable_Document_Exception)
			{
				// Initialize the data area
				$this->_data = new stdClass;
				$this->_data->_id = $this->_id;

				// We were not able to load this document
				return FALSE;
			}
			else
			{
				// We arent sure what the issue is
				throw $exception;
			}

		}
	}

	/**
	 * Saves any changes that were made to this document
	 *
	 * @param   string  if set, the document id that we should use to save this document
	 * @return  object  a reference to this class instance
	 */
	public function save($id = NULL)
	{
		if ($id === NULL)
		{
			// Use the id that was most recently loaded
			$id = $this->_id;
		}
		else
		{
			// Store the new id value that was passed in
			$this->_id = $id;
		}

		// Overwrite whatever may be there with the actual document id
		$this->_data->_id = $id;

		// If the document is new or changes were made
		if ( ! $this->_loaded OR $this->_changed)
		{
			// Save the changes that were made to this document
			$this->_couchdb->$id = $this->_data;
        }

        return $this;

	}

}
