<?php
class VoucherValidator{

    private voucherDto $dto;


    private int $errno;
    public function __construct(voucherDto $dto)    {
        $this->dto = $dto;
        $this->errno = 0;
    }

    public function create(): void
    {
        $_SESSION['dtoDetail'] = $this->dto->dtoDetails; //エラー発生時、入力データを復元するためにPOSTされた明細行をセッションに保存する。
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $_SERVER['REQUEST_URI'];
        $OwnUrl = $protocol . $OwnUrl;
        //var_dump($this->dto->session['currentShopCode'] ?? 'xxxxxxxxxxxxxxx');
        if ((trim($this->dto->session['currentShopCode'] ?? '')) === 'all') {
            $this->dto->errData[$OwnUrl] = '仕分け伝票作成で全店は選択できません。店舗を選択してください。';
            return;
        }

        if (empty($this->dto->date)) {
            $this->dto->errData[$OwnUrl] = '日付は必須です';
            return;
        }

        if (empty($this->dto->summary)) {
            $this->dto->errData[$OwnUrl] = '摘要は必須です';
            return;
        }
        $debit = 0;
        $credit = 0;
        foreach ($this->dto->dtoDetails as $idx => $row) {

            if ($row['amount'] <= 0) {
                $this->dto->errData[$OwnUrl] = '金額は0より大きくしてください';
            }

            if (!in_array($row['side'], ['debit', 'credit'])) {
                $this->dto->errData[$OwnUrl] = '貸借区分が不正です';
                return;
            }

            if ($row['side'] === 'debit') {
                $debit += $row['amount'];
            } else {
                $credit += $row['amount'];
            }
        }
        $this->ChkTotalBalance($this->dto->dtoDetails ); //貸し借り不一致チェック


    }

    public function list(): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $_SERVER['REQUEST_URI'];
        $OwnUrl = $protocol . $OwnUrl;
        $this->dto->errData = [];
        $start  = $this->dto->vcrListDatePeriod['検索開始日付'] ?? '';   
        $end    = $this->dto->vcrListDatePeriod['検索終了日付'] ?? '';
        $date   = $this->dto->Date??'';   //###########################
        $_SESSION['listInputData'] = ['検索日付' => $date , '検索開始日付'=> $start , '検索終了日付' => $end ] ; //############################

    //    if (empty($this->dto->SearchType)) {
    //         $this->dto->errData[$OwnUrl] = '検索条件を選択してください';
    //         return;
    //    }
        
        if($this->dto->searchType === 'simpleSearch') {    
//          日付期間のチェック　未着手
            //var_dump($date,$start,$end);
            if (!empty($date) && (!empty($start) || !empty($end))) {
                $this->dto->errData[$OwnUrl] = '日付,検索期間は同時入力不可です。';
                return;
            }
            // 期間検索パラメータが渡されている場合、開始日と終了日の両方を必須とする
            //if(!empty($start) || !empty($end)){
            //echo "<br>start:{$start}  /  end:{$end}";
                if (empty($start) || empty($end)) {
                    $this->dto->errData[$OwnUrl] = '期間検索では開始日付・終了日付の両方を入力してください。';
                    return;
                }
            //}
        }
    }


//貸方、借方バランスチェック 引数の配列フォーマットは連想キー'amount','side'が含まれていたらどんなフォーマットでもOK
    public function ChkTotalBalance($chkTbl){
        //var_dump($chkTbl);
        $creditTotal = 0; $debitTotal = 0;
        foreach ($chkTbl as $no0 => $value0) {
                
                if($value0['side'] === 'credit'){
                    $creditTotal += (int)$value0['amount'];
                }else{
                    $debitTotal  += (int)$value0['amount'];
                }
        }
        if( $creditTotal !== $debitTotal ){
            $this->dto->errData['VoucherService'] = "貸方合計　¥{$creditTotal}　借方合計　¥{$debitTotal}　不一致です。";
            return(0); //false 貸し借り不一致
        }else{
            return(1); //true 貸し借り一致
        }

    }


}