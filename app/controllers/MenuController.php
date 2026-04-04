<?php
class MenuController{
	public function login()	{
		$message = '';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requirePost();
			verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
			$pdo = getPDO();
			$email = trim($_POST['email']);
			$password = $_POST['password'];
			$stmt = $pdo->prepare("
				SELECT id, username, email, password_hash,
				fiscal_month, fiscal_day
				FROM users
				WHERE email = ?
			");
			$stmt->execute([$email]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($user && password_verify($password, $user['password_hash'])) {
				$this->loginUser($user);
				header('Location: index.php?route=home');
				exit;
			} else {
				$message = "メールアドレスまたはパスワードが間違っています。";
			}
		}
		require ROOT_PATH.'/views/auth/login.php';
	}
}
