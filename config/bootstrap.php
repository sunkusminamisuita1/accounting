<?php
define('ROOT_PATH', dirname(__DIR__));
echo "1".$_SESSION['flash_message']."<br>";
require_once ROOT_PATH . '/app/DTO/Constants.php';
require_once ROOT_PATH . '/lib/helpers.php';
echo "2".$_SESSION['flash_message']."<br>";

require_once ROOT_PATH . '/config/session.php';

require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/controllers/AuthController.php';
require_once ROOT_PATH . '/app/controllers/LogoutController.php';
require_once ROOT_PATH . '/app/repositories/ReportRepository.php';
require_once ROOT_PATH . '/app/services/ReportService.php';
require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
require_once ROOT_PATH . '/app/controllers/HomeController.php';
require_once ROOT_PATH . '/app/services/lib/HomeLib.php';
require_once ROOT_PATH . '/app/controllers/VoucherController.php';

?>
