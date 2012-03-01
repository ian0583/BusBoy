<?

/**
 * Class for commonly used functions
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *
 */
class Core_Helper
{

	/**
	 * HTML Formatted print_r alias
	 *
	 * @param $mixed mixed       	
	 */
	public static function show($mixed)
	{
		echo '<pre>';
		print_r ( $mixed );
		echo '</pre>';
	}

	/**
	 * Sanitizes the given string to prevent SQL Injection and HTML Entities
	 *
	 * @param $data mixed       	
	 * @return mixed
	 */
	public static function sanitize($data)
	{
		if (is_array ( $data ))
		{
			foreach ( $data as $key => $value )
			{
				if (is_array ( $value ))
				{
					$data [$key] = Core_Helper::sanitizeAll ( $value );
				}
				else
				{
					$data [$key] = addslashes ( urlencode ( $value ) );
				}
			}
			
			return $data;
		}
		else
		{
			return addslashes ( urlencode ( $data ) );
		}
	
	}

	/**
	 * Desanitizes the given string that was previously sanitized
	 *
	 * @param $data mixed       	
	 * @return mixed
	 */
	public static function desanitize($data)
	{
		if (is_array ( $data ))
		{
			foreach ( $data as $key => $value )
			{
				if (is_array ( $value ))
				{
					$data [$key] = Core_Helper::desanitize ( $value );
				}
				else
				{
					$data [$key] = urldecode ( stripslashes ( $value ) );
				}
			}
			
			return $data;
		}
		else
		{
			return urldecode ( stripslashes ( $data ) );
		}
	}

