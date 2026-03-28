<?php
require_once __DIR__ . '/../config/bootstrap';
$token = generateCsrfToken();
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	verifyCsrfToken($_POST['csrfToken'] ?? '');
	$username	= trim($_POST['username']);
	$email 		= trim($_POST['email']);
	$password	= $_POST['password'];
	$fiscalMonth	= $_POST['fiscalMonth'];
	$fiscalDay	= $_POST['fiscalDay'];
	if ($username && $email && $password) {
	// パスワードをハッシュ化して保存
		$hash = password_hash($password, PASSWORD_DEFAULT);
		try {
			$stmt = $pdo->prepare("INSERT INTO users 
				(username, email, password_hash, fiscal_month, fiscal_day) VALUES (?, ?, ?, ?, ?)");
			$stmt->execute([$username, $email, $hash, $fiscalMonth, $fiscalDay]);
			$message = "登録が完了しました！<a href='login.php'>ログインはこちら</a>";
		} catch (PDOException $e) {
			$message = "登録エラー: " . htmlspecialchars($e->getMessage());
		}
	} else {
		$message = "全ての項目を入力してください。";
	}
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ユーザー登録</title>
</head>
<body>
<h1>新規登録</h1>
<form method="post">
	<input type="hidden" name="csrfToken" value="<?= h($token) ?>">
	<p>ユーザー名: <input type="text" name="username" required></p>
	<p>メールアドレス: <input type="email" name="email" required></p>
	<p>パスワード: <input type="password" name="password" required></p>
<p>決算 月(1-12): <input type="text" name="fiscalMonth" required></p>
<p>決算 日(1-31): <input type="text" name="fiscalDay" required></p>
	<button type="submit">登録</button>
</form>
<p style="color:red;"><?= $message ?></p>
<p><a href="login.php">ログインへ戻る</a></p>
</body>
</html>

