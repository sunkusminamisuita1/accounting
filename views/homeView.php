<?php 
require_once ROOT_PATH . '/app/dto/constants.php';
?>
<!DOCTYPE html>
    <html lang="ja">
            <head>
                <meta charset="UTF-8">
                    <title>試算表テスト</title>
                <style>
                    table { border-collapse: collapse; width: 100%; }
                    th { border: 1px solid #ccc; padding: 8px; text-align: right;
                        background: #f4f4f4; text-align: center; }
                    th { background: #f4f4f4; text-align: center; }
                    td { border: 1px solid #ccc; padding: 8px; text-align: right; }
                    .text-left { text-align: left; }
                </style>
            </head>
                <body>



                    <?php if (!empty($_SESSION['flash_message'])): ?>
                        <script>
                            alert(<?= json_encode($_SESSION['flash_message']) ?>);
                        </script>
                    <?php unset($_SESSION['flash_message']); endif; ?>



                    <h1>ホーム画面</h1>
                        <p>ようこそ <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト') ?></p>

                        <?php
                            //echo "qqqqqqq : {$_GET['route']}";
                            require_once ROOT_PATH.'/views/lib/procSlct.php'; 
                        ?>

                    <h2>試算表表示：<?= $reportType ?></h2>
                    <form action="index.php?route=home" method="post">試算表<br>
                        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
                        <input type="radio" name="reportType"
                            value=<?= getujiSisanhyou ?>>月次試算表出力
                        <input type="radio" name="reportType"
                            value=<?= nenjiSisanhyou ?>>年次試算表出力
                        <input type="radio" name="reportType"
                            value=<?=  ruisekiSisanhyou ?>>累積試算表出力
                        <input type="radio" name="reportType"
                            value=<?=  zenkiHikaku ?>>前期比較出力
                        <input type="radio" name="reportType"
                            value=<?=  kikanSisanhyou ?>>期間入力試算表出力
                        <br>
                        <button type="submit">切替</button><br><br>
                    </form>
                    <form method="post" action="index.php?route=home">
<!--                        <input type="hidden" name="csrftokenTime" value="<?php //echo h($tokenTime); ?>">  -->
                        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
<?php
                $today = new dateTime();
                $nenji_nen = $nenji_nen ?? "";
                $lastDate = $today->modify('-1 month');               
                $from = $from ?? $lastDate->format('Y-m-d');
                $to = $to ?? date('Y-m-d');
                $result = [];
                if ($reportType) {
                    if($reportType === getujiSisanhyou){
        //                $from = $from ?? $lastDate->format('Y-m');
                        $from = date('Y-m', strtotime($from));
?>
                        年月：<input type="month" name="from"
                        value="<?= h($from ) ?>" required>
<?php               } 
                    if($reportType === nenjiSisanhyou){
?>
                        年：<input type="number" name="nenji_nen" min='1900' max='2100'
                        value="<?= h($nenji_nen) ?>" required placeholder="例: 2025">
<?php
                        $from = isset($_GET['nenji_nen'])?$_GET['nenji_nen'] . '0101':"";
                    };
                    if($reportType === ruisekiSisanhyou){
?>
                        試算表期日：<input type="date" name="to" value="<?= h($to) ?>" required>
<?php               }
                    if($reportType === zenkiHikaku){
?>
                        基準年：<input type="number" name="kijyun_nen" min='1900' max='2100'
                        value="" required placeholder="例: 2025">
<?php                   $from = isset($_GET['kijyun_nen'])?$_GET['kijyun_nen'] . '0101':"";
                    };
                    if($reportType === kikanSisanhyou){
?>
                        開始日：<input type="date" name="from" value="<?= h($from) ?>" required>
                        終了日：<input type="date" name="to" value="" required>
<?php
                    };
                }
?>
			            <br>
			            <button name="KeisanJikkou" type="submit" value="Exec"> 計算実行</button>
		            </form>
<?php
    if (in_array($reportType, [getujiSisanhyou, nenjiSisanhyou, kikanSisanhyou])){
?>
        <p>抽出期間： <?= h($from) ?> 〜 <?= h($to) ?></p>
        <table>
	        <thead>
		        <tr>
			        <th>科目</th>
			        <th>借方</th>
			        <th>貸方</th>
			        <th>残高</th>
		        </tr>
	        </thead>
	        <tbody>
<?php
    foreach ($viewResult as $row){
        if ($row['row_type'] === 'account'){
?>
		        <tr>
			        <td class="text-left"><?= h($row['name']) ?></td>
			        <td><?= number_format($row['debit']) ?></td>
			        <td><?= number_format($row['credit']) ?></td>
			        <td><?= number_format($row['balance']) ?></td>
		        </tr>
<?php
        }elseif ($row['row_type'] === 'total'){ ?>
		        <tr>
			        <th><?= h($row['label']) ?></th>
			        <th><?= number_format($row['debit']) ?></th>
			        <th><?= number_format($row['credit']) ?></th>
			        <th></th>
		        </tr>
<?php
        }
    };
?>
	        </tbody>
        </table>
<?php
    }
    if (in_array($reportType,[ruisekiSisanhyou])):
?>
        <p>期間： <?= h($from) ?> 〜 <?= h($to) ?></p>
        <table>
	        <thead>
	            <tr>
		            <th>科目</th>
		            <th>残高</th>
	            </tr>
	        </thead>
	    <tbody>
<?php
        foreach ($viewResult as $row):
            if ($row['row_type'] === 'account'):
?>
	            <tr>
		            <td class="text-left"><?= h($row['name']) ?></td>
		            <td><?= number_format($row['balance']) ?></td>
	            </tr>
<?php
            elseif ($row['row_type'] === 'subtotal'): 
?>
                <tr>
		            <th class="text-left"><?= h($row['label']) ?></th>
		            <th><?= number_format($row['balance']) ?></th>
	            </tr>
<?php
                elseif ($row['row_type'] === 'total'):
?>
                    <tr style="background:#eee;">
		                <th class="text-left"><?= h($row['label']) ?></th>
		                <th><?= number_format($row['balance']) ?></th>
	                </tr>
<?php
            endif;
        endforeach;
?>
	    </tbody>
        </table>
<?php
    endif;
?>
<?php
    if (in_array($reportType,[zenkiHikaku])){
?>
        <p>当期期間： <?= h($service->from) ?> 〜 <?= h($service->to) ?></p>
        <p>前期期間： <?= h($service->zenki_from) ?> 〜 <?= h($service->zenki_to) ?></p>
        <table>
	        <thead>
		        <tr>
			        <th>科目</th>
			        <th>当期残高</th>
			        <th>前期残高</th>
			        <th>増減</th>
		        </tr>
	        </thead>
	        <tbody>
<?php
        foreach ($viewResult as $row){
 ?>
		        <tr>
			        <td class="text-left"><?= h($row['name']) ?></td>
			        <td><?= number_format($row['cur_balance']) ?></td>
			        <td><?= number_format($row['prev_balance']) ?></td>
			        <td><?= number_format($row['diff']) ?></td>
		        </tr>
<?php
        }
    }
?>
	        </tbody>
        </table>
    </html>













