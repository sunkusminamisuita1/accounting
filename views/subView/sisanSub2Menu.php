            <form action="index.php?route=home.report" method="post">

                <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
<?php
//echo "現在のルート2: " . h($requestRoute) . "<br>";

                if ($this->dto->reportType) {

                    if($this->dto->reportType === getujiSisanhyou){
                        $this->dto->from = date('Y-m', strtotime($this->dto->from));
?>
                        年月：<input type="month" name="from" style="width: 120px;"
                        value="<?= h($this->dto->from ) ?>" placeholder="例: 2025-01">
                        
<?php               } 
                    if($this->dto->reportType  === nenjiSisanhyou){
?>
                        年：<input type="number" name="nenji_nen" style="width: 120px;" min='1900' max='2100'
                        value="<?= h($this->dto->post['nenji_nen'] ?? "") ?>" placeholder="例: 2025">
                        
<?php
                        $this->dto->from = isset($_POST['nenji_nen']) ? $_POST['nenji_nen'] . '0101' : "";
                    };
                    if($this->dto->reportType  === ruisekiSisanhyou){
?>
                        試算表期日：<input type="date" name="to"  style="width: 120px;"
                            value="<?= h($this->dto->to ) ?>" placeholder="例: 2025-01-01">
                        
<?php               }
                    if($this->dto->reportType  === zenkiHikaku){
?>
                        基準年：<input type="number" name="kijyun_nen" style="width: 120px;" min='1900' max='2100'
                        value="<?= h($this->dto->post['kijyun_nen'] ?? "") ?>"  placeholder="例: 2025">
                        
<?php                   $this->dto->from = isset($_POST['kijyun_nen'])?$_POST['kijyun_nen'] . '0101':"";
                    };
                    if($this->dto->reportType  === kikanSisanhyou){
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
			        　　<button name="KeisanJikkou" type="submit" value="Exec">試算表表示</button>
