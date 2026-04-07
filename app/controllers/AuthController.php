<?php
class AuthController{
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

				session_regenerate_id(true);
					$_SESSION['user']	=	[
												'id'			=>	(int)$user['id'],
												'user_id'		=>	(int)$user['id'],
												'username'		=>	$user['username'],
												'fiscalMonth'	=>	$user['fiscal_month'],
												'fiscalDay'		=>	$user['fiscal_day']
											];


				header('Location: index.php?route=home');
				exit;
			} else {
				$message = "メールアドレスまたはパスワードが間違っています。";
			}
		}
		$TokenKey = generateCsrfToken();
		require ROOT_PATH.'/views/auth/login.php';
	}
	public function register()	{
		$message = '';
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			requirePost();
			verifyCsrfToken($_POST['csrfTokenKey'] ?? '');
			$pdo = getPDO();
			$username = trim($_POST['username']);
			$email = trim($_POST['email']);
			$password = $_POST['password'];
			$fiscalMonth = (int)$_POST['fiscal_month'];
			$fiscalDay   = (int)$_POST['fiscal_day'];
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);
			try {
				$stmt = $pdo->prepare("
					INSERT INTO users
					(username, email, password_hash, fiscal_month, fiscal_day)
					VALUES (?, ?, ?, ?, ?)
				");
				$stmt->execute([
					$username,
					$email,
					$passwordHash,
					$fiscalMonth,
					$fiscalDay
				]);
				header('Location: index.php?route=login');
				exit;
			} catch (PDOException $e) {
				$message = "登録に失敗しました。";
			}
		}
		require ROOT_PATH.'/views/auth/register.php';
	}
	private function loginUser(array $user): void{
		session_regenerate_id(true);
		$_SESSION['user'] = [
			'id' => (int)$user['id'],
			'username' => $user['username'],
			'email' => $user['email'],
			'fiscalMonth' => $user['fiscal_month'],
			'fiscalDay' => $user['fiscal_day']
		];
	}
}
