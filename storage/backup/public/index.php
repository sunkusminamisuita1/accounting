<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/bootstrap.php';
$route = $_GET['route'] ?? 'login';

$routes = 	[
					'home' 			=> 'views/home.php',
					'login' 		=> 'views/login.php',
					'logout' 		=> 'views/logout.php',
					'register' 		=> 'views/register.php',
					'voucher.create'=> 'actions/voucher_create.php',
					'voucher.add' 	=> 'actions/voucher_add.php',
					'voucher.alt' 	=> 'actions/voucher_alt.php',
					'voucher.store' => 'actions/voucher_store.php',
			];
if (!array_key_exists($route, $routes)) {
	http_response_code(404);
	exit('Route Not Found');
}

$protectedRoutes = [
					'home',
					'logout',
					'voucher.create',
					'voucher.add',
					'voucher.alt',
					'voucher.store'
					];

if (in_array($route, $protectedRoutes, true)) {
	requireLogin();
}
require __DIR__ . '/../' . $routes[$route];
?>
