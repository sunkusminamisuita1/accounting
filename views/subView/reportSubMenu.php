    
            <form action="index.php?route=home" method="post">試算表メニュー<br><br>

                <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">

                <input type="radio" name="reportType"
                    value="<?= getujiSisanhyou ?>" <?= $currentReport === getujiSisanhyou ? 'checked' : '' ?>>
                        月次試算表出力<br><br>

                <input type="radio" name="reportType"
                    value="<?= nenjiSisanhyou ?>" <?= $currentReport === nenjiSisanhyou ? 'checked' : '' ?>>
                        年次試算表出力<br><br>

                <input type="radio" name="reportType"
                    value="<?= ruisekiSisanhyou ?>" <?= $currentReport === ruisekiSisanhyou ? 'checked' : '' ?>>
                        累積試算表出力<br><br>

                <input type="radio" name="reportType"
                    value="<?= zenkiHikaku ?>" <?= $currentReport === zenkiHikaku ? 'checked' : '' ?>>
                        前期比較出力<br><br>

                <input type="radio" name="reportType"
                    value="<?= kikanSisanhyou ?>" <?= $currentReport === kikanSisanhyou ? 'checked' : '' ?>>
                        期間入力試算表出力<br><br>
                <button type="submit">切替</button><br><br>
                <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
            </form>
<!-- ここから下は、reportSubView.phpに移動

//echo "現在のルート2: " . h($requestRoute) . "<br>";

            <form action="index.php?route=home" method="post">試算表<br>
<?php
                if ($this->dto->reportType) {

                    if($this->dto->reportType === getujiSisanhyou){
                        $this->dto->from = date('Y-m', strtotime($this->dto->from));
?>
                        年月：<input type="month" name="from"
                        value="<?= h($this->dto->from ) ?>" placeholder="例: 2025-01">
                        
<?php               } 
                    if($this->dto->reportType  === nenjiSisanhyou){
?>
                        年：<input type="number" name="nenji_nen" min='1900' max='2100'
                        value="<?= h($this->dto->post['nenji_nen'] ?? "") ?>" placeholder="例: 2025">
                        
<?php
                        $this->dto->from = isset($_POST['nenji_nen']) ? $_POST['nenji_nen'] . '0101' : "";
                    };
                    if($this->dto->reportType  === ruisekiSisanhyou){
?>
                        試算表期日：<input type="date" name="to" value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
                        
<?php               }
                    if($this->dto->reportType  === zenkiHikaku){
?>
                        基準年：<input type="number" name="kijyun_nen" min='1900' max='2100'
                        value="<?= h($this->dto->post['kijyun_nen'] ?? "") ?>"  placeholder="例: 2025">
                        
<?php                   $this->dto->from = isset($_POST['kijyun_nen'])?$_POST['kijyun_nen'] . '0101':"";
                    };
                    if($this->dto->reportType  === kikanSisanhyou){
?>
                        開始日：<input type="date" name="from" value="<?= h($this->dto->from) ?>" placeholder="例: 2025-01-01">
                        終了日：<input type="date" name="to" value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
<?php
                    };
                }
?>
			            <br>
			            <button name="KeisanJikkou" type="submit" value="Exec"> 試算表表示</button>
		    </form>
-->
