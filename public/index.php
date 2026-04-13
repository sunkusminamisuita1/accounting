<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__.'/../config/bootstrap.php';
$route = $_GET['route'] ?? 'login';
$routes =   [
                'login' => [
                        'controller' => AuthController::class,
                        'method' => 'login',
                        'auth' => false
                ],

                'register' => [
                        'controller' => AuthController::class,
                        'method' => 'register',
                        'auth' => false
                ],

                'home' => [
                        'controller' => HomeController::class,
                        'method' => 'index',
                        'auth' => true
                ],
                'voucher.index' => [
                        'controller' => VoucherController::class,
                        'method' => 'index',
                        'auth' => true
                ],

                'voucher.edit' => [
                        'controller' => VoucherController::class,
                        'method' => 'edit',
                        'auth' => true
                ],

                'voucher.update' => [
                        'controller' => VoucherController::class,
                        'method' => 'update',
                        'auth' => true
                ],

                'voucher.delete' => [
                        'controller' => VoucherController::class,
                        'method' => 'delete',
                        'auth' => true
                ],
                'voucher.create' => [
                        'controller' => VoucherController::class,
                        'method' => 'create',
                        'auth' => true
                ],
//               'voucher.add' => [
//                       'controller' => VoucherController::class,
//                       'method' => 'add',
//                       'auth' => true
//                ],
                'voucher.store' => [
                        'controller' => VoucherController::class,
                        'method' => 'store',
                        'auth' => true
                ],
                'logout' => [
                        'controller' => LogoutController::class,
                        'method' => 'index',
                        'auth' => true
                ],

];
if (!isset($routes[$route])) {
    http_response_code(404);
    exit('Not Found');
}
$routeInfo = $routes[$route];
if ($routeInfo['auth']) {
    requireLogin();
}
$controllerName = $routeInfo['controller'];
$method = $routeInfo['method'];
$controller = new $controllerName();
$controller->$method();
