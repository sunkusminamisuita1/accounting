<?php
class ShopsValidator
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

    public function CommonVali(ShopsDto $Dto): int
    {
        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一
        $errFlg = 0;
        $Dto->isLocked   = '';

        $this->log("バリデーション開始。対象データ数: " . count($Dto->ShopAltTbl));
        //var_dump($Dto->ShopAltTbl);exit;

        foreach ($Dto->ShopAltTbl as $key => $Row) 
        {
            // 1. 店舗番号チェック
            $ShopCode = trim((string)$Row['shop_code']);
                //店番を正規表現で'000001'~'999999'でチェック
            $ShopNoPattern  =   '/^\d{6}$/';
            if ( ! preg_match($ShopNoPattern , $ShopCode) ) {
                $Dto->ShopAltTbl[$key]['errmsg'] = "shopsvali  店番は半角数字６桁で入力してください。";
                $errFlg++;
                continue;
            }
            if ($ShopCode === '000000') {
                $Dto->ShopAltTbl[$key]['errmsg'] = "shopsvali 000000は無効な店番です（000001以上）。";
                $errFlg++;
                continue;
            }

            if ( empty($Row['shop_name'])) {
                $Dto->ShopAltTbl[$key]['errmsg'] = "shopvali 店舗名は必須入力です。";
                $errFlg++;
                continue;// 
            }

            // 3. 必須・店舗名チェック
            $ShopName = trim(mb_convert_kana($Row['shop_name'] ?? '', "s", "UTF-8"));
            if ($ShopName === '') {
                $Dto->ShopAltTbl[$key]['errmsg'] = "店舗名は必須です。";
                $errFlg++;
                continue;
            }
            if (mb_strlen($ShopName, 'UTF-8') > 50) {
                $Dto->ShopAltTbl[$key]['errmsg'] = "店舗名は50文字以内で入力してください。";
                $errFlg++;
                continue;
            }

            // 4. 閉店フラグが立っているデータは新規登録できません。＃＃＃＃＃　　新規のみ　＃＃＃＃＃＃
            $Closed = $Row['closed'] ?? 0;

            // 5. 送信データ内での重複チェック
            if ($Closed) {
                if($Row['edittype'] === '追加'){
                    $Dto->ShopAltTbl[$key]['errmsg'] = "新規登録で削除は設定できません。";
                    $errFlg++;
                }
            }
                //店番重複チェック
                $sameRows = array_filter($Dto->ShopAltTbl, function($searchRow) use ($Row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['shop_code'] === $Row['shop_code'];
                });

                if (count($sameRows) >= 2) {
                    $Dto->ShopAltTbl[$key]['errmsg'] = "この店番はすでに登録（重複）されています。";
                    $errFlg++;
                }

                //店名重複チェック
                $sameRows = array_filter($Dto->ShopAltTbl, function($searchRow) use ($Row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['shop_name'] === $Row['shop_name'];
                });

                if (count($sameRows) >= 2) {
                    $Dto->ShopAltTbl[$key]['errmsg'] = "この店名はすでに登録（重複）されています。";
                    $errFlg++;
                }

                // 開店日が入力されている場合、YYYY-MM-DD形式であることをチェック
                if($Row['open_date'] ?? ''){
                    if ( ! $this->isValidDate($Row['open_date'] ?? '')) {
                        $Dto->ShopAltTbl[$key]['errmsg'] = "開店日はYYYY-MM-DD形式で入力してください。";
                        $errFlg++;
                    }
                }

                // 閉店日が入力されている場合、YYYY-MM-DD形式であることをチェック
                if($Row['closed_date'] ?? ''){
                    if ( ! $this->isValidDate($Row['closed_date'] ?? '')) {
                        $Dto->ShopAltTbl[$key]['errmsg'] = "閉店日はYYYY-MM-DD形式で入力してください。";
                        $errFlg++;
                    }
                }

                //閉店フラグと閉店日の整合性チェック
                    $Closed = (int)($Row['closed'] ?? 0);
                    $ClosedDate = trim((string)($Row['closed_date'] ?? ''));
                    if ( $Closed === 1 && $ClosedDate === '') {
                            $Dto->ShopAltTbl[$key]['errmsg'] = "閉店フラグが立っている場合、閉店日は必須です。";
                            $errFlg++;
                    }
                    if($Closed === 0 && $ClosedDate !== ''){
                            $Dto->ShopAltTbl[$key]['errmsg'] = "閉店日が入力されている場合、閉店フラグを立ててください。";
                            $errFlg++;
                    }
                            
        }

        if ($errFlg > 0) {
            $Dto->ErrData['shopValidator.commonVali'] = "登録エラーが存在します。エラーを修正してください。";
            $Dto->isLocked   = '';
        }else{
            $Dto->isLocked   = 'readonly';
        }

        $this->log("バリデーション終了。エラー数: " . $errFlg);

        return $errFlg;
    }

    function isValidDate($value): bool {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);
        $errors = DateTime::getLastErrors();

        if ($date === false) {
            return false;
        }

        $errors = is_array($errors) ? $errors : [];
        $warningCount = $errors['warning_count'] ?? 0;
        $errorCount = $errors['error_count'] ?? 0;

        return $warningCount === 0 && $errorCount === 0;
    }

}