<?php
// app/DTO/VoucherDTO.php
class VoucherDTO
{
    public  $Date = '';
    public  $Summary = '';
    public array $Accounts = [];
    public array $DtoDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public array $InitDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public  $SearchType = '';
    public  $ListVcrNum = '';
    public array $ErrData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $VcrListResult =  []; //検索結果の配列
    public array $VcrSearchedData = [];
    public array $VcrUpdData = [];//vcrlistで修正対象行のデータを格納する配列
    //public array $InitVcrSearchedData = 
    //[0 => [
    //        'id' => '',
    //        'JdId' => '',
    //        'voucher_date' => '',
    //        'summary' => '',
    //        'account_id' => '',
    //        'name' => '',
    //        'type' => '',
    //        'side' => '',
    //        'amount' => '',
    //        'voucher_id' => '',
    //        'debit_total' => '',
    //        'credit_total' => ''
    //     ]
    //];///////////////
    public array $InitVcrSearchedData = [];
    public array $VcrListDatePeriod = []; //検索日付期間    [開始日付=>9999-99-99,終了日付=>9999-99-99]
    public array $AccountTbl = [];
    public array $VcrUpdRow = [];
    public $VcrUpdNo = 0;
    public $VcrDeleteNo = 0;//vcrlistで伝票削除行の行番号を格納する変数(voucher_id)


    public function __construct(array $Details)
    {
        $this->VcrListResult = $_SESSION['VoucherDetail'] ?? []; //検索結果の配列

        $this->Date      = $_POST['voucher_date'] ?? ''; //create.phpのVoucherDate
        $this->Summary   = $_POST['summary'] ?? '';      //create.phpのVoucherSummary
        $this->SearchType = $_POST['search_type'] ?? ''; //search.phpのSearchType
        $this->VcrUpdNo = $_SESSION['VcrUpdNo'] ?? 0; //vcrlistで修正対象行の伝票番号を格納する変数
        $this->DtoDetails       = $_POST['details'] ?? [$Details];
        if(empty($this->VcrSearchedData) && !empty($_SESSION['VcrSearchedData'])){
            $this->VcrSearchedData = $_SESSION['VcrSearchedData'];
        }
    }

    public function InitDetailsDto()
    {
        $this->DtoDetails   = $this->InitDetails; //初期値の明細行をDTOにセット
        $_SESSION['VoucherDetail'] = $this->InitDetails; //セッションに初期値の明細行を保存(Voucher.create)
//        $this->Summary      = '';
//        $this->account_id   = [];
//        $this->side         = [];
//        $this->amount       = [];
    }

    public function List()
    {
        $this->DtoDetails   = $this->InitDetails??[]; //初期値の明細行をDTOにセット
        $_SESSION['VoucherDetail'] = $_SESSION['VoucherDetail']  ?? $this->InitDetails; //セッションに初期値の明細行を保存(Voucher.create)
        $this->Date         = $_POST['ListVcrDate'] ?? '';
        $this->Summary      = $_POST['ListVcrSummary'] ?? '';
        $this->ListVcrNum   = $_POST['ListVcrNum'] ?? '';
        $this->VcrListDatePeriod   =   [ '検索開始日付' => $_POST['LstVcrSearchStartDate'] ?? '' , '検索終了日付' => $_POST['LstVcrSearchEndDate'] ?? '' ];

        if (!empty($_POST['SimpleSearch'])) {
            $this->SearchType = $_POST['SimpleSearch'] ?? '';
            $this->ListVcrNum = $_POST['ListVcrNum'] ?? '';
            $this->Date = $_POST['ListVcrDate'] ?? '';
            $this->Summary = $_POST['ListVcrSummary'] ?? '';
        }else {
            $this->SearchType = $_POST['CompoundSearch'] ?? '';
        }

    }

}
