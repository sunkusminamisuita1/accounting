<?php
require_once ROOT_PATH . '/lib/helpers.php';
        // echo "post件数/" . count($this->dto->postDt['acctUpdDt']);
        // echo "alttbl件数/" . count($this->dto->accounts);exit;
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
class accountsValidator
{
    // ★クリーンアップ: 未使用だった $errno と $errMsg を削除しました。
    // ★デバッグ用: 開発中に詳細なログを出したい場合は true にします
    private bool $debugMode = false; 
    private $dto;
    private $pdo;
    private $repo;

    public function __construct( $dto, $pdo, $repo, bool $debugMode = true) {
        $this->debugMode = $debugMode ?? false;
        $this->dto          =   $dto;
        $this->repo         =   $repo;
        $this->pdo          =   $pdo;
    }

    public function accountsValiDlt() {
        $errFlg = 0;
        //削除ボタンが押されたとき行追加していた場合、エラー　削除ボタンは　削除単体で使用する。
        if( count($this->dto->postDt['acctUpdDt'])   >   count($this->dto->accounts)){
            $this->dto->acctAltTbl[0]['errmsg'] = "削除ボタンは削除単体で使用してください。";
            $errFlg++;
            // echo "111";
            // echo "<pre>postDt['acctUpdDt'][]<br>";
            // var_dump($this->dto->postDt['acctUpdDt']);
            // echo "<br>acctAltTbl[]<br>";
            // var_dump($this->dto->acctAltTbl);
            // echo "</pre>";
            return $errFlg;
        }
        $allCount = count($this->dto->postDt['acctUpdDt']);
        $chkCount = 0;
        foreach($this->dto->postDt['acctUpdDt'] as $postKey => $postRow){
            foreach($this->dto->accounts as $acctKey => $acctRow){
                if( (int)$postRow['id']          === (int)$acctRow['id'] ){
                    $chkCount++;
                    if( ( $postRow['name']             === $acctRow['name']           )         &&
                        ( $postRow['type']             === $acctRow['type']           )         &&
                        ( (int)$postRow['sort_order']  === (int)$acctRow['sort_order'])   )     {    
                            $this->dto->acctAltTbl[$acctKey]['errmsg'] = "";                     
                            //echo "222";
                            break;
                    }else{
                            $errFlg++;
                            //echo "333";
                            $this->dto->acctAltTbl[$acctKey]['errmsg'] = "削除ボタンは削除単体(更新不可)で使用してください。";
                            break;
                    }
                }
            }
        }
        if($chkCount  !== $allCount){
            $this->dto->acctAltTbl[0]['errmsg'] = "システムエラー　データ件数と元データ件数不一致";
            $errFlg++;
            //echo "444";
            return $errFlg;
        }
        if($errFlg > 0){echo "555";return $errFlg;}
        // echo "<pre>postDt['acctUpdDt'][]<br>";
        // var_dump($this->dto->postDt['acctUpdDt']);
        // echo "<br>acctAltTbl[]<br>";
        // var_dump($this->dto->accounts);
        //echo "</pre>";
    }

