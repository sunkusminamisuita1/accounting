<?php
//echo "bootstrap.php0 debug";
define('ROOT_PATH', dirname(__DIR__));
//echo "bootstrap.php1 debug";
require_once ROOT_PATH . '/app/DTO/Constants.php';
require_once ROOT_PATH . '/config/session.php';
//echo "bootstrap.php2 debug";
require_once ROOT_PATH . '/config/db.php';
//echo "bootstrap.php3 debug";
require_once ROOT_PATH . '/lib/helpers.php';
//echo "bootstrap.php4 debug";
require_once ROOT_PATH . '/app/auth.php';
//echo "bootstrap.php5 debug";
require_once ROOT_PATH . '/app/controllers/AuthController.php';
require_once ROOT_PATH . '/app/controllers/LogoutController.php';
//echo "bootstrap.php6 debug";
require_once ROOT_PATH . '/app/repositories/ReportRepository.php';
//echo "bootstrap.php7 debug";
require_once ROOT_PATH . '/app/services/ReportService.php';
//echo "bootstrap.php8 debug";
require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
//echo "bootstrap.php9 debug";
require_once ROOT_PATH . '/app/controllers/HomeController.php';
//echo "bootstrap.php debug";
require_once ROOT_PATH . '/app/services/lib/HomeLib.php';

?>
