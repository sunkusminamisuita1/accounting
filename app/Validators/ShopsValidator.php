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

        $this->log("バリデーション開始。対象データ数: " . count($Dto->ShopsAltTbl));

        foreach ($Dto->ShopsAltTbl as $key => $row) 
        {
            $Dto->ShopsAltTbl[$key]['errmsg'] = "";

            // 1. 店舗番号チェック
            //echo "<br>店舗番号=" . var_dump($row['shop_code']) . "<br>";exit;
            //if ($row['type'], $Dto->ShopsType, true) {
            //    $Dto->ShopsAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";
            //    $errFlg++;
            //    continue;
            //}

            // 2. 削除済み状態の反映
            if (!empty($row['is_deleted'])) {
                $Dto->ShopsAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                $Dto->ShopsAltTbl[$key]['edittype'] = "削除";
            }

            // 3. 必須・文字数チェック
            $trimmedName = trim(mb_convert_kana($row['name'] ?? '', "s", "UTF-8"));
            if ($trimmedName === '') {
                $Dto->ShopsAltTbl[$key]['errmsg'] = "勘定科目名は必須です。";
                $errFlg++;
                continue;
            }
            if (mb_strlen($trimmedName, 'UTF-8') > 50) {
                $Dto->ShopsAltTbl[$key]['errmsg'] = "勘定科目名は50文字以内で入力してください。";
                $errFlg++;
                continue;
            }

            // 4. 削除フラグが立っているデータの書き換えチェック
            $isDeleted = $Dto->PostDt['ShopsUpdDt'][$key]['del'] ?? 0;
            if ($isDeleted) {
                $currentId = (int)$row['id'];
                $currentName = (string)$row['name'];
                $currentType = (string)$row['type'];

                foreach ($Dto->Shops as $orgRow) {
                    if ((int)$orgRow['id'] === $currentId) {
                        if ((string)$orgRow['name'] !== $currentName || (string)$orgRow['type'] !== $currentType) {
                            $Dto->ShopsAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                            $errFlg++;
                            break;
                        }
                    }
                }
            }

            // 5. 送信データ内での重複チェック
            if (!$isDeleted) {
                $sameRows = array_filter($Dto->ShopsAltTbl, function($searchRow) use ($row) {
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['name'] === $row['name'] && $searchRow['type'] === $row['type'];
                });

                if (count($sameRows) >= 2) {
                    $Dto->ShopsAltTbl[$key]['errmsg'] = "このデータはすでに登録（重複）されています。";
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