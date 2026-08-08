<?php
class homeDto{
    public int $from;
    public int $to;
    public int $prevFrom;
    public int $prevTo;
    public string $activeShop;
    public string $reportType;

    public function __construct($kikan) {
        $this->from = $kikan['cur']['from'] ?? 0;
        $this->to = $kikan['cur']['to'] ?? 0;
        $this->prevFrom = $kikan['prev']['from'] ?? 0;
        $this->prevTo = $kikan['prev']['to'] ?? 0;
        $this->activeShop = '   all';
    }
}
?>
