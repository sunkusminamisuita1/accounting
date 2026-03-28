<?php
// app/DTO/VoucherDTO.php
class VoucherDTO
{
    public string $date;
    public string $summary;
    public int $userId;
    public array $details = []; // VoucherDetailDTO[]

    public function __construct($date, $summary, $userId, $details)
    {
        $this->date = $date;
        $this->summary = $summary;
        $this->userId = $userId;
        $this->details = $details;
    }
}
?>
