<?php
class AccountsValidator
{

    private int $errno;
    public str $ErrMsg;
    public function __construct()    {
        $this->errno = 0;

    }

    public function AccountsVali(VoucherDTO $dto): void
    {
        foreach($AcctUpdDt as $Key=>$Row){
            if($AcctUpdDt[$key]['type'] =
                '収益' or
                '費用' or
                '資産' or
                '負債' or 
                '資本' or 
                '収益'){
                $Dto->AcctAltTbl[$key]['errmsg'] = "OK";
            }else{
                $Dto->AcctAltTbl[$key]['errmsg'] = "貸借種別は'収益'か'費用'か'資産'か'負債'か'資本'以外は入力できません。";

            }

        }
    }

}