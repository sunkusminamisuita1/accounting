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

    public function delete(int $id) {
        $this->Repo->delete($id);
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

    public function getVoucherRows(): array {
        return $_SESSION['voucherRows'] ?? [];
    }

    public function getEditData(): array{
        return $_SESSION['editData'] ?? [];
    }

    public function getTotals(): array{
        return [
            'debitAmountTotal' => $_SESSION['debitAmountTotal'] ?? 0,
            'creditAmountTotal' => $_SESSION['creditAmountTotal'] ?? 0,
        ];
    }

    public function deleteRows(array $keys): void {
        foreach ($keys as $key) {
            unset($_SESSION['voucherRows'][(int)$key]);
        }
        $this->recalculateTotals();
    }

    public function setEditRow(int $key): void{
        if (isset($_SESSION['voucherRows'][$key])) {
            $_SESSION['editData'] = $_SESSION['voucherRows'][$key];
            unset($_SESSION['voucherRows'][$key]);
            $this->recalculateTotals();
        }
    }

    public function clearEntries(): void {
        unset($_SESSION['voucherRows']);
        unset($_SESSION['slipNum']);
        unset($_SESSION['editData']);
        unset($_SESSION['debitAmountTotal']);
        unset($_SESSION['creditAmountTotal']);
    }

    private function resolveAccountName(int $accountId): string {
        foreach ($this->getAccounts() as $account) {
            if ($account['id'] === $accountId) {
                return $account['name'];
            }
        }
        return '';
    }

    private function buildDetails(array $rows, string $side): array{
        $items = [];
        foreach ($rows as $row) {
            if ($row['side'] === $side) {
                $items[] = [
                    'account_id' => $row['accountId'],
                    'amount' => (int)$row['amount'],
                    'side' => $side === '借方' ? 'debit' : 'credit',
                ];
            }
        }
        return $items;
    }

    private function recalculateTotals(): void{
        $debit = 0;
        $credit = 0;
        foreach ($_SESSION['voucherRows'] ?? [] as $row) {
            if ($row['side'] === '貸方') {
                $credit += (int)$row['amount'];
            } else {
                $debit += (int)$row['amount'];
            }
        }
        $_SESSION['debitAmountTotal'] = $debit;
        $_SESSION['creditAmountTotal'] = $credit;
    }

    public function VcrCreate($Dto){
        requireCsrf();
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

    public function VcrList($VoucherDto){
//修正伝票検索エリアのロジック
        requireCsrf();
        $AccountTbl = $this->getAccounts();
        $VoucherDto->AccountTbl = $AccountTbl;
        $VoucherDto->VcrSearchedData = $VoucherDto->InitVcrSearchedData ;
        $VoucherDto->List(); //DTOのListメソッドで検索条件をセット
        if (isset($_POST['SimpleSearch'])) {  
            //$VoucherDto->List(); //DTOのListメソッドで検索条件をセット
            $this->Validator->list($VoucherDto);
            if(empty($VoucherDto->ErrData)){
                $VcrListResult = $this->Repo->VcrListSearch($VoucherDto)??[];           
                foreach($VcrListResult as $idx => $row) {
                    foreach ( $row as $key => $value) {
                        $VcrListResult[$idx][$key]=$value;
                        if(empty($VcrListResult[$idx]['voucher_id']))  {
                            $VcrListResult[$idx]['voucher_id']='999999999999';
                        }else {
                            $VcrListResult[$idx][$key]=$value;
                        }
                    }
                }
                $VoucherDto->VcrListResult = $VcrListResult;
                $_SESSION['VoucherDetail'] = $VcrListResult;
            }        
        }
                //$VoucherDto->VcrListResult = $_SESSION['VoucherDetail'] ?? [];
//修正エリアのロジック 
                    //修正ボタンを押したとき修正データ作成 $VoucherDto->VcrSearchedData
        if (isset($_POST['VcrUpdateNo'])) {
            $VoucherDto->VcrUpdNo =  $_POST['VcrUpdateNo'] ?? 0; //VcrUpdNoに伝票番号(VoucerDetail->voucher_id)をセット
            #_SESSION['VcrUpdNo'] = $VoucherDto->VcrUpdNo; //セッションにVcrUpdNoを保存 リダイレクト時、Dtoで復元される
            $CreditTotal = 0;$DebitTotal = 0; $LineNo = 0;
            foreach ($VoucherDto->VcrListResult as $no0 => $value0) {
                if (isset($value0['voucher_id']) && 
                    $value0['voucher_id'] == $VoucherDto->VcrUpdNo &&
                    isset($value0['JdId'])) 
                {
                    $VoucherDto->VcrSearchedData[$LineNo] = $value0;
                    $LineNo++;//編集用データ$VoucherDto->VcrSearchedDataの行番号を0から振り直すための変数
                    if($value0['side'] === 'credit'){
                        $CreditTotal += $value0['amount'];
                    }else{
                        $DebitTotal  += $value0['amount'];
                    }
                }
            }
            $_SESSION['VcrSearchedData'] = $VoucherDto->VcrSearchedData;//修正用データをセッションに保存
            if( $CreditTotal !== $DebitTotal ){
                $VoucherDto->ErrData['VoucherService'] = "貸方合計　¥{$CreditTotal}　借方合計　¥{$DebitTotal}　不一致です。";
            }
        }

//行追加・行削除ボタンを押したときの処理
            if(isset($_POST['VcrAddDebit'])  ||
               isset($_POST['VcrAddCredit']) ||
               isset($_POST['VcrDetailLineDel']) ){
                $VcrSearchedData = $_SESSION['VcrSearchedData'];
                $VoucherDto->VcrSearchedData = $_SESSION['VcrSearchedData'];
                if(isset($_POST['VcrAddDebit'])){   //借方 追加行の行番号を取得
                    $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;
                    $NewId = $_POST['id'] ?? '';
                    $Side = 'debit';
                }
                if(isset($_POST['VcrAddCredit'])){  //貸方 追加行の行番号を取得
                    $NewVcrRowAddr = (int)$_POST['VcrAddCredit'] + 1;
                    $NewId = $_POST['id'] ?? '';
                    $Side = 'credit';
                }
                if(!isset($_POST['VcrDetailLineDel'])){ //借方、貸方　共通設定項目
                    $NewJdId = (int)$_POST['JdId'] ?? 0;
                    $NewRow = [
                            'id' => $NewId , 'JdId' => $NewJdId , 'voucher_date' => '' , 'summary' => '',
                            'account_id' => '' , 'name' => '' , 'type' => '' , 'side' => $Side , 'amount' => '0' ,
                            'summary' => '' , 'voucher_id' => $NewId , 'debit_total' => '' , 'credit_total' => '' , 
                              ];
                    array_splice($VoucherDto->VcrSearchedData , $NewVcrRowAddr , 0, [$NewRow]); //行挿入
                }else {                                            //行削除
                    $NewVcrRowAddr = (int)$_POST['VcrDetailLineDel'];
                    array_splice($VoucherDto->VcrSearchedData, $NewVcrRowAddr, 1);
                }
                $VoucherDto->VcrSearchedData = array_values($VoucherDto->VcrSearchedData); //インデックスを振り直す
                $_SESSION['VcrSearchedData'] = $VoucherDto->VcrSearchedData;//行追加・行削除後のデータをセッションに保存
            }
             echo "削除ロジック直前。";//デバッグ
            if(isset($_POST['VcrUpdate'])){
                    echo "削除ロジックupdateにはいった。";//デバッグ
                    file_put_contents(__DIR__ . '/../../debug.log', "vcrupdate！\n", FILE_APPEND);
                    //修正実行ボタンを押したとき　$VoucherDto->VcrUpdNoでテーブルjournal_detailから削除VcrDetailLineDel
                    //その後テーブルjournal_detailsに$voucherdto->vcrsearcheddataの内容をinsertする。
                    //journal_vouchersフォーマット
                    //| id  | voucher_date | summary   | user_id | created_at |
                    //journal_detailsのフォーマット 
                    //| id       | voucher_id | line_no | account_id | side   | amount   |
                    $this->Repo->VcrDelete($VoucherDto); //リポジトリのVcrDeleteメソッドで削除処理を呼び出す
                    //更新後、更新した伝票のデータを再検索して$VoucherDto->VcrSearchedDataにセットする。
                    //$VoucherDto->VcrSearchedData = $this->Repo->VcrListSearch($VoucherDto); //リポジトリのVcrListSearchメソッドで更新した伝票のデータを再検索して$VoucherDto->VcrSearchedDataにセットする。
                    //$_SESSION['VcrSearchedData'] = $VoucherDto->VcrSearchedData;//更新後のデータをセッションに保存
            
            }

                    //伝票削除ボタンを押したとき　voucherNoでテーブルjournal_vouchersから削除　CASCADEでjournal_detailsも削除されるはず
            if(isset($_POST['VcrDetailLineDel'])){
                echo "削除ロジックにはいった。";//デバッグ
                $VoucherDto->VcrDeleteNo = $_POST['VcrDetailLineDel'] ?? 0;
                $this->repo->delete($VoucherDto->VcrDeleteNo);//repo sql 呼び出す
                echo "削除完了しました。";//デバッグ
            }
            
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
