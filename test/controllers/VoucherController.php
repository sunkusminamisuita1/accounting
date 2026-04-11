<?php
class VoucherController {
    public function create() {
        // 伝票作成のメイン処理
        $pdo = getPDO();
        $accounts = $pdo->query("SELECT id, name FROM accounts ORDER BY id")->fetchAll();
        array_unshift($accounts, ['id' => 9999, 'name' => '----------']);

        // Viewに$accountsを渡す
        $GLOBALS['accounts'] = $accounts;

        if (!isset($_SESSION['voucherRows'])) {
            $_SESSION['voucherRows'] = [];
        }
        if (!isset($_SESSION['slipNum'])) {
            $_SESSION['slipNum'] = 0;
        }
        if (!isset($_SESSION['debitAmountTotal'])) {
            $_SESSION['debitAmountTotal'] = 0;
        }
        if (!isset($_SESSION['creditAmountTotal'])) {
            $_SESSION['creditAmountTotal'] = 0;
        }

        $diff = $_SESSION['debitAmountTotal'] - $_SESSION['creditAmountTotal'];
        $is_balanced = ($diff === 0 && !empty($_SESSION['voucherRows']));

        // POST処理
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            // ここにPOST処理を追加（例: 保存など）
        }

        // View表示
        require_once ROOT_PATH . '/test/view/vouchers/VoucherView.php';
    }

    public function add() {
        // 勘定科目リストを取得してViewに渡す
        $pdo = getPDO();
        $accounts = $pdo->query("SELECT id, name FROM accounts ORDER BY id")->fetchAll();
        array_unshift($accounts, ['id' => 9999, 'name' => '----------']);
        $GLOBALS['accounts'] = $accounts;

        // 明細追加の処理
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            if (isset($_POST['add'])) {
                $voucherDate = $_POST['voucherDate'] ?? date("Y-m-d");
                $side = $_POST['side'] ?? "";
                $accountId = $_POST['accountId'] ?? null;
                $amount = $_POST['amount'] ?? "";
                $summary = $_POST['summary'] ?? "";

                // DTO作成
                $dto = new VAddDTO($voucherDate, $side, $accountId, $amount, $summary);

                // Validator
                $validator = new VoucherValidate();
                $errors = $validator->validateAdd($dto);
                if (!empty($errors)) {
                    // エラー処理
                    $_SESSION['errors'] = $errors;
                    header('Location: index.php?route=voucher.add');
                    exit;
                }

                // Service
                $service = new VoucherService();
                $service->addVoucherRow($dto);

                header('Location: index.php?route=voucher.create');
                exit;
            }
        }

        // View表示
        require_once ROOT_PATH . '/test/view/vouchers/VoucherView.php';
    }

    public function delete() {
        // 削除処理
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
            if (isset($_POST['clear'])) {
                // 全削除
                $dto = new VDeleteDTO('clear');
                $validator = new VoucherValidate();
                $errors = $validator->validateDelete($dto);
                if (!empty($errors)) {
                    $_SESSION['errors'] = $errors;
                    header('Location: index.php?route=voucher.create');
                    exit;
                }

                $service = new VoucherService();
                $service->executeDelete($dto);
                header('Location: index.php?route=voucher.create');
                exit;
            }
            if (isset($_POST['alt'])) {
                // 削除・修正処理
                $deleteKeys = $_POST['deleteKeys'] ?? [];
                $updateKey = $_POST['update_key'] ?? null;

                $dto = new VDeleteDTO('alt', $deleteKeys, $updateKey);
                $validator = new VoucherValidate();
                $errors = $validator->validateDelete($dto);
                if (!empty($errors)) {
                    $_SESSION['errors'] = $errors;
                    header('Location: index.php?route=voucher.create');
                    exit;
                }

                $service = new VoucherService();
                $service->executeDelete($dto);
                header('Location: index.php?route=voucher.create');
                exit;
            }
        }

        // View表示
        require_once ROOT_PATH . '/test/view/vouchers/VoucherView.php';
    }
}
?>