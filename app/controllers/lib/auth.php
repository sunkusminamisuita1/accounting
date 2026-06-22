<?php
function getLoginUserId(): int
{
	if (empty($_SESSION['user']['id'])) {
		// 1. エラーメッセージをセッションに保存（移動先で表示するため）
		$_SESSION['flash_message'] = "再度ログインしてください。";
	}
	return (int)($_SESSION['user']['id'] ?? 0);
}

function generateCsrfToken(): string
{
	// 古いトークン削除（2時間以上）
	foreach ($_SESSION['csrfTokens'] ?? [] as $t => $time) {
		if (time() - $time > 7200) {
			unset($_SESSION['csrfTokens'][$t]);
		}
	}
	$tokenKey = bin2hex(random_bytes(32));
	$_SESSION['csrfTokens'][$tokenKey] = time();
	return $tokenKey;
}

function verifyCsrfToken(string $FmTknKey): void
{
	echo "<br><pre> {$FmTknKey}  </pre><br>";
	echo "<br><pre> {$_SESSION['csrfTokens'][$FmTknKey] } </pre><br>";
	if (empty($FmTknKey) || empty($_SESSION['csrfTokens'][$FmTknKey]) ) {
		// 詳細ログ（デバッグ用）
		//error_log("[CSRF] verify failed. Posted token=" . var_export($FmTknKey, true));
		//error_log("[CSRF] session tokens=" . var_export($_SESSION['csrfTokens'] ?? [], true));
		http_response_code(403);
		exit('Invalid CSRF token-X');
	}

	$created = $_SESSION['csrfTokens'][$FmTknKey] ?? 0;
	// トークン有効期間（秒）
	$ttl = 3600; // 1 hour
	if (!is_numeric($created) || (time() - (int)$created > $ttl)) {
		// ワンタイムなので削除
		unset($_SESSION['csrfTokens'][$FmTknKey]);
		// デバッグログ出力
		//error_log("[CSRF] token expired. token=" . var_export($FmTknKey, true) . " created_at=" . var_export($created, true));
		$_SESSION['flash_message'] = "セッションの有効期限が切れたか、不正な操作が行われました。再度ログインしてください。";
		header('Location: index.php?route=login');
		exit;
	}

	// ワンタイムなので削除
	unset($_SESSION['csrfTokens'][$FmTknKey]);
}

function requireLogin(): void
{
	if (!getLoginUserId()) {
		header('Location: index.php?route=login');
		exit;
	}
}

function requireCsrf(): void
{
	requirePost();
	verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
}

?>