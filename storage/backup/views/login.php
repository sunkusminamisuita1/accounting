<?php
if (!defined('ROOT_PATH')) {
	exit('Direct access not allowed!');
}
$message = '';
$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = trim($_POST['email']);
	$password = $_POST['password'];
//		ログインidは username  or  email   一意性を保つため emailに決定　2026/02/13
//		上記は再検討要　　chatGPTからIDがbetter 担当者変更によるメールアドレス変更等 2026/2/17
	$stmt = $pdo->prepare("SELECT id, username, email, password_hash,
						fiscal_month, fiscal_day FROM users WHERE email = ?");
	$stmt->execute([$email]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($user && password_verify($password, $user['password_hash'])) {
		loginUser($user);
		header('Location:' . './index.php?route=home');
		exit;
	} else {
		$message = "メールアドレスまたはパスワードが間違っています。";
	}
}
require_once ROOT_PATH . '/views/login.html';
verifyCsrfToken($_POST['csrfTtoken'] ?? '');
//$token = generateCsrfToken();
function loginUser(array $user): void
{
	session_regenerate_id(true);
	$_SESSION['user']	=	[
						'id'			=>	(int)$user['id'],
						'user_id'		=>	$user['email'],
						'username'		=>	$user['username'],
						'fiscalMonth'	=>	$user['fiscal_month'],
						'fiscalDay'		=>	$user['fiscal_day']
					];
}
?>
