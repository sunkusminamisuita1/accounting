<?php
require_once  dirname(__DIR__) . '/config/session.php';
function getLoginUserId(): int
{
	if (empty($_SESSION['user']['id'])) {
		throw new Exception('ログインしていません');
	}
	return (int)$_SESSION['user']['id'];
}
function generateCsrfToken(): string
{
	$token = bin2hex(random_bytes(32));

	// 複数フォーム対応のため配列で保存
	$_SESSION['csrfTokens'][$token] = time();

	return $token;
}
function verifyCsrfToken(string $token): void
{
	if (empty($token) || empty($_SESSION['csrfTokens'][$token]) ) {
		http_response_code(403);
		exit('Invalid CSRF token');
	}
	$created = ($_SESSION['csrfTokens'][$token])??"";
	// 600秒 = 10分
	if (time() - $created > 600) {
		// ワンタイムなので削除
		unset($_SESSION['csrfTokens'][$token]);
		http_response_code(403);
		exit('CSRF token expired');
	}
}

function requireLogin(): void
{
	if (!getLoginUserId()) {
		header('Location: index.php?route=login');
		exit;
	}
}
?>
