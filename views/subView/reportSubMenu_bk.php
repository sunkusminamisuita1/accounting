            <form action="index.php?route=home.report" method="post">試算表メニュー<br><br>

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
                        期間入力試算表出力<br><br><!--決算関連帳票<br><br> -->

                <!-- <input type="radio" name="reportType"
                    value="<?= hanbaihiIppankanrihi ?>" <?= $currentReport === hanbaihiIppankanrihi ? 'checked' : '' ?>>
                        販売費・一般管理費<br><br> -->

                <button type="submit">切替</button><br><br>
                <!-- <input type="hidden" name="csrfTokenKey" value="<?= h($tokenKey) ?>"> -->
            </form>
<!-- ここから下は、sisanhyou.phpに移動-->

