<?php
class AccountsValidator
{

    private int $errno;
    public string $ErrMsg;
    public function __construct()    {
        $this->errno = 0;

    }

    public function AccountsVali(AccuntsDTO $Dto): void
    {

        foreach ($Dto->AcctAltTbl as $key => $Row) {
            // 配列の中に現在のタイプが含まれているかチェックする
            if (in_array($Row['type'], $Dto->AccountsType, true)) {
                $Dto->AcctAltTbl[$key]['errmsg'] = "OK";
            } else {
                $Dto->AcctAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";
            }
            XXX
        }

    }

}