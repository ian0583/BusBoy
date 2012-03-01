<?
require APP_CONF . 'DB.conf.php';

$processed = array ();
foreach ( $DATABASES as $database )
{
	$hostdb = serialize ( array ('host' => $database ['host'], 'db' => $database ['db'] ) );
	
	// try to connect to the database first
	$connects = @mysql_connect ( $database ['host'], $database ['username'], $database ['password'] );
	$exists = @mysql_select_db ( $database ['db'] );
	
	if (! in_array ( $hostdb, $processed ) && $connects && $exists)
	{
		mysql_close ( $connects );
		$sqlfile = APP_DATA . 'sql/' . $database ['host'] . '_' . $database ['db'] . '.sql';
		$command = "mysqldump --opt -h $database[host] -u$database[username] -p$database[password] $database[db] > $sqlfile";
		
		$this->message ( $command );
		
		exec ( $command );
		$processed [] = $hostdb;
	}
}
