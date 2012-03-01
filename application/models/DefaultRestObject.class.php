<?

/**
 * Default REST Object Implementation that uses the Database for the objects
 * 
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 * 
 */
class DefaultRestObject extends Core_RestObject
{

	protected $db;

	public function __construct($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{
		$this->_restServer = $restServer;
		$this->_table = $table;
		$this->_primaryKey = $table . '_id';
		$this->db = new Core_DB ();
		
		if (isset ( $this->db->dataset [$this->_table] ) && $this->db->dataset [$this->_table] ['primary'] == $this->_primaryKey)
		{
			parent::__construct ( $ids, $arguments, $restArguments, $restFilters );
		}
		else
		{
			$this->_restServer->setStatusCode ( 404 );
			exit ();
		}
	}

}