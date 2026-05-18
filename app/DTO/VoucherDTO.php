<?php
// app/DTO/VoucherDTO.php
class VoucherDTO
{
    public  $Date = '';
    public  $Summary = '';
    public array $account_id = [];
    public array $side = [];
    public array $amount = [];
    public array $DtoDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public array $InitDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public  $SearchType = '';
    public  $ListVcrNum = '';
    public array $ErrData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $VcrListResult = []; //検索結果の配列
    public array $VcrListDatePeriod = []; //検索日付期間    [開始日付=>9999-99-99,終了日付=>9999-99-99]
    public array $AccountTbl = [];
    public array $VcrUpdRow = [];
    public $VcrUpdNo = 0;
    public array $VcrSearchedData =[];

    public function __construct(array $Details)
    {
        $this->Date      = $_POST['voucher_date'] ?? ''; //create.phpのVoucherDate
        $this->Summary   = $_POST['summary'] ?? '';      //create.phpのVoucherSummary
        $this->SearchType = $_POST['search_type'] ?? ''; //search.phpのSearchType

        foreach ($Details as $idx => $row) {
            if (!isset($row['account_id'], $row['side'], $row['amount'])) {
                throw new InvalidArgumentException('Invalid detail data');
            }
            $this->account_id[$idx]  = $row['account_id'];
            $this->side[$idx]       = $row['side'];
            $this->amount[$idx]     = $row['amount'];
            $this->DtoDetails       = $_POST['details'] ?? [$Details];
        }
        //$_SESSION['VoucherDetail'] = $this->DtoDetails; //セッションに伝票一枚分を保存(Voucher.create)
    }

    public function InitDetailsDto()
    {
        $this->DtoDetails   = $this->InitDetails; //初期値の明細行をDTOにセット
        $_SESSION['VoucherDetail'] = $this->InitDetails; //セッションに初期値の明細行を保存(Voucher.create)
        $this->Summary      = '';
        $this->account_id   = [];
        $this->side         = [];
        $this->amount       = [];
    }

    public function List()
    {
        $this->DtoDetails   = $this->InitDetails??[]; //初期値の明細行をDTOにセット
        $_SESSION['VoucherDetail'] = $_SESSION['VoucherDetail']  ?? $this->InitDetails; //セッションに初期値の明細行を保存(Voucher.create)
        $this->Date         = $_POST['ListVcrDate'] ?? '';
        $this->Summary      = $_POST['ListVcrSummary'] ?? '';
        $this->ListVcrNum   = $_POST['ListVcrNum'] ?? '';
        $this->account_id   = [];
        $this->side         = [];
        $this->amount       = [];
        $this->VcrListDatePeriod   =   [ '検索開始日付' => $_POST['LstVcrSearchStartDate'] ?? '' , '検索終了日付' => $_POST['LstVcrSearchEndDate'] ?? '' ];

        if (!empty($_POST['SimpleSearch'])) {
            //echo "SimpleSearch selected"; // デバッグ用出力
            $this->SearchType = $_POST['SimpleSearch'] ?? '';
            $this->ListVcrNum = $_POST['ListVcrNum'] ?? '';
            $this->Date = $_POST['ListVcrDate'] ?? '';
            $this->Summary = $_POST['ListVcrSummary'] ?? '';
        }else {
            //echo "CompoundSearch selected"; // デバッグ用出力
            $this->SearchType = $_POST['CompoundSearch'] ?? '';
        }
        //var_dump($_POST); 
        //return();

    }

}
