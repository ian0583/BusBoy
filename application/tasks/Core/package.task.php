<?
$ignoreList = array ();
$ignoreList [] = 'log';
$ignoreList [] = 'data';
$ignoreList [] = 'uploads';
$ignoreList [] = 'application/views/.smarty/';

$this->message ( "Creating list of files to package...\n" );
$files = Core_Helper::listFiles ( APP_ROOT );

$fileList = array ();
foreach ( $files as $file )
{
	$allowed = true;
	foreach ( $ignoreList as $ignored )
	{
		if (strpos ( $file, APP_ROOT . $ignored ) === 0)
		{
			$allowed = false;
		}
	}
	
	if ($allowed)
	{
		$fileList [] = str_replace ( APP_ROOT, '', $file );
	}
}
unset ( $files );

$fh = fopen ( PACKAGE_LIST, 'w+' );

foreach ( $fileList as $file )
{
	fputs ( $fh, $file . "\n" );
}

fclose ( $fh );

$package = APP_UPLOAD . 'package_' . date ( 'Ymd' ) . '.tgz';

$this->message ( "Creating tar ball...\n" );

exec ( "tar -cvzf $package -T " . PACKAGE_LIST );

$this->message ( "Cleaning up...\n" );
unlink ( PACKAGE_LIST );

$this->message ( "Package is now ready and can be found at: $package" );