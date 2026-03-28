<?php $token = generateCsrfToken(); ?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>ユーザー登録</title>
</head>
<body>

<h1>ユーザー登録</h1>

	<form method="post" action="index.php?route=register">

		<input type="hidden" name="csrfToken" value="<?= h($token) ?>">

			<p>ユーザー名
				<input type="text" name="username" required>
			</p>

			<p>	メールアドレス
				<input type="email" name="email" required>
			</p>

			<p>パスワード
				<input type="password" name="password" required>
			</p>

			<p>決算月
				<input type="number" name="fiscal_month" min="1" max="12" required>
			</p>

			<p>決算日
				<input type="number" name="fiscal_day" min="1" max="31" required>
			</p>

			<button type="submit">登録</button>

	</form>

<?php if(!empty($message)): ?>
<p style="color:red"><?= h($message) ?></p>
<?php endif; ?>

<p>
<a href="index.php?route=login">ログイン画面へ</a>
</p>

</body>
</html>

