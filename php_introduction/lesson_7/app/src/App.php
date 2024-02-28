<?php

namespace Root\App;

use app\models\UserModel;
use HttpSoft\Response\HtmlResponse;
use Root\App\services\Helper;
use Root\App\services\Render;

class App
{
    static protected App $app;
    public UserModel $user;
    
    public function run(): string
    {
        try {
            session_start();
            // if (empty($_SESSION['authHash']) && !empty($_COOKIE['authHash'])) {
            //     $_SESSION['authHash'] = $_COOKIE['authHash'];
            // }
            $this::$app = &$this;
            
            $uri = urldecode($_SERVER['REQUEST_URI']);
            $query = urldecode($_SERVER['QUERY_STRING']);
            $address = rtrim(!empty($query) ? str_replace($query, '', $uri) : $uri, '?');
            $url = explode('/', ltrim($address, '/'));
            if (count($url) < 2 || empty($controllerName = array_shift($url))) {
                $controllerName = 'page';
            }
            if (empty($methodName = array_shift($url))) {
                $methodName = 'index';
            }
            
            // View error pages
            if ($controllerName === 'page' && str_starts_with($methodName, 'error')) {
                try {
                    $response = new HtmlResponse('', (int)substr($methodName, 5));
                    $code = $response->getStatusCode();
                    $message = $response->getReasonPhrase();
                } catch (\Throwable) {
                    $code = 404;
                    $message = 'Page not found!';
                }
                throw new \Exception($message, $code);
            }
            
            $controllerName = Helper::getController(ucfirst($controllerName) . 'Controller');
            $methodName = 'action' . ucfirst($methodName);
            
            // echo '<pre>';
            // print_r([
            //     '$controllerName' => $controllerName,
            //     '$methodName' => $methodName,
            //     '$props' => $url,
            // ]);
            // echo '</pre>';
            
            if (!class_exists($controllerName)) {
                throw new \Exception('Page not found!', 404);
            }
            if (!method_exists($controllerName, $methodName) && !method_exists($controllerName, '__call')) {
                throw new \Exception('Page not found!', 404);
            }
            return (new $controllerName())->$methodName(...$url);
        } catch (\ArgumentCountError) {
            header("HTTP/1.1 404");
            return Render::app()->renderError('Page not found!', 404);
        } catch (\Throwable $e) {
            if ($e->getCode() > 0) {
                header("HTTP/1.1 {$e->getCode()}");
            }
            return Render::app()->renderError($e->getMessage(), $e->getCode());
        }
    }
}