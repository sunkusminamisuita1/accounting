<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/app/Validators/voucherValidator.php';

class VoucherController
{
    private VoucherService $Service;
    private VoucherDTO $VoucherDto;
    private VoucherValidator $validator;

    public function __construct()  {
        $this->Service = new VoucherService();
        $this->validator = new VoucherValidator();
    }
    public function create(): void    {
        $TokenKey  = generateCsrfToken();
        $this->Service->InitializeSession();//voucher.createでは不要かも？
        //accountsテーブルから勘定科目を取得
        $accounts = $this->Service->getAccounts();
        $this->VoucherDto = new VoucherDTO($_POST['details'] ?? []); //DTOにPOSTされた明細行を渡す
        $details = $this->VoucherDto->DtoDetails; //DTOから明細行を取得
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
            requireCsrf();
            if (isset($_POST['add_row'])) {  
                $this->Service->VcrRowAdd($this->VoucherDto);
            }
            if (isset($_POST['delete_row'])) {
                $this->Service->VcrRowDel($this->VoucherDto);
            }

            if (isset($_POST['save'])) {
                $this->Service->VcrSave($this->VoucherDto,$this->validator);
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

        $this->Service->saveVoucher($data);

        header('Location: index.php?route=voucher.index');
        exit;
    }
        // 一覧
    public function index() {
        $userId = getLoginUserId();
        $vouchers = $this->Service->list($userId);
        //print_r($vouchers);
        require ROOT_PATH.'/views/voucher/index.php';
    }

    // 編集画面
    public function edit(){
        $id = (int)($_GET['id'] ?? 0);
        $voucher = $this->Service->find($id);
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
        $this->Service->update($id, $data);
        header('Location: index.php?route=voucher.index');
        exit;
    }

    // 削除
    public function delete(){
        $id = (int)($_GET['id'] ?? 0);
        $this->Service->delete($id);
        header('Location: index.php?route=voucher.index');
        exit;
    }
}
