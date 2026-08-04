<?php
class accountsValidator
{
    // ★クリーンアップ: 未使用だった $errno と $errMsg を削除しました。
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

    public function accountsVali(accountsDto $dto): int
    {
        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一
        $errFlg = 0;

        $this->log("バリデーション開始。対象データ数: " . count($dto->acctAltTbl));

        foreach ($dto->acctAltTbl as $key => $row) 
        {
            $dto->acctAltTbl[$key]['errmsg'] = "";

            // 1. 貸借種別チェック
            if (!in_array($row['type'], $dto->accountsType, true)) {
                $dto->acctAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";
                $errFlg++;
                continue;
            }

            // 2. 削除済み状態の反映
            if (!empty($row['is_deleted'])) {
                $dto->acctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                $dto->acctAltTbl[$key]['editType'] = "削除";
            }

            // 3. 必須・文字数チェック
            $trimmedName = trim(mb_convert_kana($row['name'] ?? '', "s", "UTF-8"));
            if ($trimmedName === '') {
                $dto->acctAltTbl[$key]['errmsg'] = "勘定科目名は必須です。";
                $errFlg++;
                continue;
            }
            if (mb_strlen($trimmedName, 'UTF-8') > 50) {
                $dto->acctAltTbl[$key]['errmsg'] = "勘定科目名は50文字以内で入力してください。";
                $errFlg++;
                continue;
            }

            // 4. 削除フラグが立っているデータの書き換えチェック
            $isDeleted = $dto->postDt['AcctUpdDt'][$key]['del'] ?? 0;
            if ($isDeleted) {
                $currentId = (int)$row['id'];
                $currentName = (string)$row['name'];
                $currentType = (string)$row['type'];

                foreach ($dto->accounts as $orgRow) {
                    if ((int)$orgRow['id'] === $currentId) {
                        if ((string)$orgRow['name'] !== $currentName || (string)$orgRow['type'] !== $currentType) {
                            $dto->acctAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                            $errFlg++;
                            break;
                        }
                    }
                }
            }

            // 5. 送信データ内での重複チェック
            if (!$isDeleted) {
                $sameRows = array_filter($dto->acctAltTbl, function($searchRow) use ($row) {
                    if (($searchRow['editType'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['name'] === $row['name'] && $searchRow['type'] === $row['type'];
                });

                if (count($sameRows) >= 2) {
                    $dto->acctAltTbl[$key]['errmsg'] = "このデータはすでに登録（重複）されています。";
                    $errFlg++;
                }
            }
        }

        if ($errFlg > 0) {
            $dto->errData[0] = "登録エラーが存在します。エラーを修正してください。";
        }

        $this->log("バリデーション終了。エラー数: " . $errFlg);

        return $errFlg;
    }
}