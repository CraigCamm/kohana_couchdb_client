<?php defined('SYSPATH') or die('No direct access');
/**
 * Extends the Kohana Exception class to indicate CouchDB failures caused by missing databases
 *
 * @package    Kohana/CouchDB
 * @category   Exceptions
 * @author     Neuroxy
 * @copyright  (c) 2008-2009 Neuroxy
 * @license    FIXME
 */
class CouchDB_Unavailable_Database_Exception extends Kohana_Exception {

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string   error message
	 * @param   array    translation variables
	 * @param   integer  the exception code
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0)
	{
		// Pass the message to the parent
		parent::__construct($message, $variables, $code);
	}

} // End CouchDB_Unavailable_Document_Exception
