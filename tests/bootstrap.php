<?php
set_include_path(dirname(__FILE__) . '/../' . PATH_SEPARATOR . get_include_path());

// Set default timezone
date_default_timezone_set('America/New_York');

require_once 'src/Factory.php';
require_once 'src/Exceptions/DefinitionAlreadyDefinedException.php';
require_once 'src/Exceptions/DefinitionNotFoundException.php';
require_once 'src/Exceptions/DirectoryNotFoundException.php';
require_once 'src/Exceptions/ModelNotFoundException.php';
require_once 'src/Exceptions/SetterNotCallableException.php';
require_once 'src/Exceptions/DefinitionNotCallableException.php';

//Register non-Slim autoloader
function customAutoLoader( $class )
{
    $file = rtrim(dirname(__FILE__), '/') . '/' . $class . '.php';
    if ( file_exists($file) ) {
        require $file;
    } else {
        return;
    }
}
spl_autoload_register('customAutoLoader');
