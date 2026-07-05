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
                //break;
            }
            if(isset($Row['is_deleted']) && $Row['is_deleted'] ?? 0) {
                $Dto->AcctAltTbl[$key]['errmsg'] = "このデータは削除済みです。";
                $Dto->AcctAltTbl[$key]['edittype'] = "削除";
            }

            $trimmedName = trim(mb_convert_kana($Row['name'] ?? '', "s", "UTF-8"));//全角スペースを半角に変換してトリム
            if ($trimmedName === '') {
                $Dto->AcctAltTbl[$key]['errmsg'] = "勘定科目名は必須です。";
                $ErrFlg++;
                continue;
            }

            if (mb_strlen($trimmedName, 'UTF-8') > 50) {                          //文字数チェック　半角も全角も1文字としてカウントする
                $Dto->AcctAltTbl[$key]['errmsg'] = "勘定科目名は50文字以内で入力してください。";
                $ErrFlg++;
                continue;
            }

            $trimmedType = trim(mb_convert_kana($Row['type'] ?? '', "s", "UTF-8"));//全角スペースを半角に変換してトリム
            if ($trimmedType === '') {
                $Dto->AcctAltTbl[$key]['errmsg'] = "貸借種別は必須です。";
                $ErrFlg++;
                continue;
            }

            if (mb_strlen($trimmedType, 'UTF-8') > 50) {                          //文字数チェック　半角も全角も1文字としてカウントする
                $Dto->AcctAltTbl[$key]['errmsg'] = "貸借種別は50文字以内で入力してください。";
                $ErrFlg++;
                continue;
            }

            $IsDeleted      =   $Dto->PostDt['AcctUpdDt'][$key]['del'] ?? 0;
            if ($IsDeleted) {
                foreach($Dto->Accounts as $OrgKey => $OrgRow) {

                    $SameId         =   (int)$OrgRow['id']      === (int)$Row['id'];
                    $NameChanged    =   (string)$OrgRow['name'] !== (string)$Row['name'];
                    $TypeChanged    =   (string)$OrgRow['type'] !== (string)$Row['type'];

                    if ($SameId && ($NameChanged || $TypeChanged)) {
                        $Dto->AcctAltTbl[$key]['errmsg'] = "削除済みの勘定科目、種別は修正できません。";
                        $ErrFlg++ ;
                        //break;
                    }
                }
            }

            // 🌟 2. 自分自身も含めて「AcctAltTbl」全体から同じデータを検索する
            // ※ すでに削除チェック（del == 'On'）がついている行は比較対象から外すとより正確になります
            if (!$IsDeleted) {
            
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
                    //break;
                }
            }
        }
        if($ErrFlg > 0){
            $Dto->ErrData[0] = "登録エラーが存在します。エラーを修正してください。";
        }

        return $ErrFlg;
    }
}
