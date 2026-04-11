<?php
// /test/DTO/VoucherDTO.php

class VAddDTO {
    public string $voucherDate;
    public string $side;
    public ?int $accountId;
    public string $amount;
    public string $summary;

    public function __construct(string $voucherDate, string $side, ?int $accountId, string $amount, string $summary) {
        $this->voucherDate = $voucherDate;
        $this->side = $side;
        $this->accountId = $accountId;
        $this->amount = $amount;
        $this->summary = $summary;
    }
}

class VCreateDTO {
    public array $voucherRows;
    public int $debitAmountTotal;
    public int $creditAmountTotal;
    public bool $isBalanced;

    public function __construct(array $voucherRows, int $debitAmountTotal, int $creditAmountTotal, bool $isBalanced) {
        $this->voucherRows = $voucherRows;
        $this->debitAmountTotal = $debitAmountTotal;
        $this->creditAmountTotal = $creditAmountTotal;
        $this->isBalanced = $isBalanced;
    }
}

class VDeleteDTO {
    public string $action; // 'clear' or 'alt'
    public ?array $deleteKeys; // for alt action
    public ?int $updateKey; // for update in alt

    public function __construct(string $action, ?array $deleteKeys = null, ?int $updateKey = null) {
        $this->action = $action;
        $this->deleteKeys = $deleteKeys;
        $this->updateKey = $updateKey;
    }
}
?>