<?php
//デバッグ出力　function debug_log(string $message, mixed $data = null, bool $debugMode = true): void {
require_once ROOT_PATH . '/app/dto/homeDto.php';
require_once ROOT_PATH . '/lib/helpers.php';



class homeValidator
{
    private bool $debugMode = false;
    private $dto;

    public function __construct(homeDto $dto, bool $debugMode = false)
    {
        $this->debugMode    =   $debugMode;
        $this->dto          =   $dto;
        
        
    }

    public function commonVali(): int
    {
        $errFlg = 0;
        $this->dto->errData = $this->dto->errData ?? [];

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $protocol . ($_SERVER['REQUEST_URI'] ?? '');

        $reportType = $this->dto->reportType ?? '';

        switch ($reportType) {
            case getujiSisanhyou: // 月次
                $from = $this->dto->post['from'] ?? $this->dto->from ?? '';
                if (empty($from)) {
                    $msg = '月次試算表: 年月を入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                // YYYY-MM チェック
                if (!preg_match('/^\d{4}-\d{2}$/', $from)) {
                    $msg = '月次試算表: 年月の形式が不正です。YYYY-MM を指定してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case nenjiSisanhyou: // 年次
                $nen = $this->dto->post['nenji_nen'] ?? $this->dto->nenji_nen ?? '';
                if (empty($nen)) {
                    $msg = '年次試算表: 年を入力してください。';
                    $this->dto->errData['nenji_nen'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!ctype_digit((string)$nen) || (int)$nen < 1900 || (int)$nen > 2100) {
                    $msg = '年次試算表: 年は1900〜2100の整数で指定してください。';
                    $this->dto->errData['nenji_nen'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case ruisekiSisanhyou: // 累積（終了日必須）
                $to = $this->dto->post['to'] ?? $this->dto->to ?? '';
                if (empty($to)) {
                    $msg = '累積試算表: 期日（終了日）を入力してください。';
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!$this->isValidDate($to)) {
                    $msg = '累積試算表: 期日の形式が不正です。YYYY-MM-DD を指定してください。';
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case zenkiHikaku: // 前期比較（基準年必須）
                $kijyun = $this->dto->post['kijyun_nen'] ?? '';
                if (empty($kijyun)) {
                    $msg = '前期比較: 基準年を入力してください。';
                    $this->dto->errData['kijyun_nen'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!ctype_digit((string)$kijyun) || (int)$kijyun < 1900 || (int)$kijyun > 2100) {
                    $msg = '前期比較: 基準年は1900〜2100の整数で指定してください。';
                    $this->dto->errData['kijyun_nen'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case kikanSisanhyou: // 期間指定
                $from = $this->dto->post['from'] ?? $this->dto->from ?? '';
                $to = $this->dto->post['to'] ?? $this->dto->to ?? '';
                if (empty($from) || empty($to)) {
                    $msg = '期間試算表: 開始日・終了日は両方入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!$this->isValidDate($from) || !$this->isValidDate($to)) {
                    $msg = '期間試算表: 日付は YYYY-MM-DD の形式で入力してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (strtotime($from) > strtotime($to)) {
                    $msg = '期間試算表: 開始日は終了日より前の日付を指定してください。';
                    $this->dto->errData['from'] = $msg;
                    $this->dto->errData['to'] = $msg;
                    $this->dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            default:
                // 未選択時はエラーにする
                $msg = '試算表の種類を選択してください。';
                $this->dto->errData['reportType'] = $msg;
                $this->dto->errData[$OwnUrl] = $msg;
                $errFlg++;
                break;
        }

        debug_log('validation finished', $this->dto->errData, false);

        return $errFlg;
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

}

?>
