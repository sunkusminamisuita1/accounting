<?php
// app/validators/VoucherValidator.php
class VoucherValidator
{
    public function validate(VoucherDTO $dto): void
    {
        if (empty($dto->Date)) {
            throw new Exception('日付は必須です');
        }

        if (empty($dto->Summary)) {
            throw new Exception('摘要は必須です');
        }

        $debit = 0;
        $credit = 0;
        foreach ($dto->DtoDetails as $idx => $row) {

            if ($row['amount'] <= 0) {
                throw new Exception('金額は0より大きくしてください');
            }

            if (!in_array($row['side'], ['debit', 'credit'])) {
                throw new Exception('貸借区分が不正です');
            }

            if ($row['side'] === 'debit') {
                $debit += $row['amount'];
            } else {
                $credit += $row['amount'];
            }
        }

        if ($debit !== $credit) {
            throw new Exception('借方と貸方が一致しません');
        }
    }
}
?>