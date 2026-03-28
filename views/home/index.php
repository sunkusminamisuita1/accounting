<h1>会計ダッシュボード</h1>
<h2>資産</h2>
<?php foreach($report['asset'] as $r): ?>
    <?=h($r['name'])?>
    <?=number_format($r['amount'])?><br>
<?php endforeach; ?>
<h2>負債</h2>
<?php foreach($report['liability'] as $r): ?>
    <?=h($r['name'])?>
    <?=number_format($r['amount'])?><br>
<?php endforeach; ?>
<h2>収益</h2>
<?php foreach($report['revenue'] as $r): ?>
    <?=h($r['name'])?>
    <?=number_format($r['amount'])?><br>
<?php endforeach; ?>
