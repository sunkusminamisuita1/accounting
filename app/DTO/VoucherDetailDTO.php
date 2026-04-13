<?php
// app/DTO/VoucherDetailDTO.php
class VoucherDetailDTO
{
    public int $accountId;
    public string $side;
    public int $amount;

    public function __construct($accountId, $side, $amount)
    {
        $this->accountId = $accountId;
        $this->side = $side;
        $this->amount = $amount;
    }
}




//class VoucherDetailDTO
//{
//    public int $accountId;
//    public string $side;
//    public int $amount;
//    public int $lineNo;
//}
?>
