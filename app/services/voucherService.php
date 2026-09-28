<?php
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';
require_once ROOT_PATH . '/app/validators/voucherValidator.php';

class voucherService{
    private voucherRepository $repo;
    private voucherValidator $validator;
    private voucherDto $dto;
    private $pdo;

    public function __construct($pdo, voucherDto $dto, voucherValidator $validator, voucherRepository $repo)    {
        $this->pdo          = $pdo;
        $this->dto          = $dto;
        $this->validator    = $validator;
        $this->repo         = $repo;
    }

    public function list(int $userId): array {
        return $this->repo->findAllByUser($userId);
    }

    public function find(int $id) {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data){
        $this->repo->update($id, $data);
    }

    public function InitializeSession(): void    {
        $_SESSION['voucherRows'] = $_SESSION['voucherRows'] ?? [];
        $_SESSION['slipNum'] = $_SESSION['slipNum'] ?? 0;
        $_SESSION['editData'] = $_SESSION['editData'] ?? [];
        $_SESSION['debitAmountTotal'] = $_SESSION['debitAmountTotal'] ?? 0;
        $_SESSION['creditAmountTotal'] = $_SESSION['creditAmountTotal'] ?? 0;
    }

    public function getAccounts(): array {
        return $this->repo->getAccounts();
    }

    public function vcrCreate(){
        $accounts = $this->dto->accounts;
        if (isset($_POST['add_row'])) {  
            $this->vcrRowAdd($this->dto);
        }
        if (isset($_POST['delete_row'])) {
            $this->vcrRowDel($this->dto);
        }
        if (isset($_POST['save'])) {
            $this->dto->date = $_POST['voucher_date'] ?? "" ;
            $this->validator->create();
            $this->vcrSave();
            if(empty($this->dto->errData)) {
                $this->dto->InitDetailsDto(); //保存成功後、Dtoの明細行を初期化
                $this->dto->errData = ['voucherService' => '保存が完了しました'];
            }
        }
    }

    public function vcrRowAdd(){
        $details = $_POST['details'] ?? [];
        $addKey = (int)$_POST['add_row'] + 1; //追加する行の位置
        $addRow = [['account_id' => '', 'jd_summary' => "", 'amount' => '', 'side' => 'debit']]; //初期値は借方
        array_splice($details, $addKey, 0, $addRow);
        $this->dto->dtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }


    private function VcrRowAddCommon(): void {
        $vcrSearchedData = $_SESSION['VcrSearchedData'];
        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData'];
        $newVcrRowAddr = (int)$_POST['vcrAddDebit']  + 1;
        $newId = $_POST['id'] ?? '';
    }


    public function vcrSimpleSearch(): void {
            $this->dto->list(); //dtoのListメソッドで検索条件をセット
            $this->validator->list($this->dto);
            if(empty($this->dto->errData)){
                $vcrListResult = $this->repo->vcrListSearch()??[];           
                foreach($vcrListResult as $idx => $row) {
                    foreach ( $row as $key => $value) {
                        $vcrListResult[$idx][$key]=$value;
                        $vcrListResult[$idx][$key]=$value;
                    //    }
                    }  
                }
                $this->dto->vcrListResult         = empty($vcrListResult) ? [] : $vcrListResult;
                $_SESSION['vcrListResult']  = empty($vcrListResult) ? [] : $vcrListResult; //変数名上に合わしたほうがベター
            }        
    }

//修正ボタンを押したとき修正データ作成 $voucherDto->vcrSearchedData
    public function vcrUpdNo(): void {
            $creditTotal = 0;$debitTotal = 0; $LineNo = 0;
            $this->dto->vcrSearchedData = [];                 //修正用データを格納する配列を初期化
            $this->dto->vcrUpdNo        =  $_POST['vcrUpdateNo'] ?? 0; //vcrUpdNoに伝票番号(VoucerDetail->voucher_id)をセット
            $_SESSION['vcrUpdNo'] = $_POST['vcrUpdateNo'];     //セッションにvcrUpdNoを保存 リダイレクト時、Dtoで復元される
            $this->dto->vcrListResult   = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元
            foreach ($this->dto->vcrListResult as $no0 => $value0) {
                if (isset($value0['voucher_id']) && 
                    $value0['voucher_id'] == $this->dto->vcrUpdNo &&
                    isset($value0['JdId']))   {   //修正対象伝票のデータだけを$voucherDto->vcrSearchedDataに格納
                    $this->dto->vcrSearchedData[$LineNo] = $value0;
                    $LineNo++;                          //編集用データ$voucherDto->vcrSearchedDataの行番号を0から振り直すための変数
                }
            }
            $Success    =   $this->validator->ChkTotalBalance($this->dto->vcrSearchedData); //貸し借り不一致チェック
            $_SESSION['VcrSearchedData'] = $this->dto->vcrSearchedData;//修正用データをセッションに保存
    }


//行追加・行削除ボタンを押したときの処理
    public function vcrAddDebit(): void {
        $newVcrRowAddr = (int)$_POST['vcrAddDebit']  + 1;

        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元

        $this->vcrSearchedDataRemake($newVcrRowAddr);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $_SESSION['NewVcrRowAddr'] = $newVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1

        $newId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';

        $Side = 'debit';

        $this->vcrAddRowIns($newVcrRowAddr, $newId, $Side);

        $this->vcrTmpDataSave($newVcrRowAddr);

    }

