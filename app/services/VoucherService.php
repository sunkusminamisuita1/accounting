<?php
require_once ROOT_PATH . '/app/repositories/voucherRepository.php';
require_once ROOT_PATH . '/app/Validators/VoucherValidator.php';

class VoucherService{
    private VoucherRepository $Repo;
    private VoucherValidator $Validator;
    public function __construct()    {
        $this->Repo = new VoucherRepository();
        $this->Validator = new VoucherValidator();
    }

    public function list(int $userId): array {
        return $this->Repo->findAllByUser($userId);
    }

    public function find(int $id) {
        return $this->Repo->find($id);
    }

    public function update(int $id, array $data){
        $this->Repo->update($id, $data);
    }

    public function InitializeSession(): void    {
        $_SESSION['voucherRows'] = $_SESSION['voucherRows'] ?? [];
        $_SESSION['slipNum'] = $_SESSION['slipNum'] ?? 0;
        $_SESSION['editData'] = $_SESSION['editData'] ?? [];
        $_SESSION['debitAmountTotal'] = $_SESSION['debitAmountTotal'] ?? 0;
        $_SESSION['creditAmountTotal'] = $_SESSION['creditAmountTotal'] ?? 0;
    }

    public function getAccounts(): array {
        return $this->Repo->getAccounts();
    }

    public function VcrCreate($Dto){
        $accounts = $Dto->Accounts;
        if (isset($_POST['add_row'])) {  
            $this->VcrRowAdd($Dto);
        }
        if (isset($_POST['delete_row'])) {
            $this->VcrRowDel($Dto);
        }
        if (isset($_POST['save'])) {
            $this->Validator->Create($Dto);
            $this->VcrSave($Dto,$this->Validator);
            if(empty($Dto->ErrData)) {
                $Dto->InitDetailsDto(); //保存成功後、DTOの明細行を初期化
                $Dto->ErrData = ['VoucherService' => '保存が完了しました'];
            }
        }
    }


