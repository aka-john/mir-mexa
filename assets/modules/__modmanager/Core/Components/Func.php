<?php
/*
 * Function include in all part of project
 */
function CRM_GET($key)
{
    return isset($_GET[$key]) ? $_GET[$key] : null;
}

function CRM_POST($key)
{
    return isset($_POST[$key]) ? $_POST[$key] : null;
}

function CRM_IS_AJAX()
{
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return true;
    } else {
        return false;
    }
}

function CRM_GET_REQUEST()
{
    return isset($_REQUEST) ? $_REQUEST : null;
}

function CRM_GET_FILES()
{
    return isset($_FILES) ? $_FILES : null;
}

function CRM_IS_POST()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function CRM_GET_METHOD()
{
    $method =  $_SERVER['REQUEST_METHOD'];

    if(CRM_IS_POST()){
        if(isset($_SERVER['X-HTTP-METHOD-OVERRIDE'])){
            $method = strtoupper($_SERVER['X-HTTP-METHOD-OVERRIDE']);
        }
    }

    return $method;
}

function CRM_IS_HTTPS()
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
}

function CRM_GET_HTTP_HOST()
{
    $host = CRM_IS_HTTPS() ? 'https://' : 'http://';
    $host .= CRM_GET_HOST();
    return $host;
}

function CRM_GET_HOST()
{
    $host = $_SERVER['HTTP_HOST'];

    $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

    if ($host && !preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host)) {
        throw new \UnexpectedValueException(sprintf('Invalid Host "%s"', $host));
    }

    return $host;
}

function CRM_GET_ROOT_PATH()
{
    return dirname(__FILE__).'/../../../../../';
}

function CRM_CURRENT_URL()
{
    return  (CRM_IS_HTTPS() ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"].(isset($_GET) ? '?' : '');
}

function CRM_GET_REFERER_URL()
{
    return $_SERVER['HTTP_REFERER'];
}

function CRM_MODULE_PATH() {
    return 'index.php?a='.$_REQUEST['a'].'&id='.$_REQUEST['id'];
}

function CRM_MODULE_ACTION_PATH() {
    return 'index.php?a='.$_REQUEST['a'].'&id='.$_REQUEST['id'].'&controller='.$_REQUEST['controller'].'&action='.$_REQUEST['action'];
}

function CRM_MODULE_CONTROLLER() {
    return strtolower($_REQUEST['controller']);
}

function CRM_MODULE_ACTION() {
    return strtolower($_REQUEST['action']);
}

function CRM_GET_PATH_INFO($baseUrl = null)
{
    static $pathInfo;

    if (!$pathInfo) {
        $pathInfo = $_SERVER['REQUEST_URI'];

        if (!$pathInfo) {
            $pathInfo = '/';
        }

        $schemeAndHttpHost = CRM_IS_HTTPS() ? 'https://' : 'http://';
        $schemeAndHttpHost .= $_SERVER['HTTP_HOST'];

        if (strpos($pathInfo, $schemeAndHttpHost) === 0) {
            $pathInfo = substr($pathInfo, strlen($schemeAndHttpHost));
        }

        if ($pos = strpos($pathInfo, '?')) {
            $pathInfo = substr($pathInfo, 0, $pos);
        }

        if (null != $baseUrl) {
            $pathInfo = substr($pathInfo, strlen($pathInfo));
        }

        if (!$pathInfo) {
            $pathInfo = '/';
        }
    }

    return $pathInfo;
}

function CRM_GET_MAIN_PATH()
{
    $path = explode('/',CRM_GET_PATH_INFO());
    return '/'.$path[1];
}

function CRM_TIMER_START()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $start = $time;
    $_SESSION['dev']['timer']['start'] = $start;
}

function CRM_TIMER_END()
{
    $time = microtime();
    $time = explode(' ', $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round(($finish - $_SESSION['dev']['timer']['start']), 4);
    $_SESSION['dev']['timer']['total_time'] = $total_time;
}

function CRM_GET_MEMORY()
{
    $unit = array('b','kb','mb','gb','tb','pb');
    $_SESSION['dev']['memory'] = @round(memory_get_usage()/pow(1024,($i=floor(log(memory_get_usage(),1024)))),2).' '.$unit[$i];
}