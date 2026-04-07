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
    	$caller = $trace[1]; 
		http_response_code(403);
		exit('Invalid CSRF token-X');
	}
	$created = ($_SESSION['csrfTokens'][$FmTknKey])??"";
	// 600秒 = 10分
	if (time() - $created > 100) {
		// ワンタイムなので削除
		unset($_SESSION['csrfTokens'][$FmTknKey]);
		http_response_code(403);
		$trace = debug_backtrace();
    	$caller = $trace[1];
		echo "	<script>
    			    alert('セッションの有効期限が切れたか、不正な操作が行われました。\\n再度ログインしてください。');
        			window.location.href = 'index.php?route=login';
    			</script>
		";
		exit;
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