    public function VcrSimpleSearch(VoucherDTO $Dto): void {
            $Dto->List(); //DTOのListメソッドで検索条件をセット
            $this->Validator->list($Dto);
            if(empty($Dto->ErrData)){
                $VcrListResult = $this->Repo->VcrListSearch($Dto)??[];           
                foreach($VcrListResult as $idx => $row) {
                    foreach ( $row as $key => $value) {
                        $VcrListResult[$idx][$key]=$value;
                            $VcrListResult[$idx][$key]=$value;
                    //    }
                    }  
                }
                $Dto->VcrListResult         = empty($VcrListResult) ? [] : $VcrListResult;
                $_SESSION['VcrListResult']  = empty($VcrListResult) ? [] : $VcrListResult; //変数名上に合わしたほうがベター
            }        
    }

//修正ボタンを押したとき修正データ作成 $VoucherDto->VcrSearchedData
    public function VcrUpdNo(VoucherDTO $Dto , VoucherRepository $Repo, VoucherValidator $Validator): void {
            $CreditTotal = 0;$DebitTotal = 0; $LineNo = 0;
            $Dto->VcrSearchedData = [];                 //修正用データを格納する配列を初期化
            $Dto->VcrUpdNo        =  $_POST['VcrUpdateNo'] ?? 0; //VcrUpdNoに伝票番号(VoucerDetail->voucher_id)をセット
            $_SESSION['VcrUpdNo'] = $_POST['VcrUpdateNo'];     //セッションにVcrUpdNoを保存 リダイレクト時、Dtoで復元される
            $Dto->VcrListResult   = $_SESSION['VcrListResult'] ?? []; //検索結果をセッションから復元
            foreach ($Dto->VcrListResult as $no0 => $value0) {
                if (isset($value0['voucher_id']) && 
                    $value0['voucher_id'] == $Dto->VcrUpdNo &&
                    isset($value0['JdId']))   {   //修正対象伝票のデータだけを$VoucherDto->VcrSearchedDataに格納
                    $Dto->VcrSearchedData[$LineNo] = $value0;
                    $LineNo++;                          //編集用データ$VoucherDto->VcrSearchedDataの行番号を0から振り直すための変数
                    if($value0['side'] === 'credit'){
                        $CreditTotal += (int)$value0['amount'];
                    }else{
                        $DebitTotal  += (int)$value0['amount'];
                    }
                }
            }
            $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData;//修正用データをセッションに保存
            if( $CreditTotal !== $DebitTotal ){
                $Dto->ErrData['VoucherService'] = "貸方合計　¥{$CreditTotal}　借方合計　¥{$DebitTotal}　不一致です。";
            }
    }

//行追加・行削除ボタンを押したときの処理
    public function VcrAddDebit(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {

        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元
        $this->VcrSearchedDataRemake($Dto , $Repo, $Validator);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1
        $NewId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';
        $Side = 'debit';
        $this->VcrAddRowIns( $Dto, $NewVcrRowAddr, $NewId, $Side);
        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    public function VcrAddCredit(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        //
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; //行追加前のデータをセッションから復元
        $this->VcrSearchedDataRemake($Dto , $Repo, $Validator);

        $_SESSION['UnsavedData'] = true; //追加行を作成した場合は、保存されるまで、次回の行追加・行削除をできないようにするフラグ
                                         //このフラグは保存処理の最後でfalseにする
        $Dto->VcrListResult = $_SESSION['VcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $NewVcrRowAddr = (int)$_POST['VcrAddCredit'] + 1;
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行追加後の行番号をDtoに保存　行追加後の行番号は、行追加前の行番号+1
        $NewId = $_SESSION['VcrSearchedData'][0]['voucher_id'] ?? '';
        $Side = 'credit';
        $this->VcrAddRowIns( $Dto, $NewVcrRowAddr, $NewId, $Side);
        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    public function VcrDetailLineDel(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        $Dto->VcrListResult = $_SESSION['VcrListResult'] ?? []; //検索結果をセッションから復元 simplesearch(右側)エリア表示用
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ; //行追加前のデータをセッションから復元
        $this->VcrSearchedDataRemake($Dto , $Repo, $Validator);

        foreach ($Dto->VcrSearchedData as $idx => $row) {
        }
        if($idx < 1) {
            $Dto->ErrData['VoucherService.VcrDetailLineDel'] = "最終行は削除できません。伝票を削除するには、伝票削除のボタンを押してください。";
            return;
        }

        $NewVcrRowAddr = (int)$_POST['VcrDetailLineDel'];
        $NewId = $_POST['id'] ?? '';
        $_SESSION['NewVcrRowAddr'] = $NewVcrRowAddr; //行削除後の行番号をDtoに保存

        array_splice($Dto->VcrSearchedData, $NewVcrRowAddr, 1);
        $Dto->VcrSearchedData = array_values($Dto->VcrSearchedData ); // インデックスを並べ直す     saveVoucher(array $data)

        $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData; // 左側を保存

        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    private function VcrAddRowIns(VoucherDTO $Dto, $NewVcrRowAddr, $NewId, $Side): void {
        // 1. セッションからデータを復元
        $Dto->VcrListResult = $_SESSION['VcrListResult'] ?? []; 
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ?? []; 


        // -------------------------------------------------------------
        // 【仕様対応】新しく挿入する「空の箱（明細行）」を作成
        // -------------------------------------------------------------
        $NewJdId = (int)($_POST['JdId'] ?? 0);
        $NewRow = [
            'id'            => $NewId, 
            'JdId'          => $NewJdId, 
            'voucher_date'  => '', 
            'summary'       => '',
            'account_id'    => '', 
            'name'          => '',
            'type'          => '', 
            'side'          => $Side, 
            'amount'        => '0',
            'voucher_id'    => $NewId,
            'LineNo'       => (int)0,
            'CreditName'    => '', 
            'DebitName'     => '', 
            'debit_total'   => '', 
            'credit_total'  =>  '' 
        ];

        // -------------------------------------------------------------
        // 【仕様対応：左側】VcrSearchedData（修正対象1件）の指定位置に行を挿入
        // -------------------------------------------------------------
        array_splice($Dto->VcrSearchedData, $NewVcrRowAddr, 0, [$NewRow]); //行挿入
        $Dto->VcrSearchedData = array_values($Dto->VcrSearchedData); 

        $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData; // 左側を保存

        // -------------------------------------------------------------
        // 【仕様対応：右側】VcrListResult から、対象の voucher_id を一旦削除し、
        // 新しい $Dto->VcrSearchedData（複数行）を「元の位置」に正しく挿入する
        // -------------------------------------------------------------

        /////////////////////　　　　　add del 共通　　　　　　　　//////////////////////
        // ① 右側一覧の中から、今回修正する伝票(voucher_id)が「最初に現れる位置」を探す
        //$insertAddress = 0;
        //foreach ($Dto->VcrListResult as $idx => $row) {
        //    if ((int)$row['voucher_id'] === (int)$NewId) {
        //        $insertAddress = $idx; // 元の出現位置を記憶
        //        break;
        //    }
        //}

        //$filteredList = [];
        //$filteredList = array_filter($Dto->VcrListResult, function($row) use ($NewId) {
        //    return (int)$row['voucher_id'] !== (int)$NewId;
        //});
        //$filteredList = array_values($filteredList); // 

        //array_splice($filteredList, $insertAddress, 0, $Dto->VcrSearchedData);

        //$Dto->VcrListResult = array_values($filteredList);
        //$_SESSION['VcrListResult'] = $Dto->VcrListResult; 
    }

    public function VcrSearchedDataRemake(VoucherDTO $Dto , VoucherRepository $Repo, VoucherValidator $Validator): void {

        $NewCount = count($_SESSION['VcrSearchedData'] ?? []) -1; //行追加、行削除の前の行数をカウント　
        $AccountTbl  =      empty($Dto->AccountTbl) ? [] : $Dto->AccountTbl; //AccountTblがDTOにセットされていない場合は、Repoから取得して$AccountTblにセット　行追加・行削除の前の行数をカウント
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData']; //行追加・行削除の処理を行う前に、$Dto->VcrSearchedDataを初期化  VcrUpdDt
        for($idx = 0; $idx <= $NewCount; ) {
            foreach ($AccountTbl as $a) {
                if($a['id'] === (int)($_POST['VcrUpdDt'][$idx]['account_id'] ?? '0')) {
                    $AccountId = $a['id'];
                    $Name = $a['name'];
                    break;
                }
            }
            //echo $_POST['VcrUpdDt'][$idx]['account_id'] ?? '0';
            //echo "<pre>"; var_dump($AccountTbl); echo "</pre>";

            //$AccountId                                  = $_POST['VcrUpdDt'][$idx]['account_id'] ?? '0';
            $Dto->VcrSearchedData[$idx]['voucher_date'] = (string)$_SESSION['VcrSearchedData'][0]['voucher_date']?? '';
            $Dto->VcrSearchedData[$idx]['Jdid']         = (int)$_POST['VcrUpdDt'][$idx]['voucher_id'] ?? '0';
            $Dto->VcrSearchedData[$idx]['account_id']   = (int)$_POST['VcrUpdDt'][$idx]['account_id'] ?? '0';
            $Dto->VcrSearchedData[$idx]['side']         = (string)$_POST['VcrUpdDt'][$idx]['side'] ?? '';
            $Dto->VcrSearchedData[$idx]['amount']       = (int)$_POST['VcrUpdDt'][$idx]['amount'] ?? '';
            $Dto->VcrSearchedData[$idx]['voucher_id']   = (int)$_POST['VcrUpdDt'][$idx]['voucher_id'] ?? '0';
            $Dto->VcrSearchedData[$idx]['LineNo']       = (int)$idx;
            $Dto->VcrSearchedData[$idx]['name']         = $Name ?? '';
            //if($Dto->VcrSearchedData[$idx]['side'] === 'debit'){
            //    $Dto->VcrSearchedData[$idx]['name'] = (int)$Dto->VcrSearchedData[$idx]['DebitName'] ?? '';
            //}else{
            //    $Dto->VcrSearchedData[$idx]['name'] = (int)$Dto->VcrSearchedData[$idx]['CreditName'] ?? '';
            //}
            $idx++;
        }
        $_SESSION['VcrSearchedData'] =  $Dto->VcrSearchedData ?? []; //行追加のデータをセッションに保存
    }

    private function VcrRowAddCommon(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        $VcrSearchedData = $_SESSION['VcrSearchedData'];
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'];
        $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;
        $NewId = $_POST['id'] ?? '';
    }

    private function VcrTmpDataSave(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        $Dto->VcrSearchedData = array_values($Dto->VcrSearchedData); //インデックスを振り直す
        $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData;//行追加・行削除後のデータをセッションに保存
    }

    public function VcrDelete(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): bool {
        //requireCsrf();　　　　　//CSRFトークンの検証はコントローラーで行う
        $Dto->VcrUpdNo  =   $_SESSION['VcrUpdNo'] ?? 0;      //セッションにVcrUpdNoをDtoに保存
        $voucherId      =   $_SESSION['VcrUpdNo'] ?? 0;       //セッションから伝票番号を取得
        $Repo->delete($voucherId);

        return true;
    }
    public function VcrRowAdd($VcrDTO){
        $details = $_POST['details'] ?? [];
        $AddKey = (int)$_POST['add_row'] + 1; //追加する行の位置
        $AddRow = [['account_id' => '', 'amount' => '', 'side' => 'debit']]; //初期値は借方
        array_splice($details, $AddKey, 0, $AddRow);
        $VcrDTO->DtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }
            
    public function VcrRowDel($VcrDTO){
        $details = $_POST['details'] ?? [];
        $idx = (int)$_POST['delete_row'];
        unset($details[$idx]);
        $VcrDTO->DtoDetails = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
    }

    public function VcrSave($VcrDTO,$VcrValidator){
        if (empty($VcrDTO->ErrData)) {
//            $IndexCnt = count($VcrDTO->account_id) ?? 0;
            $this->Repo->insertVoucher($VcrDTO); 
        }
    }
}
