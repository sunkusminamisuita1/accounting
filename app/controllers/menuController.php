<?php
class MenuController{
	public function login()	{
		$message = '';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requireCsrf();
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
			$tokenKey = $_POST['csrfTokenKey'];
		}else{
			$tokenKey  = generateCsrfToken();
		}
		require ROOT_PATH.'/views/auth/login.php';
	}

	//誤って、削除(いつ削除したかは不明)　以降はvscodeで再実装してもらった。2026-08-4
	private function loginUser($user)
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		session_regenerate_id(true);

		$sessionUser = $user;
		unset($sessionUser['password_hash']);

		$_SESSION['user'] = $sessionUser;
	}
}
