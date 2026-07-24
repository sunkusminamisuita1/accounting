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

    public function ShopsVali(ShopsDTO $Dto): int
    {
        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一
        $errFlg = 0;

        $this->log("バリデーション開始。対象データ数: " . count($Dto->ShopAltTbl));
        //var_dump($Dto->ShopAltTbl);exit;

        foreach ($Dto->ShopAltTbl as $key => $Row) 
        {
            // 1. 店舗番号チェック
            $ShopCode = trim((string)$Row['shop_code']);
                //店番を正規表現で'000001'~'999999'でチェック
            $ShopNoPattern  =   '/^\d{6}$/';
            if ( ! preg_match($ShopNoPattern , $ShopCode) ) {
                $Dto->ShopsAltTbl[$key]['errmsg'] = "shopsvali  店番は半角数字６桁で入力してください。";
                $errFlg++;
                continue;
            }
            if ($ShopCode === '000000') {
                $Dto->ShopsAltTbl[$key]['errmsg'] = "shopsvali 000000は無効な店番です（000001以上）。";
                $errFlg++;
                continue;
            }
            //foreach ($Dto->ShopAltTbl as $key1 => $Row1){
            //    if($ShopCode = trim((string)$Row1['shop_code'])){
            //        $Dto->ShopAltTbl[$key]['errmsg'] = "shopsvali 同じ店番がすでに登録されています。";
            //        $errFlg++;
            //        continue;
            //    }

            //} 

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

            // 4. 閉店フラグが立っているデータの書き換えチェック
            $Closed = $Row['closed'] ?? 0;
            if ($Closed) {
                $currentId = (int)$Row['id'];
                $currentName = trim((string)$Row['name']);
                $currentType = trim((string)$Row['type']);

                foreach ($Dto->UserShops as $OrgKey => $OrgRow) {
                    if ((int)$OrgRow['id'] === $currentId) {
                        if (trim((string)$orgRow['name']) !== $currentName || trim((string)$OrgRow['type']) !== $currentType) {
                            $Dto->ShopAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                            $errFlg++;
                            break;
                        }
                    }
                }
            }

            // 5. 送信データ内での重複チェック
            if (!$Closed) 
            {
                //店番重複チェック
                $sameRows = array_filter($Dto->ShopAltTbl, function($searchRow) use ($Row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['shop_code'] === $Row['shop_code'];
                });

                if (count($sameRows) >= 2) {
                    $Dto->ShopsAltTbl[$key]['errmsg'] = "この店番はすでに登録（重複）されています。";
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
                    $Dto->ShopsAltTbl[$key]['errmsg'] = "この店名はすでに登録（重複）されています。";
                    $errFlg++;
                }             
            }
        }

        if ($errFlg > 0) {
            $Dto->ErrData[0] = "登録エラーが存在します。エラーを修正してください。";
        }

        $this->log("バリデーション終了。エラー数: " . $errFlg);

        return $errFlg;
    }    
}