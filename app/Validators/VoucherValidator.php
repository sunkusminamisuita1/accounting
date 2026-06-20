<?php
class VoucherValidator
{

    private int $errno;
    public function __construct()    {
        $this->errno = 0;
    }

    public function Create(VoucherDTO $dto): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $_SERVER['REQUEST_URI'];
        $OwnUrl = $protocol . $OwnUrl;
        if (empty($dto->Date)) {
            $dto->ErrData[$OwnUrl] = '日付は必須です';
            return;
        }

        if (empty($dto->Summary)) {
            $dto->ErrData[$OwnUrl] = '摘要は必須です';
            return;
        }
        $debit = 0;
        $credit = 0;
        foreach ($dto->DtoDetails as $idx => $row) {

            if ($row['amount'] <= 0) {
                $dto->ErrData[$OwnUrl] = '金額は0より大きくしてください';
            }

            if (!in_array($row['side'], ['debit', 'credit'])) {
                $dto->ErrData[$OwnUrl] = '貸借区分が不正です';
                return;
            }

            if ($row['side'] === 'debit') {
                $debit += $row['amount'];
            } else {
                $credit += $row['amount'];
            }
        }
        $this->ChkTotalBalance($dto,$dto->DtoDetails ); //貸し借り不一致チェック


    }

    public function List(VoucherDTO $dto): void
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $_SERVER['REQUEST_URI'];
        $OwnUrl = $protocol . $OwnUrl;
        $dto->ErrData = [];
        $Start  = $dto->VcrListDatePeriod['検索開始日付'] ?? '';
        $End    = $dto->VcrListDatePeriod['検索終了日付'] ?? '';
        $Date   = $dto->Date??'';   //###########################
        $_SESSION['ListInputData'] = ['検索日付' => $Date , '検索開始日付'=> $Start , '検索終了日付' => $End ] ; //############################

    //    if (empty($dto->SearchType)) {
    //         $dto->ErrData[$OwnUrl] = '検索条件を選択してください';
    //         return;
    //    }
        
        if($dto->SearchType === 'SimpleSearch') {    
//          日付期間のチェック　未着手
            //var_dump($Date,$Start,$End);
            if (!empty($Date) && (!empty($Start) || !empty($End))) {
                $dto->ErrData[$OwnUrl] = '日付,検索期間は同時入力不可です。';
                return;
            }
            // 期間検索パラメータが渡されている場合、開始日と終了日の両方を必須とする
            if(!empty($Start) || !empty($End)){
                if (empty($Start) || empty($End)) {
                    $dto->ErrData[$OwnUrl] = '期間検索では開始日付・終了日付の両方を入力してください。';
                    return;
                }
            }
        }
    }


//貸方、借方バランスチェック 引数の配列フォーマットは連想キー'amount','side'が含まれていたらどんなフォーマットでもOK
    public function ChkTotalBalance($Dto, $ChkTbl){
        //var_dump($ChkTbl);
        $CreditTotal = 0; $DebitTotal = 0;
        foreach ($ChkTbl as $no0 => $value0) {
                
                if($value0['side'] === 'credit'){
                    $CreditTotal += (int)$value0['amount'];
                }else{
                    $DebitTotal  += (int)$value0['amount'];
                }
        }
        if( $CreditTotal !== $DebitTotal ){
            $Dto->ErrData['VoucherService'] = "貸方合計　¥{$CreditTotal}　借方合計　¥{$DebitTotal}　不一致です。";
            return(0); //false 貸し借り不一致
        }else{
            return(1); //true 貸し借り一致
        }

    }


}