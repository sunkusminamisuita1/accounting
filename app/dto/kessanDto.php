<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
class kessanDto{
    //public int $from;
    public array    $viewResult = [];
    public array    $errData = [];
    public int      $prevFrom;
    public int      $prevTo;
    public string   $activeShop;
    public array    $session = [];
    public array    $post = [];
    //public string   $viewResult;
    public string   $reportType;
    public string   $reportSubType;
    public string   $from;
    public string   $to;
    public string   $zenki_from;
    public string   $zenki_to;
    public string   $nenji_nen;
    public array    $keihiItiran = [];
    public array    $keihiZenkiItiran = [];

    public function __construct($kikan) {
        $this->from = $kikan['cur']['from'] ?? 0;
        $this->to = $kikan['cur']['to'] ?? 0;
        $this->prevFrom = $kikan['prev']['from'] ?? 0;
        $this->prevTo = $kikan['prev']['to'] ?? 0;
        $this->activeShop = '   all';
        $this->reportType = '';
        $this->reportSubType = '月次';
        $this->zenki_from = 0;
        $this->zenki_to   = 0;
        // $this->$keihiItiran[0] = 
		// 		[
		// 			'zenkiKeihiKamoku' => '',
		// 			'zenkiKeihiTotal' => 0,
        //             'keihiKamoku' => '',
        //             'keihiTotal' => 0
		// 		];
        // $this->keihiItiran[] = [
        //                         'jvId' => 0, 'jdId' => 0, 'jvDate' => '', 'keihiKamoku' => '', 'aiteKamoku' => '',
        //                         'kingaku' => 0, 'jdSummary' => '', 'total' => 0, 'aiteTotal' => 0, 'sagaku' => 0
        //                        ];
        //$this->keihiItiran = [];
    }

    public function pl() {

    }
}
?>
