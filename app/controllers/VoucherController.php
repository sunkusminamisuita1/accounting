<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';

class VoucherController
{
    private VoucherService $Service;
    private VoucherDTO $VoucherDto;

    public function __construct()  {
        $this->Service = new VoucherService();
    }

    public function create(): void    {
        $TokenKey  = generateCsrfToken();
        $accounts = $this->Service->getAccounts();
        $this->VoucherDto = new VoucherDTO($_POST['details'] ?? []); //DTOにPOSTされた明細行を渡す
        $details = $this->VoucherDto->DtoDetails; //DTOから明細行を取得
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->Service->VcrCreate($this->VoucherDto);
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
        require ROOT_PATH.'/views/voucher/index.php';
    }

    // 修正、削除データ検索
    public function list() {
        $TokenKey  = generateCsrfToken();
        $this->VoucherDto = new VoucherDTO($_POST['details'] ?? []);

        $accounts = $this->Service->getAccounts();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->Service->VcrList($this->VoucherDto);
        }
        require ROOT_PATH.'/views/voucher/list.php';
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