    public function vcrAddCredit(): void {
        
        $newVcrRowAddr = (int)$_POST['vcrAddCredit']  + 1;
        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元
        $this->vcrSearchedDataRemake($newVcrRowAddr);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $this->dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $_SESSION['NewVcrRowAddr'] = $newVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1
        $newId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';
        $Side = 'credit';
        $this->vcrAddRowIns($newVcrRowAddr, $newId, $Side);
        $this->vcrTmpDataSave($newVcrRowAddr);
    }

    public function vcrDetailLineDel(): void {
        $this->dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ; //行追加前のデータをセッションから復元
        $this->vcrSearchedDataRemake();

        foreach ($this->dto->vcrSearchedData as $idx => $row) {
        }
        if($idx < 1) {
            $this->dto->errData['voucherService.vcrDetailLineDel'] = "最終行は削除できません。伝票を削除するには、伝票削除のボタンを押してください。";
            return;
        }

        $newVcrRowAddr = (int)$_POST['vcrDetailLineDel'];
        $newId = $_POST['id'] ?? '';
        $_SESSION['NewVcrRowAddr'] = $newVcrRowAddr; //行削除後の行番号をDtoに保存

        array_splice($this->dto->vcrSearchedData, $newVcrRowAddr, 1);
        $this->dto->vcrSearchedData = array_values($this->dto->vcrSearchedData ); // インデックスを並べ直す     saveVoucher(array $data)

        $_SESSION['VcrSearchedData'] = $this->dto->vcrSearchedData; // 左側を保存

        $this->vcrTmpDataSave();
    }

    private function vcrAddRowIns($newVcrRowAddr, $newId, $Side): void {
        // 1. セッションからデータを復元
        $this->dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; 
        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; 

        // -------------------------------------------------------------
        // 【仕様対応】新しく挿入する「空の箱（明細行）」を作成
        // -------------------------------------------------------------
        $newJdId = (int)($_POST['JdId'] ?? 0);
        $newRow = [
            'id'            =>  (int)'0',
            'JdId'          =>  (int)$newId,
            'voucher_date'  =>  (string)$this->dto->vcrListResult[0]['voucher_date'],
            'summary'       =>  (string)"",
            'account_id'    =>  (int)'0',
            'name'          =>  (string)"",
            'type'          =>  (string)"",
            'side'          =>  (string)$Side,
            'amount'        =>  (int)'0',
            'voucher_id'    => $newId,
            'LineNo'        => "0",
            'jd_summary'   =>  (string)""
        ];
        $_SESSION['VcrSearchedData'] =  $this->dto->vcrSearchedData ?? []; //行追加のデータをセッションに保存

        // -------------------------------------------------------------
        // 【仕様対応：左側】VcrSearchedData（修正対象1件）の指定位置に行を挿入
        // -------------------------------------------------------------
        array_splice($this->dto->vcrSearchedData, $newVcrRowAddr, 0, [$newRow]); //行挿入
        $this->dto->vcrSearchedData = array_values($this->dto->vcrSearchedData); 
        $_SESSION['VcrSearchedData'] = $this->dto->vcrSearchedData; // 左側を保存
 
    }

