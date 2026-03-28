<?php

// プロジェクトルート
define('ROOT_PATH', dirname(__DIR__));
// セッション開始
require_once ROOT_PATH . '/config/session.php';
// DB接続
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/lib/helpers.php';
require_once ROOT_PATH . '/app/auth.php';

?>
