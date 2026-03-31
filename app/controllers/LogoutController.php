<?php
class LogoutController{
//    private $service;
//    public function __construct() {
//        $this->service = new ReportService();
//    }
    public function index() {
// セッション開始（未開始の場合）
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

// 1. セッション変数をすべて空にする
        $_SESSION = array();

// 2. ブラウザ側のセッションクッキーを削除する
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
// 3. サーバー側のセッションデータを破棄する
        session_destroy();
// 4. リダイレクト（ログアウト後の画面へ）
        header("Location: /index.php?route=login");
        exit;
    }
}
?>
