<form action="index.php?route=kessan.report" method="post">決算書類メニュー<br><br>

    <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">

    <input type="radio" name="reportSubType"
        value="<?= '月次' ?>" <?= $currentSubReport === '月次' ? 'checked' : '' ?>>
            月次出力<br><br>

    <input type="radio" name="reportSubType"
        value="<?= '年次' ?>" <?= $currentSubReport === '年次' ? 'checked' : '' ?>>
            年次出力<br><br>

    <input type="radio" name="reportSubType"
        value="<?= '累積' ?>" <?= $currentSubReport === '累積' ? 'checked' : '' ?>>
            累積出力<br><br>

    <input type="radio" name="reportSubType"
        value="<?= '前期比較' ?>" <?= $currentSubReport === '前期比較' ? 'checked' : '' ?>>
            前期比較出力<br><br>

    <input type="radio" name="reportSubType"
        value="<?= '期間' ?>" <?= $currentSubReport === '期間' ? 'checked' : '' ?>>
            期間出力<br><br>
    <!-- <input type="radio" name="reportType"
        value="<?= hanbaihiIppankanrihi ?>" <?= $currentSubReport === hanbaihiIppankanrihi ? 'checked' : '' ?>>
            販売費・一般管理費<br><br> -->

    <button type="submit">切替</button><br><br>
</form>



<form action="index.php?route=kessan.report" method="post">

        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
<?php
//echo "現在のルート2: " . h($requestRoute) . "<br>";

                if ($this->dto->reportSubType) {

                    if($this->dto->reportSubType === '月次'){
                        $this->dto->from = date('Y-m', strtotime($this->dto->from));
?>
                        年月：<input type="month" name="from" style="width: 120px;"
                        value="<?= h($this->dto->from ) ?>" placeholder="例: 2025-01">
                        
<?php               } 
                    if($this->dto->reportSubType  === '年次'){
?>
                        年：<input type="number" name="nenji_nen" style="width: 120px;" min='1900' max='2100'
                        value="<?= h($this->dto->post['nenji_nen'] ?? "") ?>" placeholder="例: 2025">
                        
<?php
                        $this->dto->from = isset($_POST['nenji_nen']) ? $_POST['nenji_nen'] . '0101' : "";
                    };
                    if($this->dto->reportSubType  === '累積'){
?>
                        試算表期日：<input type="date" name="to"  style="width: 120px;"
                            value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
                        
<?php               }
                    if($this->dto->reportSubType  === '前期比較'){
?>
                        基準年：<input type="number" name="kijyun_nen" style="width: 120px;" min='1900' max='2100'
                        value="<?= h($this->dto->post['kijyun_nen'] ?? "") ?>"  placeholder="例: 2025">
                        
<?php                   $this->dto->from = isset($_POST['kijyun_nen'])?$_POST['kijyun_nen'] . '0101':"";
                    };
                    if($this->dto->reportSubType  === '期間'){
?>
                        開始日：<input type="date" name="from" style="width: 120px;"
                             value="<?= h($this->dto->from) ?>" placeholder="例: 2025-01-01">
                        <br><br>
                        終了日：<input type="date" name="to" style="width: 120px;"
                         value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
<?php
                    };
                }
?>
                    <br><br>
			        　　<button name="KeisanJikkou" type="submit" value="Exec">経費一覧表示</button>
</form>
