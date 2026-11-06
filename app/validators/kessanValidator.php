<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH . '/app/dto/kessanDto.php';
//require_once ROOT_PATH . '/lib/helpers.php';



class kessanValidator
{
    private bool $debugMode = false;
    private $dto;
    private $ownUrl;
    private $errFlg;

    public function __construct(kessanDto $dto, bool $debugMode = false)
    {
        $this->debugMode    =   $debugMode;
        $this->dto          =   $dto;
        $this->errFlg       =   0;
        
        
    }

    public function kessanItiran(): int
    {
        $this->errFlg = 0;

        $this->dto->errData = $this->dto->errData ?? [];

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $this->ownUrl = $protocol . ($_SERVER['REQUEST_URI'] ?? '');

        $reportSubType = $this->dto->reportSubType ?? '';
        $reportType    = empty($this->dto->post['reportType']) ? $this->dto->session['reportType'] : $this->dto->post['reportType'];
        if( empty($reportType ) ){
            $msg = '決算書　形式が未入力です。';
            $this->dto->errData['from'] = $msg;
            $this->dto->errData['ownUrl'] = $this->ownUrl;
            $this->errFlg++;
        };


        switch ($reportSubType) {
            case '月次': // 月次

                $from = $this->dto->post['from'] ?? $this->dto->from ?? '';
                if (empty($from)) {
                    $msg = '月次経費一覧: 年月を入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                // YYYY-MM チェック
                if (
                    ( !is_string($from)                              )   ||
                    ( !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $from))
                   )
                {
                    $msg = '月次経費一覧: 年月の形式が不正です。YYYY-MM を指定してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                [$year,$month] = explode('-',$from);
                if( 
                    ( (int)$year < 1900  ) || ( (int)$year >2200 )
                  )
                {
                    $msg = '年は"1900"以上 "2200"以下で指定してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                //dbg('DTOreporttype/', $this->dto->errData);
                break;

            case '年次': // 年次
                $nen = $this->dto->post['nenji_nen'] ?? $this->dto->nenji_nen ?? '';
                if (empty($nen)) {
                    $msg = '年次経費一覧: 年を入力してください。';
                    $this->dto->errData['nenji_nen'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                if (!is_string($nen) || !ctype_digit($nen) || (int)$nen < 1900 || (int)$nen > 2200) {
                    $msg = '年次経費一覧: 年は1900〜2200の整数で指定してください。';
                    $this->dto->errData['nenji_nen'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                }
                break;

            case '累積': // 累積（終了日必須）
                $to = $this->dto->post['to'] ?? $this->dto->to ?? '';
                if (empty($to)) {
                    $msg = '累積経費一覧: 期日（終了日）を入力してください。';
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                if (!$this->isValidDate($to)) {
                    $msg = '累積経費一覧: 期日の形式が不正です。YYYY-MM-DD を指定してください。';
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                }
                break;

            case '前期比較': // 前期比較（基準年必須）
                $kijyun = $this->dto->post['kijyun_nen'] ?? '';
                if (empty($kijyun)) {
                    $msg = '前期比較: 基準年を入力してください。';
                    $this->dto->errData['kijyun_nen'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                if (!ctype_digit((string)$kijyun) || (int)$kijyun < 1900 || (int)$kijyun > 2200) {
                    $msg = '前期比較: 基準年は1900〜2200の整数で指定してください。';
                    $this->dto->errData['kijyun_nen'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                }
                break;

            case '期間': // 期間指定
                $from = $this->dto->post['from'] ?? $this->dto->from ?? '';
                $to = $this->dto->post['to'] ?? $this->dto->to ?? '';
                if (empty($from) || empty($to)) {
                    $msg = '期間: 開始日・終了日は両方入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                if (!$this->isValidDate($from) || !$this->isValidDate($to)) {
                    $msg = '期間: 日付は YYYY-MM-DD の形式で入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                    break;
                }
                if (strtotime($from) > strtotime($to)) {
                    $msg = '期間: 開始日は終了日より前の日付を指定してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData['ownUrl'] = $this->ownUrl;
                    $this->errFlg++;
                }
                break;

            default:
                // 未選択時はエラーにする
                $msg = '決算書の種類を選択してください。';
                $this->dto->errData['reportType'] = $msg;
                $this->dto->errData['ownUrl'] = $this->ownUrl;
                $this->errFlg++;
                break;
        }

        debug_log('validation finished', $this->dto->errData, false);

        return $this->errFlg;
    }

    private function isValidDate($value): bool
    {
        if (!is_string($value) || trim($value) === '') return false;
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        if ($date === false) return false;
        $errors = DateTime::getLastErrors();
        $warningCount = $errors['warning_count'] ?? 0;
        $errorCount = $errors['error_count'] ?? 0;
        return $warningCount === 0 && $errorCount === 0;
    }

    public function noDataChk(array $renderData)
    {
        $kensu = count($renderData);
        if($kensu < 1){
                $msg = '対象データなし、期間、日付をチェックしてください。';
                $this->dto->errData['reportType'] = $msg;
                $this->dto->errData['ownUrl'] = $this->ownUrl;
                $this->errFlg++;
                return $this->errFlg;
        }
    }

}

?>
