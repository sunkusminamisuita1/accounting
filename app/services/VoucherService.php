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

    public function addEntry($VoucherDto): void {
        $this->initializeSession();
        $voucherDate = $VoucherDto->Date;
        $side = $VoucherDto->side ?? '';
        $accountId = isset($VoucherDto->accountId) ? (int)$VoucherDto->accountId : 9999;
        $amount = $VoucherDto->amount ?? 0;
        $summary = $VoucherDto->Summary ?? '';
        $accountName = $this->resolveAccountName($accountId);
        if ($accountId !== 9999 && $accountId > 0) {
            $_SESSION['voucherRows'][$_SESSION['slipNum']] = [
                'date' => $voucherDate,
                'side' => $side,
                'accountId' => $accountId,
                'accountName' => $accountName,
                'amount' => $amount,
                'summary' => $summary,
            ];
            $_SESSION['slipNum']++;
        }
        $this->recalculateTotals();
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

    public function VcrCreate($VoucherDto){
        requireCsrf();
        $accounts = $this->getAccounts();
        if (isset($_POST['add_row'])) {  
            $this->VcrRowAdd($VoucherDto);
        }
        if (isset($_POST['delete_row'])) {
            $this->VcrRowDel($VoucherDto);
        }
        if (isset($_POST['save'])) {
            $this->Validator->Create($VoucherDto);
            $this->VcrSave($VoucherDto,$this->Validator);
            if(empty($VoucherDto->ErrData)) {
                $VoucherDto->InitDetailsDto(); //保存成功後、DTOの明細行を初期化
                $VoucherDto->ErrData = ['VoucherService' => '保存が完了しました'];
            }
        }
    }

    public function VcrList($VoucherDto){
//修正伝票検索エリアのロジック
        requireCsrf();
        $AccountTbl = $this->getAccounts();
        $VoucherDto->AccountTbl = $AccountTbl;
        $VoucherDto->VcrSearchedData = [];
        $VoucherDto->List(); //DTOのListメソッドで検索条件をセット
        if (isset($_POST['SimpleSearch'])) {  
            $VoucherDto->List(); //DTOのListメソッドで検索条件をセット
            $this->Validator->list($VoucherDto);
//////////////////////////このif追加
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
//修正エリアのロジック
        if (isset($_POST['VcrUpdateNo'])) {
            $VoucherDto->VcrListResult = $_SESSION['VoucherDetail'];
            $VoucherDto->VcrUpdNo =  $_POST['VcrUpdateNo'] ?? 0;
            //foreach ($VoucherDto->VcrListResult as $no0 => $value0){
            //    //echo "<br>RecNo={$no0}";
            //    foreach($value0 as $no1 => $value1){
            //        //echo "　{$no1} = {$value1}";
            //        //if ($value1 == $VoucherDto->VcrUpdNo){ echo "ok<br>";   } 
            //        if( ($no1 === 'voucher_id') && ($value1 == $VoucherDto->VcrUpdNo) ){
            //            $VcrSearchedData[$no0] = $value0;
            //            //echo "<br>value0=";var_dump($VcrSearchedData);
            //        }
            //    }
            //}


            foreach ($VoucherDto->VcrListResult as $no0 => $value0) {
                if (isset($value0['voucher_id']) && 
                    $value0['voucher_id'] == $VoucherDto->VcrUpdNo &&
                    isset($value0['JdId'])) {
                    $VoucherDto->VcrSearchedData[$no0] = $value0;
                }
            }


            //echo $_POST['VcrUpdateNo'];//デバッグ
            foreach ($VoucherDto->VcrSearchedData as $no0 => $value0){
                echo "<br>RecNo={$no0}";//デバッグ
                foreach($value0 as $no1 => $value1){
                    echo "　{$no1} = {$value1}";//デバッグ
                }
            }

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
            $IndexCnt = count($VcrDTO->account_id) ?? 0;
            $this->Repo->insertVoucher($VcrDTO); 
        }
    }
}
