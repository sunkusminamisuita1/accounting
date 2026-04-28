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
        $details = [];
        $this->service->InitializeSession();//voucher.createでは不要かも？
        //accountsテーブルから勘定科目を取得
        $accounts = $this->service->getAccounts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            requireCsrf();
             if (isset($_POST['add_row'])) {
                $details[] = [
                    'account_id' => '',
                    'amount' => '',
                    'side' => ''
                ];
            }
            
             if (isset($_POST['delete_row'])) {
                $idx = (int)$_POST['delete_row'];
                unset($details[$idx]);
                $details = array_values($details); // インデックスを並べ直す     saveVoucher(array $data)
            }

            foreach ($_POST['details'] as $i => $d) {
                $details[] = [
                    'account_id' => (int)$d['account_id'],
                    'amount' => (int)$d['amount'],
                    'side' => $d['side'],
                    'line_no' => $i
                ];
            }

            if (isset($_POST['save'])) {
                    $this->VoucherDto = new VoucherDTO($details);
                    $this->validator->validate($this->VoucherDto);
                    if (!empty($this->VoucherDto->ErrData)) {
                    //    foreach ($this->VoucherDto->ErrData as $mod => $err) {
                    //        echo h($mod) . ": " . h($err) . "<br>";
                    //    }
                    } else {
                        $this->service->saveVoucher($this->VoucherDto);
                    }
            }        
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
        print_r($vouchers);
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
