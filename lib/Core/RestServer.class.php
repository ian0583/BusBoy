<?

/**
 * Rest Server Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *
 */

abstract class Core_RestServer
{

	protected $_supportedMethods = 'GET,HEAD,POST,PUT,DELETE';

	protected $_module;

	protected $_url;

	protected $_method;

	protected $_arguments = array ();

	protected $_restParameters = array ();

	protected $_restArguments = array ();

	protected $_restFilters = array ();

	protected $_accept;

	protected $_restParametersUrl;

	protected $_responseStatus = 200;

	protected $_restAuthHeaders = array ('username' => '', 'password' => '' );

	protected $_data = array ();

	protected $_responseData = '';

	protected $_lastCalled = '';

	/**
	 * Instantiate the Rest Server endpoint
	 */
	public function __construct()
	{
		$this->_module = str_replace ( 'Rest_', '', get_class ( $this ) );
		$this->_url = $this->getFullUrl ( $_SERVER );
		$this->_method = $_SERVER ['REQUEST_METHOD'];
		$this->_accept = @$_SERVER ['HTTP_ACCEPT'];
		$this->getArguments ();
		$this->parseRestParameters ();
		$this->_processNext ();
	}

	/**
	 * Retrieve data from the Rest Server
	 *
	 * @param $var string       	
	 */
	public final function __get($var)
	{
		if (isset ( $this->_data [$var] ))
		{
			return $this->_data [$var];
		}
		return null;
	}

	/**
	 * Set data for the REST Server
	 *
	 * @param $var string       	
	 * @param $value string       	
	 */
	public final function __set($var, $value)
	{
		$this->_data [$var] = $value;
	}

	/**
	 * Class destructor
	 */
	public function __destruct()
	{
	
	}

	/**
	 * Retrieve all REST Arguments
	 */
	protected final function getArguments()
	{
		switch ($this->_method)
		{
			case 'GET' :
			case 'HEAD' :
				$this->_arguments = $_GET;
				break;
			
			case 'POST' :
				$this->_arguments = $_POST;
				break;
			
			case 'PUT' :
			case 'DELETE' :
				parse_str ( file_get_contents ( 'php://input' ), $this->_arguments );
				$this->_arguments = array_merge ( $this->_arguments, $_GET );
				break;
			
			default :
				header ( 'Allow: ' . $this->_supportedMethods, true, 501 );
				break;
		}
	}

	/**
	 * Retrieve the full REST Call
	 *
	 * @param $_SERVER string       	
	 * @return string
	 */
	protected final function getFullUrl($_SERVER)
	{
		$protocol = @$_SERVER ['HTTPS'] == 'on' ? 'https' : 'http';
		$location = $_SERVER ['REQUEST_URI'];
		
		if ($_SERVER ['QUERY_STRING'])
		{
			$location = substr ( $location, 0, strrpos ( $location, $_SERVER ['QUERY_STRING'] ) - 1 );
		}
		
		return $protocol . '://' . $_SERVER ['HTTP_HOST'] . $location;
	}

	/**
	 * Header response for unallowed methods
	 */
	protected final function _methodNotAllowedResponse()
	{
		header ( 'Allow: ' . $this->_supportedMethods, true, 405 );
	}

	/**
	 * Process the REST call parameters
	 */
	protected final function parseRestParameters()
	{
		$this->_restParametersUrl = trim ( trim ( str_replace ( APP_URI . MODULE_FULLNAME . '/', '', $this->_url ) ), '/' );
		$this->_restParameters = ! ! $this->_restParametersUrl ? explode ( '/', $this->_restParametersUrl ) : array ();
		
		foreach ( $this->_restParameters as $key => $parameter )
		{
			$parameter = urldecode ( $parameter );
			
			$restFilters = array ();
			$arguments = array ();
			
			$matches = array ();
			if (preg_match ( '/\{.*\}/', $parameter, $matches ))
			{
				$this->_restParameters [$key] = str_replace ( $matches [0], '', $parameter );
				$restFilters = explode ( ',', substr ( $matches [0], 1, strlen ( $matches [0] ) - 2 ) );
			}
			
			$matches = array ();
			if (preg_match ( '/\[.*\]/', $parameter, $matches ))
			{
				$this->_restParameters [$key] = str_replace ( $matches [0], '', $parameter );
				$restArguments = explode ( ',', substr ( $matches [0], 1, strlen ( $matches [0] ) - 2 ) );
				
				foreach ( $restArguments as $restArgument )
				{
					if ($restArgument)
					{
						list ( $restArgumentKey, $restArgumentValue ) = explode ( '=', $restArgument );
						$arguments [$restArgumentKey] = $restArgumentValue;
					}
				}
			}
			
			$matches = array ();
			if (preg_match ( '/\{.*\}/', $this->_restParameters [$key], $matches ))
			{
				$this->_restParameters [$key] = str_replace ( $matches [0], '', $this->_restParameters [$key] );
				$restFilters = explode ( ',', substr ( $matches [0], 1, strlen ( $matches [0] ) - 2 ) );
			}
			
			$matches = array ();
			if (preg_match ( '/\[.*\]/', $this->_restParameters [$key], $matches ))
			{
				$this->_restParameters [$key] = str_replace ( $matches [0], '', $this->_restParameters [$key] );
				$restArguments = explode ( ',', substr ( $matches [0], 1, strlen ( $matches [0] ) - 2 ) );
				
				foreach ( $restArguments as $restArgument )
				{
					if ($restArgument)
					{
						list ( $restArgumentKey, $restArgumentValue ) = explode ( '=', $restArgument );
						$arguments [$restArgumentKey] = $restArgumentValue;
					}
				}
			}
			
			$this->_restFilters [$this->_restParameters [$key]] = $restFilters;
			$this->_restArguments [$this->_restParameters [$key]] = $arguments;
		}
	
	}

