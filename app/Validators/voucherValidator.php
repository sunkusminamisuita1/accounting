<?php
// app/validators/VoucherValidator.php
class VoucherValidator
{

    private int $errno;
    public function __construct()    {
        $this->errno = 0;
    }


    public function validate(VoucherDTO $dto): void
    {
        if (empty($dto->Date)) {
            $dto->ErrData['VoucherDto'] = '日付は必須です';
            //throw new Exception('日付は必須です');
        }

        if (empty($dto->Summary)) {
            $dto->ErrData['VoucherDto'] = '摘要は必須です';
            //throw new Exception('摘要は必須です');
        }

        $debit = 0;
        $credit = 0;
        foreach ($dto->DtoDetails as $idx => $row) {

            if ($row['amount'] <= 0) {
                $dto->ErrData['VoucherDto'] = '金額は0より大きくしてください';
                //throw new Exception('金額は0より大きくしてください');
            }

            if (!in_array($row['side'], ['debit', 'credit'])) {
                $dto->ErrData['VoucherDto'] = '貸借区分が不正です';
                //throw new Exception('貸借区分が不正です');
            }

            if ($row['side'] === 'debit') {
                $debit += $row['amount'];
            } else {
                $credit += $row['amount'];
            }
        }

        if ($debit !== $credit) {
            $dto->ErrData['VoucherDto'] = '借方と貸方が一致しません';
            //throw new Exception('借方と貸方が一致しません');
        }
    }
}
?>