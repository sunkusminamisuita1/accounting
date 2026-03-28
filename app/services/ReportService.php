<?php
class ReportService{
    private $repo;
    public function __construct() {
        $this->repo = new ReportRepository();
    }
    public function getTrialBalance($from,$to)    {
        $rows = $this->repo->getJournalSummary($from,$to);
        $result = [
            'asset' => [],
            'liability' => [],
            'equity' => [],
            'revenue' => [],
            'expense' => []
        ];
        foreach ($rows as $r) {
            $type = $r['type'];
            $amount = $r['total'];
            if ($r['side'] === 'credit') {
                $amount *= -1;
            }
            $result[$type][] = [
                'name'=>$r['name'],
                'amount'=>$amount
            ];
        }
        return $result;
    }
}
