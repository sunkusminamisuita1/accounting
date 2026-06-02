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

//    public function delete(int $id) {
//        $this->Repo->delete($id);
//    }

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
            //$VoucherDto->List(); //DTOのListメソッドで検索条件をセット
            $this->Validator->list($Dto);
            if(empty($Dto->ErrData)){
                $VcrListResult = $this->Repo->VcrListSearch($Dto)??[];           
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
                $Dto->VcrListResult = empty($VcrListResult) ? [] : $VcrListResult;
                $_SESSION['VcrListResult'] = empty($VcrListResult) ? [] : $VcrListResult; //変数名上に合わしたほうがベター
            }        
    }
//修正エリアのロジック 
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
                        $CreditTotal += $value0['amount'];
                    }else{
                        $DebitTotal  += $value0['amount'];
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
        $NewVcrRowAddr = (int)$_POST['VcrAddDebit']  + 1;
        $NewId = $_POST['id'] ?? '';
        $Side = 'debit';
        $this->VcrAddRowIns( $Dto, $NewVcrRowAddr, $NewId, $Side);
        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    public function VcrAddCredit(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        $NewVcrRowAddr = (int)$_POST['VcrAddCredit'] + 1;
        $NewId = $_POST['id'] ?? '';
        $Side = 'credit';
        $this->VcrAddRowIns( $Dto, $NewVcrRowAddr, $NewId, $Side);
        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    public function VcrDetailLineDel(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ; //行追加前のデータをセッションから復元
        $NewVcrRowAddr = (int)$_POST['VcrDetailLineDel'];
        array_splice($Dto->VcrSearchedData, $NewVcrRowAddr, 1);
        $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData; //行削除後のデータをセッションに保存
        $Dto->VcrSearchedData = array_values($Dto->VcrSearchedData ); // インデックスを並べ直す     saveVoucher(array $data)
        $Dto->VcrListResult   = $_SESSION['VcrListResult'] ?? []; //viewデータ用をセッションから復元
        $this->VcrTmpDataSave($Dto, $Repo, $Validator);
    }

    private function VcrAddRowIns(VoucherDTO $Dto , $NewVcrRowAddr, $NewId, $Side): void {
        //file_put_contents('/var/www/html/test6/public/debug.log', var_dump($Dto->VcrSearchedData)."VcrAddRowIns！\n", FILE_APPEND);
        $NewJdId = (int)$_POST['JdId'] ?? 0;
        $NewRow = [
                'id' => $NewId , 'JdId' => $NewJdId , 'voucher_date' => '' , 'summary' => '',
                'account_id' => '' , 'name' => '' , 'type' => '' , 'side' => $Side , 'amount' => '0' ,
                'summary' => '' , 'voucher_id' => $NewId , 'debit_total' => '' , 'credit_total' => '' , 
                  ];
        $Dto->VcrSearchedData = $_SESSION['VcrSearchedData'] ; //行追加前のデータをセッションから復元
        array_splice($Dto->VcrSearchedData , $NewVcrRowAddr , 0, [$NewRow]); //行挿入
        $Dto->VcrSearchedData = array_values($Dto->VcrSearchedData ); // インデックスを並べ直す     saveVoucher(array $data)
        $Dto->VcrListResult   = $_SESSION['VcrListResult'] ?? []; //viewデータ用をセッションから復元
        $_SESSION['VcrSearchedData'] = $Dto->VcrSearchedData; //行追加後のデータをセッションに保存
    }

    private function VcdRowAddCommon(VoucherDTO $Dto, VoucherRepository $Repo, VoucherValidator $Validator): void {
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
        //$voucherId = (int)$_POST['VcrDeleteNo'];
        $Repo->delete($voucherId);
//        $pdo = getPDO();
//        try {
//            $pdo->beginTransaction();

//            // 伝票IDに紐づく仕訳明細を削除
//            $stmtDetail = $pdo->prepare("DELETE FROM journal_details WHERE voucher_id = ?");
//            $stmtDetail->execute([$voucherId]);
//
//            // 伝票を削除
//            $stmtVoucher = $pdo->prepare("DELETE FROM journal_vouchers WHERE id = ?");
//            $stmtVoucher->execute([$voucherId]);

//            $pdo->commit();
//            return true;
//        } catch (Exception $e) {
//            $pdo->rollBack();
//            throw $e;
//        }
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
