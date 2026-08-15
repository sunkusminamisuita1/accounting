<?php
// app/dto/voucherDto.php
class voucherDto
{       //##############   Dtoでは$_SESSIONからデータを取得してプロパティにセットする。その後$_SESSIONは初期化する。 ##############
    public  $date = '';
    public  $summary = '';
    public array $accounts = [];
    public array $dtoDetails = [0 => ['account_id' => '', 'jd_summary' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    //public array $vcrDetailAddRow = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public array $initDetails = [0 => ['account_id' => '', 'amount' => '', 'side' => 'debit']]; //明細行の配列
    public  $searchType = '';
    public  $listVcrNum = '';
    public array $errData = []; //エラー行の配列 ['ModName' => 'エラーメッセージ']
    public array $vcrListResult =  []; //検索結果の配列
    public array $vcrSearchedData = [];
    public array $vcrUpdData = [];//vcrlistで修正対象行のデータを格納する配列
    public array $initVcrSearchedData = [];
    public array $vcrListDatePeriod = []; //検索日付期間    [開始日付=>9999-99-99,終了日付=>9999-99-99]
    public array $accountTbl = [];
    public array $vcrUpdRow = [];
    public $vcrUpdNo = 0;
    public $vcrDeleteNo = 0;//vcrlistで伝票削除行の行番号を格納する変数(voucher_id)
    public array $vcrInputData = []; //vcrlistで検索条件を格納する配列
    public array $post = [];
    public array $session = [];


    public function __construct(array $details)
    {
        $this->vcrListResult =  []; //検索結果の配列
        $this->date      =  ''; //create.phpのVoucherDate
        $this->summary   =  '';      //create.phpのVoucherSummary
        $this->searchType =  ''; //search.phpのSearchType
        $this->vcrUpdNo =  0; //vcrlistで修正対象行の伝票番号を格納する変数
        $this->dtoDetails      =  [];
        $this->vcrSearchedData = [];
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

//###########         配列vcrListResultのカラム         ##############

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
        $this->dtoDetails   = $this->initDetails; //初期値の明細行をDtoにセット
        $_SESSION['voucherDetail'] = $this->initDetails; //セッションに初期値の明細行を保存(Voucher.create)
//        $this->summary      = '';
//        $this->account_id   = [];
//        $this->side         = [];
//        $this->amount       = [];
    }

    public function list()
    {
        $this->dtoDetails   = $this->initDetails??[]; //初期値の明細行をDtoにセット
        $_SESSION['voucherDetail'] = $_SESSION['voucherDetail']  ?? $this->initDetails;                 //セッションに初期値の明細行を保存(Voucher.create)


        $this->date         = $_POST['listVcrDate'] ?? $_SESSION['vcrSearchCond']['date'] ?? '';        //search.phpのlistVcrDate



        $this->summary      = $_POST['listVcrSummary'] ?? $_SESSION['vcrSearchCond']['summary'] ?? '';  //search.phpのlistVcrSummary
        $this->listVcrNum   = $_POST['listVcrNum'] ?? $_SESSION['vcrSearchCond']['listVcrNum'] ?? '';   //search.phpのlistVcrNum
        if(empty($_POST['lstVcrSearchStartDate']) && empty($_POST['lstVcrSearchEndDate']) ) {
            $this->vcrListDatePeriod   = $_SESSION['vcrSearchCond']['vcrListDatePeriod'] ?? ['検索開始日付' => '' , '検索終了日付' => '']; //search.phpの検索日付期間
        }else{
            $this->vcrListDatePeriod   =   [ '検索開始日付' => $_POST['lstVcrSearchStartDate'] ?? '' , '検索終了日付' => $_POST['lstVcrSearchEndDate'] ?? '' ];
        }

        $_SESSION['vcrSearchCond'] = ['date'                => $this->date ,
                                      'summary'             => $this->summary,
                                      'listVcrNum'          => $this->listVcrNum,
                                      'vcrListDatePeriod'   => $this->vcrListDatePeriod
                                     ];

        if (!empty($_POST['simpleSearch'])) {
            $this->searchType = $_POST['simpleSearch'] ?? '';
            $this->listVcrNum = $_POST['listVcrNum'] ?? '';
            $this->date = $_POST['listVcrDate'] ?? '';
            $this->summary = $_POST['listVcrSummary'] ?? '';
        }else {
            $this->searchType = $_POST['compoundSearch'] ?? '';
        }
    }

    public function vcrCreData()
    {
        $this->vcrListResult = $_SESSION['voucherDetail'] ?? [];         //検索結果の配列
        unset ($_SESSION['voucherDetail']);                              //セッションの検索結果を初期化
        $this->date      = $_POST['voucher_date'] ?? '';                 //create.phpのVoucherDate
        $this->summary   = $_POST['summary'] ?? '';                      //create.phpのVoucherSummary
        $this->searchType = $_POST['search_type'] ?? '';                 //search.phpのSearchType
        $this->vcrUpdNo = $_SESSION['vcrUpdNo'] ?? 0;                    //vcrlistで修正対象行の伝票番号を格納する変数
        unset($_SESSION['vcrUpdNo']);                               //vcrlistで修正対象行の伝票番号を格納する変数を初期化
        $this->dtoDetails       = $_POST['details'] ?? [$this->initDetails[0]]; //create.phpの明細行
        if(empty($this->vcrSearchedData) && !empty($_SESSION['vcrSearchedData'])){
            $this->vcrSearchedData = $_SESSION['vcrSearchedData'];
            unset($_SESSION['vcrSearchedData']);
        }
    }
}

