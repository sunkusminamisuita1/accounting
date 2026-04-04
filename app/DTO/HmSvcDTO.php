<?php
class HmSvcClsDTO{
    public int $From;
    public int $To;
    public int $PrevFrom;
    public int $PrevTo;

    public function __construct($Kikan) {
        $this->From = $Kikan['cur']['from'] ?? 0;
        $this->To = $Kikan['cur']['to'] ?? 0;
        $this->PrevFrom = $Kikan['prev']['from'] ?? 0;
        $this->PrevTo = $Kikan['prev']['to'] ?? 0;
    }
}
?>
