<?php
//file_put_contents('/tmp/debug.log', "メソッド通ったよ！\n", FILE_APPEND);
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
                //仕分け伝票一覧
                'voucher.index' => [
                        'controller' => VoucherController::class,
                        'method' => 'index',
                        'auth' => true
                ],
                //仕分け伝票編集
                'voucher.edit' => [
                        'controller' => VoucherController::class,
                        'method' => 'edit',
                        'auth' => true
                ],
                //仕分け伝票更新
                'voucher.update' => [
                        'controller' => VoucherController::class,
                        'method' => 'update',
                        'auth' => true
                ],

                //仕分け伝票修正検索
                'voucher.list' => [
                        'controller' => VoucherController::class,
                        'method' => 'list',
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
                //勘定科目　追加
                'accounts.edit' => [
                       'controller' => AccountsController::class,
                       'method' => 'add',
                       'auth' => true
                ],
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
