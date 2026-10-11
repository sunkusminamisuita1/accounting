<?php
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
    }

    public function InitDetailsDto()
    {
        $this->dtoDetails   = $this->initDetails; //初期値の明細行をDtoにセット
        $_SESSION['voucherDetail'] = $this->initDetails; //セッションに初期値の明細行を保存(Voucher.create)
    }

    function dbg() {
        echo "<br><br><br>";
        $trace = debug_backtrace();
        print_r($trace[1] ?? null);
    }

    public function list()
    {
        $this->dtoDetails   = $this->initDetails??[]; //初期値の明細行をDtoにセット
        $_SESSION['voucherDetail'] = $_SESSION['voucherDetail']  ?? $this->initDetails;                 //セッションに初期値の明細行を保存(Voucher.create)
        $this->date         = $_POST['listVcrDate'] ?? $_SESSION['vcrSearchCond']['date'] ?? '';        //search.phpのlistVcrDate
        $this->summary      = $_POST['listVcrSummary'] ?? $_SESSION['vcrSearchCond']['summary'] ?? '';  //search.phpのlistVcrSummary
        $this->listVcrNum   = $_POST['listVcrNum'] ?? $_SESSION['vcrSearchCond']['listVcrNum'] ?? '';   //search.phpのlistVcrNum

        // ① POSTを取得
        $postStartDate = $_POST['lstVcrSearchStartDate'] ?? '';
        $postEndDate   = $_POST['lstVcrSearchEndDate'] ?? '';

        // ② POSTがあればPOST、なければSESSION
        if ($postStartDate !== '' || $postEndDate !== '') {
            $this->vcrListDatePeriod = [
                '検索開始日付' => $postStartDate,
                '検索終了日付' => $postEndDate
            ];
        } else {
            $this->vcrListDatePeriod = $_SESSION['vcrSearchCond']['vcrListDatePeriod']
                ?? [
                    '検索開始日付' => '',
                    '検索終了日付' => ''
                ];
        }
        $_SESSION['vcrSearchCond']['vcrListDatePeriod'] = '';
        // ③ 未入力ならデフォルト値
        if ($this->vcrListDatePeriod['検索開始日付'] === '') {
            $this->vcrListDatePeriod['検索開始日付']
                = date('Y-m-d', strtotime('-100 year'));
        }

        if ($this->vcrListDatePeriod['検索終了日付'] === '') {
            $this->vcrListDatePeriod['検索終了日付']
                = date('Y-m-d');
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

