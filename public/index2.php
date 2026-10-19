<?php
$table = [
    0 => [ "id" => 174, "name" => "売上", "sort_order" => "1500" ],
    1 => [ "id" => 176, "name" => "売上0", "sort_order" => "500" ],
    2 => [ "id" => 130, "name" => "現金", "sort_order" => "500" ],
];

$grouped_keys = [];
$test = $table[1]['sort_order'];    
// sort_order ごとに1次元目のキーをグループ分けする
foreach ($table as $key => $row) {
    $grouped_keys[$row['sort_order']][] = $key;
}

// グループ分けた中から、要素数が2個以上（＝重複している）のものだけを抽出
$duplicate_groups = array_filter($grouped_keys, function($keys) {
    //var_dump($keys);echo "<br>";
    return count($keys) > 1;
});

// 結果を出力

$i=0;
$duplicate_groups = array_filter($table, function($row) use ($test) {
    $i++;
    if((int)$row['sort_order'] === (int)$test ){
        return $row['sort_order'];
    }
});
print_r($duplicate_groups);