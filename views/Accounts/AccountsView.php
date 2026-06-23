<?php
    $Accounts   =   $this->Dto->Accounts;
?>
<style>
    .ProcSlct, .ProcSlct td { border: none !important; }
    .UpdTbl { border-collapse: collapse; width: 100%; table-layout: fixed; border: 1px solid #000000; } /* 幅は中身に合わせるのが一般的 */
    .ProcSlct button { cursor: pointer; padding: 5px 15px; }
    th,td {  padding: 0.6em; border: 1px solid #000000; }

    .button-container {
        display: flex;
        justify-content: space-between; /* 左右に均等配置する */
        width: 100%; /* 必要に応じて幅を指定 */
    }

<!-- ##############     エラーメッセージ表示    ################ -->
    <?php if (!empty($this->Dto->ErrData)): ?>
        <ul style="color: red;">
            <?php foreach ($this->Dto->ErrData as $mod => $err): ?>
                <li><?= h($mod) . ": " . h($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

<!-- #############     エラーメッセージ POPUP    ############### -->

    <?=  $this->ErrMsgPopUp->Show($this->Dto);  ?>



</style>
<table class="UpdTbl">
    <tbody>

        <!--###   上   ###-->
        <tr>
            <!--    左　上     -->
            <td>




                <?php foreach($Accounts as $Key => $Row): ?>id | user_id | name | type
                    <td>
                        <?= $Row['id'] ?>
                    </td>
                    <td>
                        <?= $Row['user_id'] ?>
                    </td>
                    <td>
                        <?= $Row['name'] ?>
                    </td>
                    <td>
                        <?= $Row['type'] ?>
                    </td>
                <?php endforeach; ?>





            </td>


            <!--   右  上    -->
            <td>
            


                






            </td>


        <tr>































