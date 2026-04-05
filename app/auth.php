<?php
function getLoginUserId(): int
{
	if (empty($_SESSION['user']['id'])) {
		throw new Exception('ログインしていません');
	}
	return (int)$_SESSION['user']['id'];
}
function generateCsrfToken(): string
{
	$tokenKey = bin2hex(random_bytes(32));
	$_SESSION['csrfTokens'][$tokenKey] = time();
	return $tokenKey;
}
function verifyCsrfToken(string $FmTknKey): void
{
	if (empty($FmTknKey) || empty($_SESSION['csrfTokens'][$FmTknKey]) ) {


		$trace = debug_backtrace();
    	$caller = $trace[1]; // インデックス 0 は現在の関数、1 は呼び出し元
    	echo "error;". $trace[0] . "呼び出し元: " . $caller['file'] . " 行 " . $caller['line'] . "<br>";



		http_response_code(403);
		exit('Invalid CSRF token-X');
	}
	$created = ($_SESSION['csrfTokens'][$FmTknKey])??"";
	// 600秒 = 10分
	if (time() - $created > 600) {
		// ワンタイムなので削除
		unset($_SESSION['csrfTokens'][$FmTknKey]);
		http_response_code(403);
		$trace = debug_backtrace();
    	$caller = $trace[1]; // インデックス 0 は現在の関数、1 は呼び出し元
    	echo "error;". $trace[0] . "呼び出し元: " . $caller['file'] . " 行 " . $caller['line'] . "<br>";
		exit('CSRF token expired');
	}
}
function requireLogin(): void
{
	echo "requireLogin2<br>";
	if (!getLoginUserId()) {
		echo "requireLogin1<br>";
		header('Location: index.php?route=login');
		exit;
	}
}
?>
