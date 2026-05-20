<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';

class VoucherController
{
    private VoucherService $Service;
    private VoucherDTO $Dto;
    private VoucherRepository $Repo;
    public function __construct()  {
        $this->Service = new VoucherService();
        $this->Repo = new VoucherRepository();
    }
    public function create(): void    {
        $TokenKey  = generateCsrfToken();
        $this->Dto = new VoucherDTO($_POST['details'] ?? []); //DTOにPOSTされた明細行を渡す
        $details = $this->Dto->DtoDetails; //DTOから明細行を取得
        $Accounts = $this->Repo->getAccounts();
        $this->Dto->Accounts = $Accounts;
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->Service->VcrCreate($this->Dto);
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
        $this->Dto = new VoucherDTO($_POST['details'] ?? []);

        $accounts = $this->Service->getAccounts();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->Service->VcrList($this->Dto);
        }
        $VcrListResult = $this->Dto->VcrListResult;
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