<?php
class AccountsValidator
{

    private int $errno;
    public function __construct()    {
        $this->errno = 0;
    }

    public function Create1(VoucherDTO $dto): void
    {

    }

    public function List1(VoucherDTO $dto): void
    {

    }


//貸方、借方バランスチェック 引数の配列フォーマットは連想キー'amount','side'が含まれていたらどんなフォーマットでもOK
    public function ChkTotalBalance1($Dto, $ChkTbl){

    }
}