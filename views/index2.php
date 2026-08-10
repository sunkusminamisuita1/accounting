<h1>伝票一覧</h1>

<a href="index.php?route=voucher.create">新規作成</a>

<table border="1">
<tr>
<th>日付</th>
<th>摘要</th>
<th>操作</th>
</tr>

<?php foreach ($vouchers as $v): ?>
<tr>
<td><?= h($v['voucher_date']) ?></td>
<td><?= h($v['summary']) ?></td>
<td>
<a href="index.php?route=voucher.edit&id=<?= $v['id'] ?>">編集</a>
<a href="index.php?route=voucher.delete&id=<?= $v['id'] ?>">削除</a>
</td>
</tr>
<?php endforeach; ?>

</table>