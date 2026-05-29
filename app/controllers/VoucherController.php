<?php
require_once ROOT_PATH . '/app/services/VoucherService.php';
require_once ROOT_PATH . '/app/DTO/VoucherDTO.php';
require_once ROOT_PATH . '/lib/helpers.php';


class VoucherController
{
    private VoucherService $Service;
    private VoucherDTO $Dto;
    private VoucherRepository $Repo;
    private ErrMsgPopUp $ErrMsgPopUp;
    public function __construct()  {
        $this->Service = new VoucherService();
        $this->Repo = new VoucherRepository();
        $this->ErrMsgPopUp = new ErrMsgPopUp();
    }
    public function create(): void    {
        file_put_contents('/var/www/html/test6/public/debug.log', "メソッド通ったよ！\n", FILE_APPEND);
        //file_put_contents(__DIR__ . '/../../debug.log', "メソッド通ったよ！\n", FILE_APPEND);

        $TokenKey  = generateCsrfToken();
        $this->Dto = new VoucherDTO($_POST['details'] ?? []); //DTOにPOSTされた明細行を渡す
        $details = $this->Dto->DtoDetails; //DTOから明細行を取得
        $Accounts = $this->Repo->getAccounts();
        $this->Dto->Accounts = $Accounts;
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){


        foreach($details as $key => $value){
            echo "<br>key={$key}";
            foreach($value as $key1 => $value1){
                echo "連想key={$key1} => 値 ={$value1}";
            }
        }

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
        //print_r($_SESSION['VcrSearchedData']);
        $TokenKey  = generateCsrfToken();
        $this->Dto = new VoucherDTO($_POST['details'] ?? []);
        $accounts = $this->Service->getAccounts();
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->Service->VcrList($this->Dto);
        }
        $VcrListResult = $this->Dto->VcrListResult;
        $VcrSearchedData =  $this->Dto->VcrSearchedData;


            foreach ($this->Dto->VcrSearchedData as $no0 => $value0){
                echo "<br>RecNo={$no0}";//デバッグ
                foreach($value0 as $no1 => $value1){
                    echo "　{$no1} = {$value1}";//デバッグ
                }
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