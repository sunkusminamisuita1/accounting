<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/app/Validators/voucherValidator.php';

class VoucherController
{
    private VoucherService $service;
    private VoucherDTO $VoucherDto;
    private VoucherValidator $validator;

    public function __construct()  {
        $this->service = new VoucherService();
        $this->validator = new VoucherValidator();
    }
    public function create(): void    {
        $TokenKey  = generateCsrfToken();
        $this->service->InitializeSession();//voucher.createでは不要かも？
        //accountsテーブルから勘定科目を取得
        $accounts = $this->service->getAccounts();
        $this->VoucherDto = new VoucherDTO($_POST['details'] ?? []); //DTOにPOSTされた明細行を渡す
        $details = $this->VoucherDto->DtoDetails; //DTOから明細行を取得
        print_r($details); echo "date: " . $this->VoucherDto->Date . "<br>";// デバッグ用
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            requireCsrf();
            if (isset($_POST['add_row'])) {  
                $this->service->VcrRowAdd($this->VoucherDto);
            //    $details = $_POST['details'] ?? [];
            //    $AddKey = (int)$_POST['add_row'] + 1; //追加する行の位置
            //    $AddRow = [['account_id' => '', 'amount' => '', 'side' => 'debit']]; //初期値は借方
            //    array_splice($details, $AddKey, 0, $AddRow);
            //    $details = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
            //    $VoucherDto->DtoDetails = $details;
            }
            // 
            if (isset($_POST['delete_row'])) {
                $this->service->VcrRowDel($this->VoucherDto);
            //     $idx = (int)$_POST['delete_row'];
            //     unset($details[$idx]);
            //     print_r($details); echo "<br>";// デバッグ用
            //     $details = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
            }

            if (isset($_POST['save'])) {
                $this->service->VcrSave($this->VoucherDto,$this->validator);
            //    $this->VoucherDto = new VoucherDTO($details);
            //    $this->validator->validate($this->VoucherDto);
            //    if (empty($this->VoucherDto->ErrData)) {
            //        $this->service->saveVoucher($this->VoucherDto);
            //    }
            }
            $details = $this->VoucherDto->DtoDetails; //DTOから明細行を再取得
        
        }
        require ROOT_PATH . '/views/voucher/create.php';
    }
    
    public function store(): void {
        requirePost();
        $data = $_POST;
        
        foreach ($_POST['details'] as $i => $d) {
            $details[] = [
                'account_id' => (int)$d['account_id'],
                'amount' => (int)$d['amount'],
                'side' => $d['side'],
                'line_no' => $i
            ];
        }

        $data = [
            'date' => $_POST['voucher_date'],
            'summary' => $_POST['summary'],
            'user_id' => getLoginUserId(),
            'details' => $details
        ];

        $this->service->saveVoucher($data);

        header('Location: index.php?route=voucher.index');
        exit;
    }
        // 一覧
    public function index() {
        $userId = getLoginUserId();
        $vouchers = $this->service->list($userId);
        //print_r($vouchers);
        require ROOT_PATH.'/views/voucher/index.php';
    }

    // 編集画面
    public function edit(){
        $id = (int)($_GET['id'] ?? 0);
        $voucher = $this->service->find($id);
        require ROOT_PATH.'/views/voucher/edit.php';
    }

    // 更新
    public function update() {
        requirePost();
        $id = (int)$_POST['id'];
        $data = [
            'date' => $_POST['date'],
            'summary' => $_POST['summary']
        ];
        $this->service->update($id, $data);
        header('Location: index.php?route=voucher.index');
        exit;
    }

    // 削除
    public function delete(){
        $id = (int)($_GET['id'] ?? 0);
        $this->service->delete($id);
        header('Location: index.php?route=voucher.index');
        exit;
    }
}
