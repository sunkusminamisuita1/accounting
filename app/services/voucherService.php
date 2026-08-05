<?php
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';
require_once ROOT_PATH . '/app/Validators/voucherValidator.php';

class voucherService{
    private voucherRepository $repo;
    private voucherValidator $validator;

    public function __construct()    {
        $this->repo = new voucherRepository();
        $this->validator = new voucherValidator();
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

    public function VcrCreate($dto){
        $accounts = $dto->accounts;
        if (isset($_POST['add_row'])) {  
            $this->vcrRowAdd($dto);
        }
        if (isset($_POST['delete_row'])) {
            $this->vcrRowDel($dto);
        }
        if (isset($_POST['save'])) {
            $this->validator->Create($dto);
            $this->vcrSave($dto,$this->validator);
            if(empty($dto->errData)) {
                $dto->InitDetailsDto(); //保存成功後、Dtoの明細行を初期化
                $dto->errData = ['voucherService' => '保存が完了しました'];
            }
        }
    }


    public function VcrSimpleSearch(voucherDto $dto): void {
            $dto->list(); //dtoのListメソッドで検索条件をセット
            $this->validator->list($dto);
            if(empty($dto->errData)){
                $vcrListResult = $this->repo->vcrListSearch($dto)??[];           
                foreach($vcrListResult as $idx => $row) {
                    foreach ( $row as $key => $value) {
                        $vcrListResult[$idx][$key]=$value;
                            $vcrListResult[$idx][$key]=$value;
                    //    }
                    }  
                }
                $dto->vcrListResult         = empty($vcrListResult) ? [] : $vcrListResult;
                $_SESSION['vcrListResult']  = empty($vcrListResult) ? [] : $vcrListResult; //変数名上に合わしたほうがベター
            }        
    }

//修正ボタンを押したとき修正データ作成 $voucherDto->vcrSearchedData
    public function VcrUpdNo(voucherDto $dto , voucherRepository $repo, voucherValidator $validator): void {
            $CreditTotal = 0;$DebitTotal = 0; $LineNo = 0;
            $dto->vcrSearchedData = [];                 //修正用データを格納する配列を初期化
            $dto->vcrUpdNo        =  $_POST['VcrUpdateNo'] ?? 0; //VcrUpdNoに伝票番号(VoucerDetail->voucher_id)をセット
            $_SESSION['VcrUpdNo'] = $_POST['VcrUpdateNo'];     //セッションにVcrUpdNoを保存 リダイレクト時、Dtoで復元される
            $dto->vcrListResult   = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元
            foreach ($dto->vcrListResult as $no0 => $value0) {
                if (isset($value0['voucher_id']) && 
                    $value0['voucher_id'] == $dto->vcrUpdNo &&
                    isset($value0['JdId']))   {   //修正対象伝票のデータだけを$voucherDto->vcrSearchedDataに格納
                    $dto->vcrSearchedData[$LineNo] = $value0;
                    $LineNo++;                          //編集用データ$voucherDto->vcrSearchedDataの行番号を0から振り直すための変数
                    //if($value0['side'] === 'credit'){
                    //    $CreditTotal += (int)$value0['amount'];
                    //}else{
                    //    $DebitTotal  += (int)$value0['amount'];
                    //}
                }
            }
            $Success    =   $this->validator->ChkTotalBalance( $dto, $dto->vcrSearchedData);
            $_SESSION['VcrSearchedData'] = $dto->vcrSearchedData;//修正用データをセッションに保存
            //if( $CreditTotal !== $DebitTotal ){
            //    $dto->errData['voucherService'] = "貸方合計　¥{$CreditTotal}　借方合計　¥{$DebitTotal}　不一致です。";
            //}
    }


//行追加・行削除ボタンを押したときの処理
    public function VcrAddDebit(voucherDto $dto, voucherRepository $repo, voucherValidator $validator): void {
        $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;

        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元

        $this->vcrSearchedDataRemake($dto , $repo, $validator, $NewVcrRowAddr);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1

        $NewId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';

        $Side = 'debit';

        $this->vcrAddRowIns( $dto, $NewVcrRowAddr, $NewId, $Side);


        $this->vcrTmpDataSave($dto, $repo, $validator, $NewVcrRowAddr);

    }

    public function VcrAddCredit(voucherDto $dto, voucherRepository $repo, voucherValidator $validator): void {
        
        $NewVcrRowAddr = (int)$_POST['VcrAddCredit']  + 1;
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元
        $this->vcrSearchedDataRemake($dto , $repo, $validator, $NewVcrRowAddr);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1
        $NewId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';
        $Side = 'credit';
        $this->vcrAddRowIns( $dto, $NewVcrRowAddr, $NewId, $Side);
        $this->vcrTmpDataSave($dto, $repo, $validator, $NewVcrRowAddr);
    }

    public function VcrDetailLineDel(voucherDto $dto, voucherRepository $repo, voucherValidator $validator): void {
        $dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ; //行追加前のデータをセッションから復元
        $this->vcrSearchedDataRemake($dto , $repo, $validator);

        foreach ($dto->vcrSearchedData as $idx => $row) {
        }
        if($idx < 1) {
            $dto->errData['voucherService.VcrDetailLineDel'] = "最終行は削除できません。伝票を削除するには、伝票削除のボタンを押してください。";
            return;
        }

        $NewVcrRowAddr = (int)$_POST['VcrDetailLineDel'];
        $NewId = $_POST['id'] ?? '';
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行削除後の行番号をDtoに保存

        array_splice($dto->vcrSearchedData, $NewVcrRowAddr, 1);
        $dto->vcrSearchedData = array_values($dto->vcrSearchedData ); // インデックスを並べ直す     saveVoucher(array $data)

        $_SESSION['VcrSearchedData'] = $dto->vcrSearchedData; // 左側を保存

        $this->vcrTmpDataSave($dto, $repo, $validator);
    }

    private function VcrAddRowIns(voucherDto $dto, $NewVcrRowAddr, $NewId, $Side): void {
        // 1. セッションからデータを復元
        $dto->vcrListResult = $_SESSION['vcrListResult'] ?? []; 
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; 

        // -------------------------------------------------------------
        // 【仕様対応】新しく挿入する「空の箱（明細行）」を作成
        // -------------------------------------------------------------
        $NewJdId = (int)($_POST['JdId'] ?? 0);
        $NewRow = [
            'id'            =>  (int)'0',
            'JdId'          =>  (int)$NewId,
            'voucher_date'  =>  (string)$dto->vcrListResult[0]['voucher_date'],
            'summary'       =>  (string)"",
            'account_id'    =>  (int)'0',
            'name'          =>  (string)"",
            'type'          =>  (string)"",
            'side'          =>  (string)$Side,
            'amount'        =>  (int)'0',
            'voucher_id'    => $NewId,
            'LineNo'        => "0",
            'jd_summary'   =>  (string)""
        ];
        $_SESSION['VcrSearchedData'] =  $dto->vcrSearchedData ?? []; //行追加のデータをセッションに保存

        // -------------------------------------------------------------
        // 【仕様対応：左側】VcrSearchedData（修正対象1件）の指定位置に行を挿入
        // -------------------------------------------------------------
        array_splice($dto->vcrSearchedData, $NewVcrRowAddr, 0, [$NewRow]); //行挿入
        $dto->vcrSearchedData = array_values($dto->vcrSearchedData); 
        $_SESSION['VcrSearchedData'] = $dto->vcrSearchedData; // 左側を保存
 
    }

    public function VcrSearchedDataRemake(voucherDto $dto , 
                    voucherRepository $repo, voucherValidator $validator): void {

        $NewCount = count($_SESSION['VcrSearchedData'] ?? []) - 1 ; //行追加、行削除の前の行数をカウント　
        $accounts  =      empty($dto->accounts) ? [] : $dto->accounts; //accountsがDtoにセットされていない場合は、Repoから取得して$accountsにセット　行追加・行削除の前の行数をカウント
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData']; //行追加・行削除の処理を行う前に、$dto->vcrSearchedDataを初期化  VcrUpdDt
        for($idx = 0; $idx <= $NewCount; ) {
            foreach ($accounts as $a) {
                if((int)$a['id'] === (int)($_POST['VcrUpdDt'][$idx]['account_id'] ?? '0')) {
                    $AccountId  =   $a['id'];
                    $Name       =   $a['name'];
                    $Type       =   $a['type'];
                    break;
                }
            }
            $dto->vcrSearchedData[$idx]['id']           = isset($_SESSION['VcrSearchedData'][$idx]['id']) ? (string)$_SESSION['VcrSearchedData'][$idx]['id'] : '';
            $dto->vcrSearchedData[$idx]['Jdid']         = isset($_POST['VcrUpdDt'][$idx]['voucher_id']) ? (int)$_POST['VcrUpdDt'][$idx]['voucher_id'] : 0;
            $dto->vcrSearchedData[$idx]['voucher_date'] = isset($_SESSION['VcrSearchedData'][0]['voucher_date']) ? (string)$_SESSION['VcrSearchedData'][0]['voucher_date'] : '';
            // summary: check nested key presence to avoid undefined index warning
            $dto->vcrSearchedData[$idx]['summary']      = isset($_POST['VcrUpdDt'][$idx]['summary']) ? (string)$_POST['VcrUpdDt'][$idx]['summary'] : '';
            $dto->vcrSearchedData[$idx]['account_id']   = isset($_POST['VcrUpdDt'][$idx]['account_id']) ? (int)$_POST['VcrUpdDt'][$idx]['account_id'] : 0;
            $dto->vcrSearchedData[$idx]['name']         = $Name ?? '';
            $dto->vcrSearchedData[$idx]['type']         = $Type;
            $dto->vcrSearchedData[$idx]['side']         = isset($_POST['VcrUpdDt'][$idx]['side']) ? (string)$_POST['VcrUpdDt'][$idx]['side'] : '';
            $dto->vcrSearchedData[$idx]['amount']       = isset($_POST['VcrUpdDt'][$idx]['amount']) ? (int)$_POST['VcrUpdDt'][$idx]['amount'] : '';
            $dto->vcrSearchedData[$idx]['voucher_id']   = isset($_POST['VcrUpdDt'][$idx]['voucher_id']) ? (int)$_POST['VcrUpdDt'][$idx]['voucher_id'] : 0;
            $dto->vcrSearchedData[$idx]['LineNo']       = (int)$idx;
            $dto->vcrSearchedData[$idx]['jd_summary']   = isset($_POST['VcrUpdDt'][$idx]['jd_summary']) ? (string)$_POST['VcrUpdDt'][$idx]['jd_summary'] : '';
            $idx++;
        }
        $_SESSION['VcrSearchedData'] = $dto->vcrSearchedData; // 左側を保存

    }

    private function VcrRowAddCommon(voucherDto $dto, voucherRepository $repo, voucherValidator $validator): void {
        $vcrSearchedData = $_SESSION['VcrSearchedData'];
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'];
        $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;
        $NewId = $_POST['id'] ?? '';
    }

    private function VcrTmpDataSave(voucherDto $dto, voucherRepository $repo, voucherValidator $validator): void {
        $dto->vcrSearchedData = array_values($dto->vcrSearchedData); //インデックスを振り直す
        $_SESSION['VcrSearchedData'] = $dto->vcrSearchedData;//行追加・行削除後のデータをセッションに保存
    }

//修正実行ボタンを押した時、実行
    public function VcrUpdate(voucherDto $dto ,voucherRepository $repo): bool {
        $this->vcrSearchedDataRemake($dto,$repo,$this->validator);
        // 貸方、借方　バランスチェック
        //var_dump($dto->vcrSearchedData);

//        $Success    =   $this->validator->ChkTotalBalance( $dto, $_SESSION['VcrSearchedData'] ?? "");
        $Success    =   $this->validator->ChkTotalBalance( $dto, $dto->vcrSearchedData ?? "");

        if( ! $Success){

            $dto->vcrListResult     =   $_SESSION['vcrListResult'] ?? "";
            $dto->vcrSearchedData   =   $_SESSION['VcrSearchedData'] ?? "";

            return false;

        }

        $Success = $this->vcrDelete($dto, $repo);
        $dto->vcrListResult     =   $_SESSION['vcrListResult'] ?? "";
        $dto->vcrSearchedData   =   $_SESSION['VcrSearchedData'] ?? "";

        //requireCsrf();　　　　　//CSRFトークンの検証はコントローラーで行う
        $dto->vcrUpdNo  =   $_SESSION['VcrUpdNo'] ?? 0;      //セッションにVcrUpdNoをDtoに保存
        $voucherId      =   $_SESSION['VcrUpdNo'] ?? 0;       //セッションから伝票番号を取得

        $dto->date   =   $dto->vcrListResult[0]['voucher_date'];
        $dto->summary   =   $_POST['VcrUpdDt'][0]['summary'];

        $dto->dtoDetails   =   [];
        foreach($dto->vcrSearchedData as $key => $row){
            $dto->dtoDetails[$key]['account_id']  =   $row['account_id'];
            $dto->dtoDetails[$key]['side']        =   $row['side'];
            $dto->dtoDetails[$key]['amount']        =   $row['amount'];
            $dto->dtoDetails[$key]['jd_summary']    =   $row['jd_summary'];
        }
        $repo->insertVoucher($dto);/////////////1
        
        $this->vcrSimpleSearch($dto);     //削除後、最新の検索データを読み直す
        $dto->vcrSearchedData = [];       //削除後、編集エリアをクリア

        return true;
    }

    public function VcrDelete(voucherDto $dto ,voucherRepository $repo): bool {
        $dto->vcrSearchedData = $_SESSION['VcrSearchedData'];

        //requireCsrf();　　　　　//CSRFトークンの検証はコントローラーで行う
        $dto->vcrUpdNo  =   $_SESSION['VcrUpdNo'] ?? 0;      //セッションにVcrUpdNoをDtoに保存
        $voucherId      =   $_SESSION['VcrUpdNo'] ?? 0;       //セッションから伝票番号を取得
        $repo->jvJdDelete($dto);/////////////1
        $this->vcrSimpleSearch($dto);     //削除後、最新の検索データを読み直す
        $dto->vcrSearchedData = [];       //削除後、編集エリアをクリア

        return true;
    }

    public function vcrRowAdd($vcrDto){
        $details = $_POST['details'] ?? [];
        $AddKey = (int)$_POST['add_row'] + 1; //追加する行の位置
        $AddRow = [['account_id' => '', 'jd_summary' => "", 'amount' => '', 'side' => 'debit']]; //初期値は借方
        array_splice($details, $AddKey, 0, $AddRow);
        $vcrDto->dtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }
            
    public function vcrRowDel($vcrDto){
        $details = $_POST['details'] ?? [];
        $idx = (int)$_POST['delete_row'];
        unset($details[$idx]);
        $vcrDto->dtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }

    public function vcrSave($vcrDto,$vcrValidator){
        if (empty($vcrDto->errData)) {
//            $indexCnt = count($vcrDto->account_id) ?? 0;
            $this->repo->insertVoucher($vcrDto); 
        }
    }
}