	/**
	 * Converts a numerical Value to its string equivalent
	 *
	 * @param $number integer       	
	 * @return string
	 */
	public static function numberToString($number)
	{
		if (($number < 0) || ($number > 999999999))
		{
			throw new Exception ( "Number is out of range" );
		}
		
		$Gn = floor ( $number / 1000000 ); /*
		                                    * Millions (giga)
		                                    */
		$number -= $Gn * 1000000;
		$kn = floor ( $number / 1000 ); /*
		                                 * Thousands (kilo)
		                                 */
		$number -= $kn * 1000;
		$Hn = floor ( $number / 100 ); /*
		                                * Hundreds (hecto)
		                                */
		$number -= $Hn * 100;
		$Dn = floor ( $number / 10 ); /*
		                               * Tens (deca)
		                               */
		$n = $number % 10; /*
		                    * Ones
		                    */
		
		$res = "";
		
		if ($Gn)
		{
			$res .= Core_Helper::convert_number ( $Gn ) . " Million";
		}
		
		if ($kn)
		{
			$res .= (empty ( $res ) ? "" : " ") . Core_Helper::convert_number ( $kn ) . " Thousand";
		}
		
		if ($Hn)
		{
			$res .= (empty ( $res ) ? "" : " ") . Core_Helper::convert_number ( $Hn ) . " Hundred";
		}
		
		$ones = array ("", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen", "Nineteen" );
		$tens = array ("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty", "Seventy", "Eigthy", "Ninety" );
		
		if ($Dn || $n)
		{
			if (! empty ( $res ))
			{
				$res .= " and ";
			}
			
			if ($Dn < 2)
			{
				$res .= $ones [$Dn * 10 + $n];
			}
			else
			{
				$res .= $tens [$Dn];
				
				if ($n)
				{
					$res .= "-" . $ones [$n];
				}
			}
		}
		
		if (empty ( $res ))
		{
			$res = "zero";
		}
		
		return $res;
	}

	/**
	 * Generates a random string to be used as a password
	 *
	 * @param $length int       	
	 * @return string
	 */
	public static function randomPassword($length = 8)
	{
		$vowels = 'aeuyAEUY';
		$consonants = 'bdghjmnpqrstvzBDGHJLMNPQRSTVWXZ';
		
		$password = '';
		for($i = 0; $i < $length; $i ++)
		{
			if ($i % 2 == 0)
			{
				$password .= $consonants [(rand () % strlen ( $consonants ))];
			}
			else
			{
				$password .= $vowels [(rand () % strlen ( $vowels ))];
			}
		}
		
		return $password;
	}

	/**
	 * Converts the amount in seconds to a H:i:s format
	 *
	 * @param $ts integer       	
	 * @return string
	 */
	public static function secondsToTime($ts)
	{
		$ts = ( int ) $ts;
		
		$hours = floor ( $ts / 3600 );
		
		$ts -= $hours * 3600;
		
		$minutes = floor ( $ts / 60 );
		
		$ts -= $minutes * 60;
		
		$seconds = $ts;
		
		return $hours . ':' . $minutes . ':' . $seconds;
	}

	/**
	 * Pretty formats a JSON value
	 *
	 * @param $json string       	
	 * @return string
	 */
	public static function formatJson($json)
	{
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;
		
		$json_obj = json_decode ( $json );
		
		if ($json_obj === false)
			return false;
		
		$json = json_encode ( $json_obj );
		$len = strlen ( $json );
		
		for($c = 0; $c < $len; $c ++)
		{
			$char = $json [$c];
			switch ($char)
			{
				case '{' :
				case '[' :
					if (! $in_string)
					{
						$new_json .= $char . "\n" . str_repeat ( $tab, $indent_level + 1 );
						$indent_level ++;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '}' :
				case ']' :
					if (! $in_string)
					{
						$indent_level --;
						$new_json .= "\n" . str_repeat ( $tab, $indent_level ) . $char;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ',' :
					if (! $in_string)
					{
						$new_json .= ",\n" . str_repeat ( $tab, $indent_level );
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ':' :
					if (! $in_string)
					{
						$new_json .= ": ";
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '"' :
					if ($c > 0 && $json [$c - 1] != '\\')
					{
						$in_string = ! $in_string;
					}
				default :
					$new_json .= $char;
					break;
			}
		}
		
		return $new_json;
	}

	/**
	 * Returns if a string (str) is a prefix of a given text (text)
	 *
	 * @param $str string       	
	 * @param $text string       	
	 * @return boolean
	 */
	public static function isPrefix($str, $text)
	{
		$rest = substr ( $text, 0, strlen ( $str ) );
		
		if ($rest == $str)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Returns the numerical permissions of a given file
	 *
	 * @param $file string       	
	 * @return string
	 */
	public function filePermissions($file)
	{
		return substr ( decoct ( fileperms ( $file ) ), - 3 ) . '';
	}

	/**
	 * Updates the session via the application scope
	 *
	 * @param $key string       	
	 * @param $data mixed       	
	 */
	public static function updateSession($key, $data)
	{
		$_SESSION [APP_NAME] [$key] = $data;
	}

	/**
	 * Retrieve data from the session via the application scope
	 *
	 * @param $key string       	
	 * @return mixed
	 */
	public static function getSession($key)
	{
		if (isset ( $_SESSION [APP_NAME] [$key] ))
		{
			return $_SESSION [APP_NAME] [$key];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Clears data in the session via the application scope
	 *
	 * @param $key string       	
	 */
	public static function clearSession($key)
	{
		if (isset ( $_SESSION [APP_NAME] [$key] ))
		{
			unset ( $_SESSION [APP_NAME] [$key] );
		}
	}

	/**
	 * Generates a list of all files in a given path
	 *
	 * @param $path string       	
	 * @return array
	 */
	public static function listFiles($path)
	{
		$dh = opendir ( $path );
		
		$files = array ();
		$file = readdir ( $dh );
		while ( $file )
		{
			if (! in_array ( $file, array ('.', '..', '.svn' ) ))
			{
				if (is_dir ( $path . $file ))
				{
					$files = array_merge ( $files, self::listFiles ( $path . $file . '/' ) );
				}
				else
				{
					$files [] = $path . $file;
				}
			}
			$file = readdir ( $dh );
		}
		
		return $files;
	}

	/**
	 * Formats the error backtrace
	 *
	 * @param $data mixed       	
	 * @param $key string       	
	 * @param $length string       	
	 */
	public static function prettyBacktrace($data, $key, $length)
	{
		if ($key > 0)
		{
			extract ( $data, EXTR_REFS );
			echo "<br/><b>" . ($length - $key) . ":</b><br/>\n";
			echo "<b>File:</b> $file<br/>\n";
			echo "<b>Line:</b> $line<br/>\n";
			echo "<b>Function:</b> $function<br/>\n";
		}
	}

	/**
	 * Return unique array elements with support for multidimensional arrays
	 *
	 * @param $array array       	
	 * @param $preserveKeys boolean       	
	 */
	public static function arrayUnique($array, $preserveKeys = false)
	{
		if (! is_array ( $array ))
		{
			return $array;
		}
		// Unique Array for return
		$arrayRewrite = array ();
		// Array with the md5 hashes
		$arrayHashes = array ();
		foreach ( $array as $key => $item )
		{
			// Serialize the current element and create a md5 hash
			$hash = md5 ( serialize ( $item ) );
			// If the md5 didn't come up yet, add the element to
			// to arrayRewrite, otherwise drop it
			if (! isset ( $arrayHashes [$hash] ))
			{
				// Save the current element hash
				$arrayHashes [$hash] = $hash;
				// Add element to the unique Array
				if ($preserveKeys)
				{
					$arrayRewrite [$key] = $item;
				}
				else
				{
					$arrayRewrite [] = $item;
				}
			}
		}
		return $arrayRewrite;
	}
}