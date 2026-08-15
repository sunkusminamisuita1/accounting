<?php
require_once ROOT_PATH . '/app/dto/homeDto.php';

class homeValidator
{
    private bool $debugMode = false;

    public function __construct(bool $debugMode = false)
    {
        $this->debugMode = $debugMode;
    }

    private function log(string $msg, $data = null): void
    {
        if ($this->debugMode) {
            echo "[homeValidator] " . $msg . "\n";
            if ($data !== null) print_r($data);
        }
    }

    public function commonVali(homeDto $dto): int
    {
        $errFlg = 0;
        $dto->errData = $dto->errData ?? [];

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $OwnUrl = $protocol . ($_SERVER['REQUEST_URI'] ?? '');

        $reportType = $dto->reportType ?? '';

        switch ($reportType) {
            case getujiSisanhyou: // 月次
                $from = $dto->post['from'] ?? $dto->from ?? '';
                if (empty($from)) {
                    $msg = '月次試算表: 年月を入力してください。';
                    $dto->errData['from'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                // YYYY-MM チェック
                if (!preg_match('/^\d{4}-\d{2}$/', $from)) {
                    $msg = '月次試算表: 年月の形式が不正です。YYYY-MM を指定してください。';
                    $dto->errData['from'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case nenjiSisanhyou: // 年次
                $nen = $dto->post['nenji_nen'] ?? $dto->nenji_nen ?? '';
                if (empty($nen)) {
                    $msg = '年次試算表: 年を入力してください。';
                    $dto->errData['nenji_nen'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!ctype_digit((string)$nen) || (int)$nen < 1900 || (int)$nen > 2100) {
                    $msg = '年次試算表: 年は1900〜2100の整数で指定してください。';
                    $dto->errData['nenji_nen'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case ruisekiSisanhyou: // 累積（終了日必須）
                $to = $dto->post['to'] ?? $dto->to ?? '';
                if (empty($to)) {
                    $msg = '累積試算表: 期日（終了日）を入力してください。';
                    $dto->errData['to'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!$this->isValidDate($to)) {
                    $msg = '累積試算表: 期日の形式が不正です。YYYY-MM-DD を指定してください。';
                    $dto->errData['to'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case zenkiHikaku: // 前期比較（基準年必須）
                $kijyun = $dto->post['kijyun_nen'] ?? '';
                if (empty($kijyun)) {
                    $msg = '前期比較: 基準年を入力してください。';
                    $dto->errData['kijyun_nen'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!ctype_digit((string)$kijyun) || (int)$kijyun < 1900 || (int)$kijyun > 2100) {
                    $msg = '前期比較: 基準年は1900〜2100の整数で指定してください。';
                    $dto->errData['kijyun_nen'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            case kikanSisanhyou: // 期間指定
                $from = $dto->post['from'] ?? $dto->from ?? '';
                $to = $dto->post['to'] ?? $dto->to ?? '';
                if (empty($from) || empty($to)) {
                    $msg = '期間試算表: 開始日・終了日は両方入力してください。';
                    $dto->errData['from'] = $msg;
                    $dto->errData['to'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (!$this->isValidDate($from) || !$this->isValidDate($to)) {
                    $msg = '期間試算表: 日付は YYYY-MM-DD の形式で入力してください。';
                    $dto->errData['from'] = $msg;
                    $dto->errData['to'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                    break;
                }
                if (strtotime($from) > strtotime($to)) {
                    $msg = '期間試算表: 開始日は終了日より前の日付を指定してください。';
                    $dto->errData['from'] = $msg;
                    $dto->errData['to'] = $msg;
                    $dto->errData[$OwnUrl] = $msg;
                    $errFlg++;
                }
                break;

            default:
                // 未選択時はエラーにする
                $msg = '試算表の種類を選択してください。';
                $dto->errData['reportType'] = $msg;
                $dto->errData[$OwnUrl] = $msg;
                $errFlg++;
                break;
        }

        $this->log('validation finished', $dto->errData);

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
