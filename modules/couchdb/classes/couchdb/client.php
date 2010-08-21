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
	 * @var  array  CouchDB Client instances
	 */
	protected static $instances = array();

	/**
	 * Returns a new instance of this class is one has not been created yet
	 */
	public static function instance($name = NULL)
	{
		// If we have already create an instance 
	}

}
