<?php
// app/validators/VoucherValidator.php
class VoucherValidator
{
    public function validate(VoucherDTO $dto): void
    {
        if (empty($dto->date)) {
            throw new Exception('日付は必須です');
        }

        if (empty($dto->details)) {
            throw new Exception('明細がありません');
        }

        $debit = 0;
        $credit = 0;

        foreach ($dto->details as $d) {

            if ($d->amount <= 0) {
                throw new Exception('金額は0より大きくしてください');
            }

            if (!in_array($d->side, ['debit', 'credit'])) {
                throw new Exception('貸借区分が不正です');
            }

            if ($d->side === 'debit') {
                $debit += $d->amount;
            } else {
                $credit += $d->amount;
            }
        }

        if ($debit !== $credit) {
            throw new Exception('借方と貸方が一致しません');
        }
    }
}
?>