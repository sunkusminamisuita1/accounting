<?php
class AccountsValidator
{

    private int $errno;
    public string $ErrMsg;
    public function __construct()    {
        $this->errno = 0;

    }

    public function AccountsVali(AccountsDTO $Dto): int
    {
        $ErrFlg = 0;
        foreach ($Dto->AcctAltTbl as $key => $Row) 
        {
            // 配列の中に現在のタイプが含まれているかチェックする
            if (in_array($Row['type'], $Dto->AccountsType, true)) {
                $Dto->AcctAltTbl[$key]['errmsg'] = "";
            } else {
                $Dto->AcctAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";
                $ErrFlg++ ;
                break;
            }
            if(isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                $Dto->AcctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                $Dto->AcctAltTbl[$key]['edittype'] = "削除";
            }
            if (empty($Row['name'])) {
                $Dto->AcctAltTbl[$key]['errmsg'] = "勘定科目名は必須です。";
                $ErrFlg++ ;
                break;
            }

            if($Dto->PostDt['AcctUpdDt'][$key]['del'] ?? ''  && 
              (
               $Dto->PostDt['AcctUpdDt'][$key]['name'] !== $Row['name']       ||
               $Dto->PostDt['AcctUpdDt'][$key]['type'] !== $Row['type'])
              )
            {
                    echo "<br>【削除済みデータの修正】";
                    echo "<br>入力name: " . $Dto->PostDt['AcctUpdDt'][$key]['name'];
                    echo "<br>現在name: " . $Row['name'];
                    echo "<br>入力type: " . $Dto->PostDt['AcctUpdDt'][$key]['type'];
                    echo "<br>現在type: " . $Row['type'];

                    $Dto->AcctAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                    $ErrFlg++ ;
                    break;
            }
            // 🌟 2. 自分自身も含めて「AcctAltTbl」全体から同じデータを検索する
            // ※ すでに削除チェック（del == 'On'）がついている行は比較対象から外すとより正確になります
            if (($Row['edittype'] ?? '') !== '削除') {
            
                $sameRows = array_filter($Dto->AcctAltTbl, function($searchRow) use ($Row) {
                    // 削除予定の行はカウント対象外にする
                    if (($searchRow['edittype'] ?? '') === '削除') {
                        // $Dto->ErrData[0] = "削除予定の行は重複チェック対象外です。 ";
                        return false;
                    }
                    // 「名前」と「種別」が一致する行を生き残らせる
                    return $searchRow['name'] === $Row['name'] && $searchRow['type'] === $Row['type'];
                });

                // 🌟 3. count() で生存した件数を取得し、2件以上なら重複エラー！
                if (count($sameRows) >= 2) {
                    $Dto->AcctAltTbl[$key]['errmsg'] = "このデータはすでに登録（重複）されています。";
                    $ErrFlg++ ;
                    break;
                }
            }
        }
        if($ErrFlg > 0){
            $Dto->ErrData[0] = "登録エラーが存在します。エラーを修正してください。";
        }

        




// if($ErrFlg > 0){
//     echo "<br>【エラーフラグ】ErrFlg=" . $ErrFlg;
//     echo "<br>【ErrData設定前】";
//     var_dump($Dto->ErrData);
//     $Dto->ErrData['AccountsVali'] = "登録エラーが存在します。エラーを修正してください。";
//     echo "<br>【ErrData設定後】";
//     var_dump($Dto->ErrData);
// } else {
//     echo "<br>【エラーなし】ErrFlg=" . $ErrFlg;
// }





        return $ErrFlg;
    }
}