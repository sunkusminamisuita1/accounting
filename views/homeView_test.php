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

                    /* テーブル全体のデザイン */
                    .tb-style {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }

                    /* th行：センター、太字 */
                    .tb-style th {
                        text-align: center;
                        font-weight: bold;
                        padding:8px;
                        border-bottom: 2px solid #ccc; /* 見出しの下に線を引いて見やすくしています */
                    } 
                    /* td共通の余白 */
                    .tb-style td {
                        padding: 6px 8px;
                        border-bottom: 1px solid #eee; /* 行ごとの区切り線 */
                    }

                    /* 科目名：センター */
                    .tb-style .col-name {
                        text-align: center;
                    }

                    /* 金額：右詰め */
                    .tb-style .col-amount {
                        text-align: right;
                        font-family: 'Courier New', Courier, monospace; /* 数字の桁が綺麗に揃うフォント（お好みで） */
                    }

                    .report-radio-group {
                        display: block;
                        margin-bottom: 5px; /* ラジオボタン同士の縦の隙間を調整 */
                    }

                </style>
            </head>
                <body>

                    <?php if (!empty($_SESSION['flash_message'])): ?>
                        <script>
                            alert(<?= json_encode($_SESSION['flash_message']) ?>);
                        </script>
                    <?php unset($_SESSION['flash_message']); endif; ?>


<table>
    <tr>
        <td>
                    <h1>コンビニ会計　ホーム画面</h1>
        </td>
        <td>
                    <h3>現在の操作店舗：</h3>
                    <form action="index.php?route=shop.switch" method="POST" id="shop_selector_form" style="display: inline;">

                        <select name="active_shop" id="active_shop" onchange="document.getElementById('shop_selector_form').submit();">

                            <?php foreach ($_SESSION['userShops'] as $shop): ?>

                                <option value="<?php echo $shop['shop_code']; ?>" 
                                    <?php echo ($shop['shop_code'] == $_SESSION['currentShopCode']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($shop['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>

                            <?php endforeach; ?>

                            <option value="   all" <?php echo ($_SESSION['currentShopCode'] === '   all') ? 'selected' : ''; ?>>
                                【全店合算（連結決算）】
                            </option>
                        </select>
                    </form>
                        
        </td>
        <td>
                        <label for="active_shop"><h3>ようこそ <?= htmlspecialchars($_SESSION['user']['username'] ?? 'ゲスト')."さん" ?></h3>
                        <!-- <p>ようこそ1 <?= htmlspecialchars($_SESSION['user']['userName'] ?? 'ゲスト1') ?></p> -->
                        <div class="two-col" style="display:flex; gap:1rem; align-items:flex-start;">
                            <div class="left-col" style="flex:1;">                   
        </td>
    </tr>
</table>







                        <?php
                            //echo "qqqqqqq : {$_GET['route']}";
                            require_once ROOT_PATH.'/views/procSlct.php'; 

                        $validationItems = [];
                        foreach ($this->dto->errData ?? [] as $field => $msg) {
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

                    <h2>試算表表示：<?= $this->dto->reportType ?></h2>
                    <form action="index.php?route=home" method="post">試算表<br>

                        <label class="report-radio-group">
                            <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
                        </label>

                        <label class="report-radio-group">
                            <input type="radio" name="reportType"
                                value="<?= getujiSisanhyou ?>" <?= $currentReport === getujiSisanhyou ? 'checked' : '' ?>
                                onchange="this.form.submit()">月次試算表出力
                        </label>

                        <label class="report-radio-group">
                            <input type="radio" name="reportType"
                                value="<?= nenjiSisanhyou ?>" <?= $currentReport === nenjiSisanhyou ? 'checked' : '' ?>
                                onchange="this.form.submit()" >年次試算表出力
                        </label>

                        <label class="report-radio-group">
                            <input type="radio" name="reportType"
                                value="<?= ruisekiSisanhyou ?>" <?= $currentReport === ruisekiSisanhyou ? 'checked' : '' ?>
                                onchange="this.form.submit()">累積試算表出力
                        </label>

                        <label class="report-radio-group">
                            <input type="radio" name="reportType"
                                value="<?= zenkiHikaku ?>" <?= $currentReport === zenkiHikaku ? 'checked' : '' ?>
                                onchange="this.form.submit()">前期比較出力
                        </label>

                        <label class="report-radio-group">
                            <input type="radio" name="reportType"
                                value="<?= kikanSisanhyou ?>" <?= $currentReport === kikanSisanhyou ? 'checked' : '' ?>
                                onchange="this.form.submit()">期間入力試算表出力
                        </label>

                        <br>
                        <button type="submit">切替</button><br><br>

                        <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>">
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
			            <button name="KeisanJikkou" type="submit" value="Exec"> 計算実行</button>
		            </form>
<?php
    if (in_array($this->dto->reportType, [getujiSisanhyou, nenjiSisanhyou, kikanSisanhyou])){
?>
        <p>抽出期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to ) ?></p>
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
    foreach ($this->dto->viewResult as $row){
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
    if (in_array($this->dto->reportType,[ruisekiSisanhyou])):
?>
        <p>期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to ) ?></p>
        <table>
	        <thead>
	            <tr>
		            <th>科目</th>
		            <th>残高</th>
	            </tr>
	        </thead>
	    <tbody>
<?php
        foreach ($this->dto->viewResult as $row):
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
    if (in_array($this->dto->reportType,[zenkiHikaku])){
?>
        <p>当期期間： <?= h($this->dto->from) ?> 〜 <?= h($this->dto->to) ?></p>
        <p>前期期間： <?= h($this->dto->zenki_from) ?? '' ?> 〜 <?= h($this->dto->zenki_to) ?? '' ?></p>
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
        foreach ($this->dto->viewResult as $row){
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

<?php
    for($idx=0; $idx < 10; $idx++){
        $keihiItiran[$idx] = ['name'=> 'name'. $idx,  'amount'=> 'amount' . $idx];
    }
?>

	        </tbody>
        </table>

</div>
        <div class="right-col" style="flex:1;">
            <div style="border:1px dashed #ccc; padding:1rem;">
                右側表示エリア
                   
                <!-- テーブルにCSSクラス「tb-style」を適用 -->
                <table class="tb-style">
                    <tbody>
                        <tr>
                            <th>経費科目</th>
                            <th>金額</th>
                        </tr>
                            <?php foreach ($this->dto->keihiItiran as $key => $row) : ?>
                        <tr>
                            <!-- クラス「col-name」でセンター寄せ -->
                            <td class="col-name">
                                <?= h($row['name']) ?>
                            </td>
                            <!-- クラス「col-amount」で右詰め -->
                            <td class="col-amount">
                                <?php 
                                   // 3桁カンマ区切りにし、前後に ¥ と .- を付与
                                   $formatted_debit = number_format((int)$row['debit']);
                                   echo h("¥{$formatted_debit}.-");
                                ?>
                            </td>
                        </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>
</html>