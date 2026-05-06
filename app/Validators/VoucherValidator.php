<?php
class VoucherValidator
{

    private int $errno;
    public function __construct()    {
        $this->errno = 0;
    }


    public function Validate(VoucherDTO $dto): void
    {
        if (empty($dto->Date)) {
            $dto->ErrData['VoucherDto'] = '日付は必須です';
            return;
        }

        if (empty($dto->Summary)) {
            $dto->ErrData['VoucherDto'] = '摘要は必須です';
            return;
        }
        $debit = 0;
        $credit = 0;
        foreach ($dto->DtoDetails as $idx => $row) {

            if ($row['amount'] <= 0) {
                $dto->ErrData['VoucherDto'] = '金額は0より大きくしてください';
            }

            if (!in_array($row['side'], ['debit', 'credit'])) {
                $dto->ErrData['VoucherDto'] = '貸借区分が不正です';
                return;
            }

            if ($row['side'] === 'debit') {
                $debit += $row['amount'];
            } else {
                $credit += $row['amount'];
            }
        }

        if ($debit !== $credit) {
            $dto->ErrData['VoucherDto'] = '借方と貸方が一致しません';
            return;
        }
    }
}