    public function vcrSearchedDataRemake(): void {

        $newCount = count($_SESSION['VcrSearchedData'] ?? []) - 1 ; //行追加、行削除の前の行数をカウント　
        $accounts  =      empty($this->dto->accounts) ? [] : $this->dto->accounts; //accountsがDtoにセットされていない場合は、Repoから取得して$accountsにセット　行追加・行削除の前の行数をカウント
        $this->dto->vcrSearchedData = $_SESSION['VcrSearchedData']; //行追加・行削除の処理を行う前に、$dto->vcrSearchedDataを初期化  vcrUpdDt
        for($idx = 0; $idx <= $newCount; ) {
            foreach ($accounts as $a) {
                if((int)$a['id'] === (int)($_POST['vcrUpdDt'][$idx]['account_id'] ?? '0')) {
                    $accountId  =   $a['id'];
                    $name       =   $a['name'];
                    $Type       =   $a['type'];
                    break;
                }
            }
            $this->dto->vcrSearchedData[$idx]['id']           = isset($_SESSION['VcrSearchedData'][$idx]['id']) ? (string)$_SESSION['VcrSearchedData'][$idx]['id'] : '';
            $this->dto->vcrSearchedData[$idx]['Jdid']         = isset($_POST['vcrUpdDt'][$idx]['voucher_id']) ? (int)$_POST['vcrUpdDt'][$idx]['voucher_id'] : 0;
            $this->dto->vcrSearchedData[$idx]['voucher_date'] = isset($_SESSION['VcrSearchedData'][0]['voucher_date']) ? (string)$_SESSION['VcrSearchedData'][0]['voucher_date'] : '';
            $this->dto->vcrSearchedData[$idx]['summary']      = isset($_POST['vcrUpdDt'][$idx]['summary']) ? (string)$_POST['vcrUpdDt'][$idx]['summary'] : '';
            $this->dto->vcrSearchedData[$idx]['account_id']   = isset($_POST['vcrUpdDt'][$idx]['account_id']) ? (int)$_POST['vcrUpdDt'][$idx]['account_id'] : 0;
            $this->dto->vcrSearchedData[$idx]['name']         = $name ?? '';
            $this->dto->vcrSearchedData[$idx]['type']         = $Type;
            $this->dto->vcrSearchedData[$idx]['side']         = isset($_POST['vcrUpdDt'][$idx]['side']) ? (string)$_POST['vcrUpdDt'][$idx]['side'] : '';
            $this->dto->vcrSearchedData[$idx]['amount']       = isset($_POST['vcrUpdDt'][$idx]['amount']) ? (int)$_POST['vcrUpdDt'][$idx]['amount'] : '';
            $this->dto->vcrSearchedData[$idx]['voucher_id']   = isset($_POST['vcrUpdDt'][$idx]['voucher_id']) ? (int)$_POST['vcrUpdDt'][$idx]['voucher_id'] : 0;
            $this->dto->vcrSearchedData[$idx]['LineNo']       = (int)$idx;
            $this->dto->vcrSearchedData[$idx]['jd_summary']   = isset($_POST['vcrUpdDt'][$idx]['jd_summary']) ? (string)$_POST['vcrUpdDt'][$idx]['jd_summary'] : '';
            $idx++;
        }
        $_SESSION['VcrSearchedData'] = $this->dto->vcrSearchedData; // 左側を保存

    }

    private function vcrTmpDataSave(): void {
        $this->dto->vcrSearchedData = array_values($this->dto->vcrSearchedData); //インデックスを振り直す
        $_SESSION['VcrSearchedData'] = $this->dto->vcrSearchedData;//行追加・行削除後のデータをセッションに保存
    }

//修正実行ボタンを押した時、実行
    public function vcrUpdate(): bool {
        $this->vcrSearchedDataRemake();
        // 貸方、借方　バランスチェック
 
        $Success    =   $this->validator->ChkTotalBalance($this->dto->vcrSearchedData); //貸し借り不一致チェック

        if( ! $Success){

            $this->dto->vcrListResult     =   $_SESSION['vcrListResult'] ?? "";
            $this->dto->vcrSearchedData   =   $_SESSION['VcrSearchedData'] ?? "";

            return false;

        }

        $Success = $this->vcrDelete();
        $this->dto->vcrListResult     =   $_SESSION['vcrListResult'] ?? "";
        $this->dto->vcrSearchedData   =   $_SESSION['VcrSearchedData'] ?? "";
        $this->dto->vcrUpdNo          =   $_SESSION['vcrUpdNo'] ?? 0;      //セッションにvcrUpdNoをDtoに保存
        $this->dto->date              =   $this->dto->vcrSearchedData[0]['voucher_date'];
        $this->dto->summary           =   $_POST['vcrUpdDt'][0]['summary'];

        $this->dto->dtoDetails   =   [];
        foreach($this->dto->vcrSearchedData as $key => $row){
            $this->dto->dtoDetails[$key]['account_id']    =   $row['account_id'];
            $this->dto->dtoDetails[$key]['side']          =   $row['side'];
            $this->dto->dtoDetails[$key]['amount']        =   $row['amount'];
            $this->dto->dtoDetails[$key]['jd_summary']    =   $row['jd_summary'];
        }
        $this->repo->insertVoucher();/////////////1
        
        $this->vcrSimpleSearch();     //削除後、最新の検索データを読み直す
        $this->dto->vcrSearchedData = [];       //削除後、編集エリアをクリア

        return true;
    }

    public function vcrDelete(): bool {
        $this->dto->vcrSearchedData   = $_SESSION['VcrSearchedData'];
        $this->dto->vcrUpdNo          =   $_SESSION['vcrUpdNo'] ?? 0;      //セッションにvcrUpdNoをDtoに保存
        $this->repo->jvJdDelete();/////////////1
        $this->vcrSimpleSearch();     //削除後、最新の検索データを読み直す
        $this->dto->vcrSearchedData = [];       //削除後、編集エリアをクリア

        return true;
    }
            
    public function vcrRowDel(): void {
        $details = $_POST['details'] ?? [];
        $idx = (int)$_POST['delete_row'];
        unset($details[$idx]);
        $this->dto->dtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }

    public function vcrSave(): void {
        if (empty($this->dto->errData)) {
            $this->repo->insertVoucher(); 
        }
    }
}
