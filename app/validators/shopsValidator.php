<?php
class shopsValidator
{
    // ★デバッグ用: 開発中に詳細なログを出したい場合は true にします
    private bool $debugMode = false; 

    public function __construct(bool $debugMode = false) {
        $this->debugMode = $debugMode;
    }

    /**
     * デバッグログ出力用メソッド
     */
    private function log(string $message, mixed $data = null): void {
        if ($this->debugMode) {
            echo "[DEBUG] " . $message . "\n";
            if ($data !== null) {
                print_r($data);
            }
        }
    }

    public function newRegister(shopsDto $dto): int
    {
        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一
        $errFlg = 0;
        $dto->isLocked   = '';

        $this->log("バリデーション開始。対象データ数: " . count($dto->shopAltTbl));
        //var_dump($dto->shopAltTbl);exit;

        // 1. 店舗番号チェック
        $shopCode = trim((string)$dto->postDt['newShopCode']);
            //店番を正規表現で'000001'~'999999'でチェック
        $shopNoPattern  =   '/^\d{6}$/';
        if ( ! preg_match($shopNoPattern , $shopCode) ) {
            $dto->newErrMsg = "shopsvali  店番は半角数字６桁で入力してください。";
            $errFlg++;
        }
        if ($shopCode === '000000') {
            $dto->newErrMsg = "shopsvali 000000は無効な店番です（000001以上）。";
            $errFlg++;
        }

        // 3. 必須・店舗名チェック
        $shopName = trim(mb_convert_kana($row['shop_name'] ?? '', "s", "UTF-8"));
        if ($shopName === '') {
            $dto->newErrMsg = "店舗名は必須です。";
            $errFlg++;
        }
        if (mb_strlen($shopName, 'UTF-8') > 50) {
            $dto->newErrMsg = "店舗名は50文字以内で入力してください。";
            $errFlg++;
        }

        if ($errFlg > 0) {
            $dto->errData['shopValidator.commonVali'] = "登録エラーが存在します。エラーを修正してください。";
            $dto->isLocked   = '';
        }else{
            $dto->isLocked   = 'readonly';
        }

        $this->log("バリデーション終了。エラー数: " . $errFlg);

        return $errFlg;


    }

    public function commonVali(shopsDto $dto): int
    {
        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一
        $errFlg = 0;
        $dto->isLocked   = '';

        $this->log("バリデーション開始。対象データ数: " . count($dto->shopAltTbl));
        //var_dump($dto->shopAltTbl);exit;

        foreach ($dto->shopAltTbl as $key => $row) 
        {
            // 1. 店舗番号チェック
            $shopCode = trim((string)$row['shop_code']);
                //店番を正規表現で'000001'~'999999'でチェック
            $shopNoPattern  =   '/^\d{6}$/';
            if ( ! preg_match($shopNoPattern , $shopCode) ) {
                $dto->shopAltTbl[$key]['errmsg'] = "shopsvali  店番は半角数字６桁で入力してください。";
                $errFlg++;
                continue;
            }
            if ($shopCode === '000000') {
                $dto->shopAltTbl[$key]['errmsg'] = "shopsvali 000000は無効な店番です（000001以上）。";
                $errFlg++;
                continue;
            }

            if ( empty($row['shop_name'])) {
                $dto->shopAltTbl[$key]['errmsg'] = "shopvali 店舗名は必須入力です。";
                $errFlg++;
                continue;// 
            }

            // 3. 必須・店舗名チェック
            $shopName = trim(mb_convert_kana($row['shop_name'] ?? '', "s", "UTF-8"));
            if ($shopName === '') {
                $dto->shopAltTbl[$key]['errmsg'] = "店舗名は必須です。";
                $errFlg++;
                continue;
            }
            if (mb_strlen($shopName, 'UTF-8') > 50) {
                $dto->shopAltTbl[$key]['errmsg'] = "店舗名は50文字以内で入力してください。";
                $errFlg++;
                continue;
            }

            // 4. 閉店フラグが立っているデータは新規登録できません。＃＃＃＃＃　　新規のみ　＃＃＃＃＃＃
            $closed = $row['closed'] ?? 0;

            // 5. 送信データ内での重複チェック
            if ($closed) {
                if($row['edittype'] === '追加'){
                    $dto->shopAltTbl[$key]['errmsg'] = "新規登録で削除は設定できません。";
                    $errFlg++;
                }
            }
                //店番重複チェック
                $sameRows = array_filter($dto->shopAltTbl, function($searchRow) use ($row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['shop_code'] === $row['shop_code'];
                });

                if (count($sameRows) >= 2) {
                    $dto->shopAltTbl[$key]['errmsg'] = "この店番はすでに登録（重複）されています。";
                    $errFlg++;
                }

                //店名重複チェック
                $sameRows = array_filter($dto->shopAltTbl, function($searchRow) use ($row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['shop_name'] === $row['shop_name'];
                });

                if (count($sameRows) >= 2) {
                    $dto->shopAltTbl[$key]['errmsg'] = "この店名はすでに登録（重複）されています。";
                    $errFlg++;
                }

                // 開店日が入力されている場合、YYYY-MM-DD形式であることをチェック
                if($row['open_date'] ?? ''){
                    if ( ! $this->isValidDate($row['open_date'] ?? '')) {
                        $dto->shopAltTbl[$key]['errmsg'] = "開店日はYYYY-MM-DD形式で入力してください。";
                        $errFlg++;
                    }
                }

                // 閉店日が入力されている場合、YYYY-MM-DD形式であることをチェック
                if($row['closed_date'] ?? ''){
                    if ( ! $this->isValidDate($row['closed_date'] ?? '')) {
                        $dto->shopAltTbl[$key]['errmsg'] = "閉店日はYYYY-MM-DD形式で入力してください。";
                        $errFlg++;
                    }
                }

                //閉店フラグと閉店日の整合性チェック
                    $closed = (int)($row['closed'] ?? 0);
                    $closedDate = trim((string)($row['closed_date'] ?? ''));
                    if ( $closed === 1 && $closedDate === '') {
                            $dto->shopAltTbl[$key]['errmsg'] = "閉店フラグが立っている場合、閉店日は必須です。";
                            $errFlg++;
                    }
                    if($closed === 0 && $closedDate !== ''){
                            $dto->shopAltTbl[$key]['errmsg'] = "閉店日が入力されている場合、閉店フラグを立ててください。";
                            $errFlg++;
                    }
                            
        }

        if ($errFlg > 0) {
            $dto->errData['shopValidator.commonVali'] = "登録エラーが存在します。エラーを修正してください。";
            $dto->isLocked   = '';
        }else{
            $dto->isLocked   = 'readonly';
        }

        $this->log("バリデーション終了。エラー数: " . $errFlg);

        return $errFlg;
    }

    function isValidDate($value): bool {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $date = dateTime::createFromFormat('!Y-m-d', $value);
        $errors = dateTime::getLastErrors();

        if ($date === false) {
            return false;
        }

        $errors = is_array($errors) ? $errors : [];
        $warningCount = $errors['warning_count'] ?? 0;
        $errorCount = $errors['error_count'] ?? 0;

        return $warningCount === 0 && $errorCount === 0;
    }

}