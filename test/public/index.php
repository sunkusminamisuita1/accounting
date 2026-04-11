<?php
// /test/public/index.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', dirname(__DIR__));

// 共通ファイルのインクルード
require_once ROOT_PATH . '/../config/bootstrap.php';

// DTO
require_once ROOT_PATH . '/DTO/VoucherDTO.php';

// Services
require_once ROOT_PATH . '/services/VoucherService.php';

// Validators
require_once ROOT_PATH . '/Validators/VoucherValidate.php';

// Repositories
require_once ROOT_PATH . '/repositories/VoucherRepository.php';

// Controllers
require_once ROOT_PATH . '/controllers/VoucherController.php';

$route = $_GET['route'] ?? 'voucher.create';

$routes = [
    'voucher.create' => [
        'controller' => VoucherController::class,
        'method' => 'create',
        'auth' => true
    ],
    'voucher.add' => [
        'controller' => VoucherController::class,
        'method' => 'add',
        'auth' => true
    ],
    'voucher.delete' => [
        'controller' => VoucherController::class,
        'method' => 'delete',
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
?>