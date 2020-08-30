<?php
//Валидатор https://github.com/Respect/Validation
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('upload_max_filesize', '20M');
ini_set('display_errors', 1);
session_start();

define('DEFAULT_CONTROLLER', 'Product');
define('DEFAULT_ACTION', 'index');
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('BP', dirname(dirname(__FILE__)));
define('MBP', dirname(dirname(__FILE__)).'/modmanager/');
define('MMP', '/assets/modules/modmanager/');

include dirname(realpath(__FILE__))."/Core/Core.php";
include dirname(realpath(__FILE__))."/Core/Components/Func.php";//small functions

use Core\Components\Request;
use Core\Render\Render;

$action = (Request::getRequest('action') != '' ? Request::getRequest('action') : DEFAULT_ACTION).'Action';
$controller = 'App\\Controller\\'.(Request::getRequest('controller') != '' ? ucfirst(Request::getRequest('controller')) : DEFAULT_CONTROLLER).'Controller';

$content = call_user_func_array(array(new $controller(), $action), array());

$render = new Render();
$render->set('content', $content);

echo $render->display("layout");