	/**
	 * Process the REST Call authentication headers
	 */
	public final function getRequestAuth()
	{
		return array ('username' => '' . @$_SERVER ['PHP_AUTH_USER'], 'password' => '' . @$_SERVER ['PHP_AUTH_PW'] );
	}

	/**
	 * Retrieve data from the Rest Server
	 *
	 * @param $var string       	
	 * @return mixed
	 */
	public final function get($var)
	{
		$var = '_' . $var;
		if (isset ( $this->$var ))
		{
			return $this->$var;
		}
		else
		{
			return null;
		}
	}

	/**
	 * Set HTTP Header status code for response
	 *
	 * @param $code string       	
	 */
	public final function setStatusCode($code)
	{
		$this->_responseStatus = $code;
		header ( "HTTP/1.1 $code", true, $code );
	}

	/**
	 * Returns the response data in JSON format when the object is called as a string
	 *
	 * @return string
	 */
	public final function __toString()
	{
		return Zend_Json::encode ( $this->_responseData );
	}

	/**
	 * Checks and returns the next REST Parameter
	 *
	 * @return string
	 */
	protected final function _getNextParameter()
	{
		if ($this->_hasParameter ())
		{
			return array_shift ( $this->_restParameters );
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if there is a succeeding REST Parameter
	 *
	 * @return boolean
	 */
	protected final function _hasParameter()
	{
		if (count ( $this->_restParameters ) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns a parameter onto the object list
	 *
	 * @param $parameter string       	
	 */
	protected final function _returnParameter($parameter)
	{
		array_unshift ( $this->_restParameters, $parameter );
	}

	/**
	 * Checks if the next parameter is an ID
	 *
	 * @return boolean
	 */
	protected final function _isNextParameterId()
	{
		if ($this->_hasParameter ())
		{
			$parameter = $this->_getNextParameter ();
			
			$data = explode ( ',', $parameter );
			$isId = true;
			
			foreach ( $data as $value )
			{
				if (! is_numeric ( $value ))
				{
					$isId = false;
				}
			}
			
			$this->_returnParameter ( $parameter );
			
			return $isId;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Automatically parses the url parameters and calls the appropriate objects
	 */
	protected final function _processNext()
	{
		// check if there is a parameter
		if ($this->_hasParameter ())
		{
			$objectName = $this->_getNextParameter ();
			
			// check if there is a table with the name $objectName
			$db = new Core_DB ();
			if (isset ( $db->dataset [strtolower ( $objectName )] ))
			{
				$restObject = "DefaultRestObject";
			}
			elseif (file_exists ( APP_CLASSES . 'Rest/' . ucfirst ( strtolower ( $objectName ) ) . '.class.php' ))
			{
				$restObject = 'Rest_' . ucfirst ( strtolower ( $objectName ) );
			}
			else
			{
				$this->setStatusCode ( 404 );
				exit ();
			}
			
			$objectIDs = array ();
			// check if there is an id
			if ($this->_isNextParameterId ())
			{
				$objectIDs = explode ( ',', $this->_getNextParameter () );
			}
			$objectInstance = new $restObject ( $this, $objectName, $objectIDs, $this->_arguments, $this->_restArguments [$objectName], $this->_restFilters [$objectName] );
			
			$objectInstance->setMethod ( $this->_method );
			
			$objectInstance->setResponseData ( $this->_responseData );
			
			$objectInstance->setLastCalled ( $this->_lastCalled );
			
			$objectInstance->process ();
			
			$this->_responseData = $objectInstance->getResponseData ();
			
			$this->_lastCalled = $objectName;
			
			if ($this->_hasParameter ())
			{
				$this->_processNext ();
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the method used in the REST Request
	 *
	 * @return string
	 */
	public final function getMethod()
	{
		return $this->_method;
	}
}