    public function accountsVali(): int
    {
        $errFlg = 0;
        //ソートオーダー重複エラー
        foreach ($this->dto->acctAltTbl as $key => $row) {
            $this->dto->acctAltTbl[$key]['errmsg'] = "";
            $sortOrderAltTbl[$row['sort_order']][] = $key;
        }
        $duplicateSortOrderGroups = array_filter($sortOrderAltTbl, function($row) {
            return count($row) > 1;
        });
        foreach ($duplicateSortOrderGroups as $sortOrder => $keys) {
            foreach ($keys as $key) {
                $this->dto->acctAltTbl[$key]['errmsg'] = 
                            "表示順序が重複しています。表示順序はユニークである必要があります。";
                $errFlg++;
            }
        }

        // パスカルケース（大文字始まり）だったローカル変数を、PHPで一般的なキャメルケース（小文字始まり）に統一


        debug_log("バリデーション開始。対象データ数: " . count($this->dto->acctAltTbl));
        $sortOrderCnt = 0;
        foreach ($this->dto->acctAltTbl as $key => $row){
            //$this->dto->acctAltTbl[$key]['errmsg'] = "";

            // 1. 貸借種別チェック
            if (!in_array($row['type'], $this->dto->accountsType, true)) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";
                $errFlg++;
                continue;
            }

            // 2. 削除済み状態の反映
            if (!empty($row['is_deleted'])) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
            }
            // 3. name 必須・文字数チェック
            $trimmedName = trim(mb_convert_kana($row['name'] ?? '', "s", "UTF-8"));
            if ($trimmedName === '') {
                $this->dto->acctAltTbl[$key]['errmsg'] = "勘定科目名は必須です。";
                $errFlg++;
                continue;
            }
            if (mb_strlen($trimmedName, 'UTF-8') > 50) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "勘定科目名は50文字以内で入力してください。";
                $errFlg++;
                continue;
            }

            // 3. sort_order 必須・文字数チェック
            $trimmedSortOrder = trim($row['sort_order'] ?? '');

            if ($trimmedSortOrder === '') {
                $this->dto->acctAltTbl[$key]['errmsg'] = "ソート順は必須です。";
                $errFlg++;
                continue;
            }
            if (!is_numeric($trimmedSortOrder)) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "ソート順は4桁数字で入力してください。";
                $errFlg++;
                continue;
            }

            if (!ctype_digit($trimmedSortOrder)) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "表示順序は数字で入力してください。";
                $errFlg++;
                continue;
            }

            $trimmedSortOrder = (int)$trimmedSortOrder;

            if ($trimmedSortOrder > 9999 || $trimmedSortOrder < 0) {
                $this->dto->acctAltTbl[$key]['errmsg'] = "ソート順は0から9999の範囲で入力してください。";
                $errFlg++;
                continue;
            }
            // 4. 削除フラグが立っているデータの書き換えチェック
            $isDeleted = $this->dto->postDt['acctUpdDt'][$key]['del'] ?? '0';
            if ($isDeleted) {
                $currentId = (int)$row['id'];
                $currentName = (string)$row['name'];
                $currentSortOrder = (string)$row['sort_order'];
                $currentType = (string)$row['type'];
                foreach ($this->dto->accounts as $orgRow) {
                    if ((int)$orgRow['id'] === $currentId) {
                        if ( ( (string)$orgRow['name']    !== $currentName            )         || 
                           ( (string)$orgRow['type']      !== $currentType            )         || 
                           ( (int)$orgRow['sort_order']   !== (int)$currentSortOrder) )
                        {
                            $this->dto->acctAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                            $errFlg++;
                            return $errFlg;
                        }
                    }
                    // if ((int)$orgRow['sort_order']   !== (int)$currentSortOrder) {
                    //     continue;
                    // }
                    // $sortOrderCnt ++;
                    // if($sortOrderCnt >= 2){
                    //     echo "orgsortkey:{$orgRow['sort_order']}  currentSortKey:{$currentSortOrder}  ";
                    //     $this->dto->acctAltTbl[$key]['errmsg'] = "表示順序が重複しています。表示順序はユニークである必要があります。";
                    //     $errFlg++;
                    //     return $errFlg;                       
                    // }
                }
            }

            // 4.5 勘定科目テーブル行削除の場合、仕訳帳に使用されているか確認し、使用されている場合はエラー処理
            if ($isDeleted) {

                $journalDetails = $this->repo->getJournalDtails($this->dto->shopCode, $this->dto->id);
                $filteredJournalDetails = array_filter($journalDetails, function($journalRow) use ($row){
                    return  ( $journalRow['shop_code'] == $this->dto->shopCode )  &&
                            ( $journalRow['user_id'] == $this->dto->id )          &&
                            ( $journalRow['account_id'] == $row['id'] );
                });

                if (!empty($filteredJournalDetails)) {
                    echo "<script>alert('削除できません。仕訳帳に使用されている勘定科目は削除できません。');</script>";
                    $this->dto->acctAltTbl[$key]['errmsg'] = "この勘定科目は仕訳帳に使用されているため、削除できません。";
                    $errFlg++;
                }
            }

            // 5. 送信データ内での重複チェック
            if (!$isDeleted) {
                $sameRows = array_filter($this->dto->acctAltTbl, function($searchRow) use ($row) {
                    if (($searchRow['editType'] ?? '') === '削除') {
                        return false;
                    }
                    return $searchRow['name'] === $row['name'] && $searchRow['type'] === $row['type'];
                });

                if (count($sameRows) >= 2) {
                    $this->dto->acctAltTbl[$key]['errmsg'] = "このデータはすでに登録（重複）されています。";
                    $errFlg++;
                    }
            }


                // $accountCodeMinMax = array_filter($this->dto->accountsTypeTbl, function($searchRow) use ($row) {
                //     if (($searchRow['type'] ?? '') === $row['type']) {
                //         return true;
                //     }
                //     return false;
                // });
                // if( ((int)$row['sort_order'] <  (int)$accountCodeMinMax['min_code'])      ||
                //     ((int)$row['sort_order'] >  (int)$accountCodeMinMax['max_code']) ) {
                //     $this->dto->acctAltTbl[$key]['errmsg'] = 
                //         "表示順序は{$accountCodeMinMax['min_code']}から{$accountCodeMinMax['max_code']}の間で指定してください。";
                //     $errFlg++;
                // }
                // var_dump($accountCodeMinMax);exit;

                //表示順序範囲チェック
                foreach ($this->dto->accountsTypeTbl as $searchRow) {
                    if (($searchRow['type'] ?? '') === $row['type']) {
                        $accountCodeMinMax = $searchRow;
                        break;
                    }
                }
                if( ((int)$row['sort_order'] <  (int)$accountCodeMinMax['min_code'])      ||
                    ((int)$row['sort_order'] >  (int)$accountCodeMinMax['max_code']) ) {
                    $this->dto->acctAltTbl[$key]['errmsg'] = 
                        "表示順序は{$accountCodeMinMax['min_code']}から{$accountCodeMinMax['max_code']}の間で指定してください。";
                    $errFlg++;
                }
        }
        //echo "<pre>11";var_dump($this->dto->acctAltTbl);echo "</pre>";

        if ($errFlg > 0) {
            $this->dto->errData[0] = "登録エラーが存在します。エラーを修正してください。";
        }
        debug_log("バリデーション終了。エラー数: " . $errFlg);
        //$errFlg = (int)1;
                

        return $errFlg;
    }
}