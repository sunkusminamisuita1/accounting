<?php
//file_put_contents('/tmp/debug.log', "メソッド通ったよ！\n", FILE_APPEND);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__.'/../config/bootstrap.php';
$route = $_GET['route'] ?? 'login';
$routes =   [
                //login処理　shopsテーブルも読み込む
                'login' => [
                        'controller' => authController::class,
                        'method' => 'login',
                        'auth' => false
                ],

                'register' => [
                        'controller' => authController::class,
                        'method' => 'register',
                        'auth' => false
                ],

                'home' => [
                        'controller' => homeController::class,
                        'method' => 'index',
                        'auth' => true
                ],
                //仕分け伝票一覧
                'voucher.index' => [
                        'controller' => voucherController::class,
                        'method' => 'index',
                        'auth' => true
                ],
                //仕分け伝票編集
                'voucher.edit' => [
                        'controller' => voucherController::class,
                        'method' => 'edit',
                        'auth' => true
                ],
                //仕分け伝票更新
                'voucher.update' => [
                        'controller' => voucherController::class,
                        'method' => 'update',
                        'auth' => true
                ],

                //仕分け伝票修正検索
                'voucher.list' => [
                        'controller' => voucherController::class,
                        'method' => 'list',
                        'auth' => true
                ],

                'voucher.delete' => [
                        'controller' => voucherController::class,
                        'method' => 'delete',
                        'auth' => true
                ],
                'voucher.create' => [
                        'controller' => voucherController::class,
                        'method' => 'create',
                        'auth' => true
                ],
                //勘定科目　追加
                'accounts.edit' => [
                       'controller' => accountsController::class,
                       'method' => 'index',
                       'auth' => true
                ],
                'voucher.store' => [
                        'controller' => voucherController::class,
                        'method' => 'store',
                        'auth' => true
                ],
                'logout' => [
                        'controller' => logoutController::class,
                        'method' => 'index',
                        'auth' => true
                ],
                // 店舗情報切替
                'shop.switch' => [
                        'controller' => shopController::class,
                        'method' => 'switch',
                        'auth' => true
                ],
                // 店舗情報編集
                'shop.edit' => [
                       'controller' => shopController::class,
                       'method' => 'edit',
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
