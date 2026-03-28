<?php

$date = $_POST['voucher_date'];
$summary = $_POST['summary'] ?? '';

$debitAccount = (int)$_POST['debit_account_id'];
$creditAccount = (int)$_POST['credit_account_id'];

$debitAmount = (int)$_POST['debit_amount'];
$creditAmount = (int)$_POST['credit_amount'];

// 🔥 貸借一致チェック
if ($debitAmount !== $creditAmount) {
    die('貸借が一致していません');
}

try {

    $pdo->beginTransaction();

    // ① 伝票ヘッダ
    $stmt = $pdo->prepare("
        INSERT INTO journal_vouchers (voucher_date, summary, user_id)
        VALUES (:date, :summary, :user_id)
    ");

    $stmt->execute([
        ':date' => $date,
        ':summary' => $summary,
        ':user_id' => $_SESSION['user_id']
    ]);

    $voucherId = $pdo->lastInsertId();

    // ② 借方
    $stmt = $pdo->prepare("
        INSERT INTO journal_details
        (voucher_id, account_id, side, amount)
        VALUES (:voucher_id, :account_id, 'debit', :amount)
    ");

    $stmt->execute([
        ':voucher_id' => $voucherId,
        ':account_id' => $debitAccount,
        ':amount' => $debitAmount
    ]);

    // ③ 貸方
    $stmt = $pdo->prepare("
        INSERT INTO journal_details
        (voucher_id, account_id, side, amount)
        VALUES (:voucher_id, :account_id, 'credit', :amount)
    ");

    $stmt->execute([
        ':voucher_id' => $voucherId,
        ':account_id' => $creditAccount,
        ':amount' => $creditAmount
    ]);

    $pdo->commit();

    header('Location: ?route=home');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();
    die('登録に失敗しました');
}
