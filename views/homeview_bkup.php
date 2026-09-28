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
                        <!-- <p>ようこそ <?= htmlspecialchars($_SESSION['user']['userName'] ?? 'ゲスト') ?></p> -->

                        <?php
                            //echo "qqqqqqq : {$_GET['route']}";
                            require_once ROOT_PATH.'/views/procSlct.php'; 

                        // バリデーション用メッセージ領域（常に高さを確保して表示によるレイアウト崩れを防ぐ）
                        // $dto->errData は ['fieldName'=>'msg', $OwnUrl=>'msg'] の可能性がある
                        $validationItems = [];
                        foreach ($dto->errData ?? [] as $field => $msg) {
                            // OwnUrlキーは http:// or https:// で始まるので除外
                            if (is_string($field) && (strpos($field, 'http://') === 0 || strpos($field, 'https://') === 0)) {
                                continue;
                            }
                            // 重複メッセージは除去（fieldごとに表示）
                            if (!in_array($msg, $validationItems, true)) {
                                $validationItems[$field] = $msg;
                            }
                        }
                        ?>
                        <div id="validation-messages" style="min-height:3.6em; margin-bottom:1em;">
                            <?php if (!empty($validationItems)): ?>
                                <ul id="validation-list" style="color: red; margin:0; padding-left:1.2em;">
                                    <?php foreach ($validationItems as $field => $m): ?>
                                        <li data-field="<?= h($field) ?>" style="cursor:pointer; text-decoration:underline;"><?= h($m) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <!-- プレースホルダ: 空でも高さを確保 -->
                            <?php endif; ?>
                        </div>
                        <?php
                        ?>

                    <h2>試算表表示：<?= $dto->reportType ?></h2>
                    <form action="index.php?route=home" method="post">試算表<br>

                        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">

                        <input type="radio" name="reportType"
                            value=<?= getujiSisanhyou ?> <?= $currentReport === getujiSisanhyou ? 'checked' : '' ?>>月次試算表出力

                        <input type="radio" name="reportType"
                            value=<?= nenjiSisanhyou ?> <?= $currentReport === nenjiSisanhyou ? 'checked' : '' ?>>年次試算表出力

                        <input type="radio" name="reportType"
                            value=<?= ruisekiSisanhyou ?> <?= $currentReport === ruisekiSisanhyou ? 'checked' : '' ?>>累積試算表出力

                        <input type="radio" name="reportType"
                            value=<?= zenkiHikaku ?> <?= $currentReport === zenkiHikaku ? 'checked' : '' ?>>前期比較出力

                        <input type="radio" name="reportType"
                            value=<?= kikanSisanhyou ?> <?= $currentReport === kikanSisanhyou ? 'checked' : '' ?>>期間入力試算表出力
                        <br>
                        <button type="submit">切替</button><br><br>
                    <!-- </form>
                    <form method="post" action="index.php?route=home"> -->
<!--                        <input type="hidden" name="csrftokenTime" value="<?php //echo h($tokenTime); ?>">  -->
                        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
<?php

                if ($dto->reportType) {
                    if($dto->reportType === getujiSisanhyou){
                        $dto->from = date('Y-m', strtotime($dto->from));
?>
                        年月：<input type="month" name="from"
                        value="<?= h($dto->from ) ?>" placeholder="例: 2025-01">
                        
<?php               } 
                    if($dto->reportType  === nenjiSisanhyou){
?>
                        年：<input type="number" name="nenji_nen" min='1900' max='2100'
                        value="<?= h($dto->post['nenji_nen'] ?? "") ?>" placeholder="例: 2025">
                        
<?php
                        $dto->from = isset($_GET['nenji_nen']) ? $_GET['nenji_nen'] . '0101' : "";
                    };
                    if($dto->reportType  === ruisekiSisanhyou){
?>
                        試算表期日：<input type="date" name="to" value="<?= h($dto->to ) ?>" placeholder="例: 2025-01-01">
                        
<?php               }
                    if($dto->reportType  === zenkiHikaku){
?>
                        基準年：<input type="number" name="kijyun_nen" min='1900' max='2100'
                        value=""  placeholder="例: 2025">
                        
<?php                   $dto->from = isset($_GET['kijyun_nen'])?$_GET['kijyun_nen'] . '0101':"";
                    };
                    if($dto->reportType  === kikanSisanhyou){
?>
                        開始日：<input type="date" name="from" value="<?= h($dto->from) ?>" placeholder="例: 2025-01-01">
                        終了日：<input type="date" name="to" value="<?= h($dto->to ) ?>" placeholder="例: 2025-01-01">
<?php
                    };
                }
?>
			            <br>
			            <button name="KeisanJikkou" type="submit" value="Exec"> 計算実行</button>
		            </form>
<?php
    if (in_array($dto->reportType, [getujiSisanhyou, nenjiSisanhyou, kikanSisanhyou])){
?>
        <p>抽出期間： <?= h($dto->from) ?> 〜 <?= h($dto->to ) ?></p>
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
    foreach ($dto->viewResult as $row){
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
    if (in_array($dto->reportType,[ruisekiSisanhyou])):
?>
        <p>期間： <?= h($dto->from) ?> 〜 <?= h($dto->to ) ?></p>
        <table>
	        <thead>
	            <tr>
		            <th>科目</th>
		            <th>残高</th>
	            </tr>
	        </thead>
	    <tbody>
<?php
        foreach ($dto->viewResult as $row):
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
<script>
// クリックで該当フィールドにフォーカス
document.addEventListener('DOMContentLoaded', function(){
    var list = document.getElementById('validation-list');
    if(!list) return;
    list.addEventListener('click', function(e){
        var li = e.target.closest('li[data-field]');
        if(!li) return;
        var field = li.getAttribute('data-field');
        if(!field) return;
        // try to find element(s) by name
        var el = document.querySelector('[name="'+field+'"]');
        if(el){
            el.focus();
            if(el.select) try{ el.select(); }catch(err){}
            return;
        }
        // special handling for names that might be used multiple times (e.g., radio groups 'reportType')
        var els = document.getElementsByName(field);
        if(els && els.length){
            els[0].focus();
        }
    });
});
</script>
<?php
    if (in_array($dto->reportType,[zenkiHikaku])){
?>
        <p>当期期間： <?= h($dto->from) ?> 〜 <?= h($dto->to) ?></p>
        <p>前期期間： <?= h($dto->zenki_from) ?? '' ?> 〜 <?= h($dto->zenki_to) ?? '' ?></p>
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
        foreach ($dto->viewResult as $row){
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