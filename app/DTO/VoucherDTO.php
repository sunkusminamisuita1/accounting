<?php
// app/DTO/VoucherDTO.php
class VoucherDTO
{
    public  $Date;
    public  $Summary;
    public array $accountId = [];
    public array $side = [];
    public array $amount = [];
    public array $DtoDetails = []; //明細行の配列

    public function __construct(array $Details)
    {
        $this->Date      = $_POST['voucher_date'] ?? ''; //create.phpのVoucherDate
        $this->Summary   = $_POST['summary'] ?? '';      //create.phpのVoucherSummary
        foreach ($Details as $idx => $row) {
            if (!isset($row['account_id'], $row['side'], $row['amount'])) {
                throw new InvalidArgumentException('Invalid detail data');
            }
            $this->accountId[$idx]  = $row['account_id'];
            $this->side[$idx]       = $row['side'];
            $this->amount[$idx]     = $row['amount'];
            $this->DtoDetails       = $Details;
        }
        $_SESSION['VoucherDetail'] = $this->DtoDetails ?? ''; //セッションに伝票一枚分を保存(Voucher.create)
    }
}
