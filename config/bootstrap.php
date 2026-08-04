<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/dto/Constants.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/config/session.php';
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/app/controllers/lib/auth.php';
require_once ROOT_PATH . '/app/controllers/authController.php';
require_once ROOT_PATH . '/app/controllers/logoutController.php';
require_once ROOT_PATH . '/app/repositories/reportRepository.php';
require_once ROOT_PATH . '/app/services/reportService.php';
require_once ROOT_PATH . '/app/services/lib/homeLib.php';
require_once ROOT_PATH . '/app/controllers/homeController.php';
require_once ROOT_PATH . '/app/controllers/accountsController.php';
require_once ROOT_PATH . '/app/controllers/voucherController.php';
require_once ROOT_PATH . '/app/controllers/shopController.php';
?>
