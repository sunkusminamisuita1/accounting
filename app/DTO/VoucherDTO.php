<?php
// app/DTO/VoucherDTO.php
class VoucherDTO
{       //##############   DTOでは$_SESSIONからデータを取得してプロパティにセットする。その後$_SESSIONは初期化する。 ##############
    public  $Date = '';
    public  $Summary = '';
    public array $Accounts = [];
    public array $DtoDetails = [0 => ['account_id' => '', 'jd_summary' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    //public array $VcrDetailAddRow = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public array $InitDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public  $SearchType = '';
    public  $ListVcrNum = '';
    public array $ErrData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $VcrListResult =  []; //検索結果の配列
    public array $VcrSearchedData = [];
    public array $VcrUpdData = [];//vcrlistで修正対象行のデータを格納する配列
    public array $InitVcrSearchedData = [];
    public array $VcrListDatePeriod = []; //検索日付期間    [開始日付=>9999-99-99,終了日付=>9999-99-99]
    public array $AccountTbl = [];
    public array $VcrUpdRow = [];
    public $VcrUpdNo = 0;
    public $VcrDeleteNo = 0;//vcrlistで伝票削除行の行番号を格納する変数(voucher_id)
    public array $VcrInputData = []; //vcrlistで検索条件を格納する配列


    public function __construct(array $Details)
    {
        $this->VcrListResult =  []; //検索結果の配列
        $this->Date      =  ''; //create.phpのVoucherDate
        $this->Summary   =  '';      //create.phpのVoucherSummary
        $this->SearchType =  ''; //search.phpのSearchType
        $this->VcrUpdNo =  0; //vcrlistで修正対象行の伝票番号を格納する変数
        $this->DtoDetails      =  [];
        $this->VcrSearchedData = [];
//###########         journal_vouchersのカラム         ##############
//| id         | int(11)                | NO   | PRI | NULL    | auto_increment |
//| voucher_date | date                   | NO   |     | NULL    |                |
//| summary     | varchar(255)           | NO   |     | NULL    |
//| user_id     | int(11)                | NO   | MUL | NULL    |                |
//| created_at  | datetime               | NO   |     | NULL    |
    

//###########         journal_detailsのカラム         ##############
//| id         | int(11)                | NO   | PRI | NULL    | auto_increment |
//| voucher_id | int(11)                | NO   | MUL | NULL    |                |
//| line_no    | int(11)                | YES  |     | NULL    |                |
//| account_id | int(11)                | NO   |     | NULL    |                |
//| side       | enum('debit','credit') | NO   |     | NULL    |                |
//| amount     | int(11)                | NO   |     | NULL    |                |

//###########         配列VcrListResultのカラム         ##############

  //["id"]            =>  int(17)
  //["JdId"]          =>  int(2)
  //["voucher_date"]  =>  string(10) "2025-12-31"
  //["summary"]       =>  string(0) ""
  //["account_id"]    =>  int(1)
  //["name"]          =>  string(6) "現金"
  //["type"]          =>  string(6) "資産"
  //["side"]          =>  string(6) "credit"
  //["amount"]        =>  int(5500)
  //["voucher_id"]    =>  int(17)

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
        $_SESSION['VoucherDetail'] = $_SESSION['VoucherDetail']  ?? $this->InitDetails;                 //セッションに初期値の明細行を保存(Voucher.create)


        $this->Date         = $_POST['ListVcrDate'] ?? $_SESSION['VcrSearchCond']['Date'] ?? '';        //search.phpのListVcrDate



        $this->Summary      = $_POST['ListVcrSummary'] ?? $_SESSION['VcrSearchCond']['Summary'] ?? '';  //search.phpのListVcrSummary
        $this->ListVcrNum   = $_POST['ListVcrNum'] ?? $_SESSION['VcrSearchCond']['ListVcrNum'] ?? '';   //search.phpのListVcrNum
        if(empty($_POST['LstVcrSearchStartDate']) && empty($_POST['LstVcrSearchEndDate']) ) {
            $this->VcrListDatePeriod   = $_SESSION['VcrSearchCond']['VcrListDatePeriod'] ?? ['検索開始日付' => '' , '検索終了日付' => '']; //search.phpの検索日付期間
        }else{
            $this->VcrListDatePeriod   =   [ '検索開始日付' => $_POST['LstVcrSearchStartDate'] ?? '' , '検索終了日付' => $_POST['LstVcrSearchEndDate'] ?? '' ];
        }

        $_SESSION['VcrSearchCond'] = ['Date'                => $this->Date ,
                                      'Summary'             => $this->Summary,
                                      'ListVcrNum'          => $this->ListVcrNum,
                                      'VcrListDatePeriod'   => $this->VcrListDatePeriod
                                     ];

        if (!empty($_POST['SimpleSearch'])) {
            $this->SearchType = $_POST['SimpleSearch'] ?? '';
            $this->ListVcrNum = $_POST['ListVcrNum'] ?? '';
            $this->Date = $_POST['ListVcrDate'] ?? '';
            $this->Summary = $_POST['ListVcrSummary'] ?? '';
        }else {
            $this->SearchType = $_POST['CompoundSearch'] ?? '';
        }
    }

    public function VcrCreData()
    {
        $this->VcrListResult = $_SESSION['VoucherDetail'] ?? [];         //検索結果の配列
        unset ($_SESSION['VoucherDetail']);                              //セッションの検索結果を初期化
        $this->Date      = $_POST['voucher_date'] ?? '';                 //create.phpのVoucherDate
        $this->Summary   = $_POST['summary'] ?? '';                      //create.phpのVoucherSummary
        $this->SearchType = $_POST['search_type'] ?? '';                 //search.phpのSearchType
        $this->VcrUpdNo = $_SESSION['VcrUpdNo'] ?? 0;                    //vcrlistで修正対象行の伝票番号を格納する変数
        unset($_SESSION['VcrUpdNo']);                               //vcrlistで修正対象行の伝票番号を格納する変数を初期化
        $this->DtoDetails       = $_POST['details'] ?? [$this->InitDetails[0]]; //create.phpの明細行
        if(empty($this->VcrSearchedData) && !empty($_SESSION['VcrSearchedData'])){
            $this->VcrSearchedData = $_SESSION['VcrSearchedData'];
            unset($_SESSION['VcrSearchedData']);
        }
    }
}

