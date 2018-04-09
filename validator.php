<?php
$validGcode = '
/(M5\r\n)??G80G49G21
G28Z0
M6T[1-9][0]?
G5[4-9](\.?1P[1-9])?[0-2]?
G17
G0X[0-9].[0-9]{3}Y[0-9].[0-9]{3}S[0-9]{5}M3
G43Z[0-9].[0-9]{1,3}H[1-9][0]?/';
$gBlock = '/G80G49G21.*?G53Z0/s';
//$gCodeNum = array(
//    "G54",
//    "G55",
//    "G56",
//    "G57",
//    "G58",
//    "G59",
//    "G54.1P1",
//    "G54.1P2",
//    "G54.1P3",
//    "G54.1P4",
//    "G54.1P5",
//    "G54.1P6",
//    "G54.1P7",
//    "G54.1P8",
//    "G54.1P9",
//    "G54.1P10",
//    "G54.1P11",
//    "G54.1P12",
//);
$uploads_dir = 'uploads';
$inputGcode = file_get_contents($_FILES['fileToUpload']['tmp_name']);
$tmp_name = $_FILES['fileToUpload']['tmp_name'];
$name = $_FILES["fileToUpload"]["name"];
move_uploaded_file($tmp_name, "$uploads_dir/$name");
if (!empty ($inputGcode)) {

    $resG80 = preg_match_all('/G80/', $inputGcode);
    $resG17 = preg_match_all('/G17/', $inputGcode);
    $resM5 = preg_match_all('/M5/', $inputGcode);
    preg_match_all($validGcode, $inputGcode, $matches);
    $g80Hn = $matches;
    preg_match_all($gBlock, $inputGcode, $matches);
    $gCodeWhole = $matches;
    $resM30 = preg_match_all('/M30/', $inputGcode);

    /*Test for extra M30*/
    if ($resM30 > 1) { ?>
        <p style="color:red">Повторення M30</p>
        <?php
    }

    /*Test for G80 error*/
    if ($resG80 != $resG17) {
        ?>
        <p style="color:red">Помилка G80</p>
        <?php
    };

    /*Test for M5 error*/
    if ($resG80 != $resM5) {
        ?>
        <p style="color:red">Помилка M5</p>
        <?php
    };

    /*Test if number of T equals to H*/
    for ($i = 0;
         $i < count($gCodeWhole[0]);
         $i++) {
        preg_match('/G5[4-9](\.?1P[1-9])?[0-12]?/', $gCodeWhole[0][$i], $matches);
        $gcode = $matches;
        preg_match_all('/(?<=T)[1-9][0]?/', $gCodeWhole[0][$i], $matches);
        $t = $matches;
        preg_match_all('/(?<=H)[1-9][0]?/', $gCodeWhole[0][$i], $matches);
        $h = $matches;
        if ($t != $h) {
            ?>
            <p style="color:red"><span style="color:blue"><?= $gcode[0] ?></span>&nbsp;T не співпадає з H</p>
            <?php
        }
    }

    /*Test for repeating G-codes*/
    preg_match_all('/G5[4-9](\.?1P[1-9])?[0-12]?/', $inputGcode, $matches);
    $gcode = $matches;
    if (count($gcode[0]) != count(array_unique($gcode[0]))) {
        ?>
        <p style="color:red">Повторення G-кодів</p>
        <?php
    }

    /*Finding blocks of code by pattern G80....H(n) */
    for ($i = 0;
         $i < count($g80Hn[0]);
         $i++) {
        preg_match('/G5[4-9](\.?1P[1-9])?[0-12]?/', $g80Hn[0][$i], $matches);
        $marker = $matches[0];
        ?>
        <div id="gblock<?= $marker ?>" style="background-color:#ccc; width:160px;">
            <pre>
<?= $g80Hn[0][$i] ?>
            </pre>

            <!-- Highlighting the G-code -->
            <script>
                var str = document.getElementById("gblock<?= $marker ?>").innerHTML;
                var tohighlight = "<?= $marker ?>";
                var txt = str.replace(tohighlight, "<span style='color:orangered; font-size: 130%'>" + tohighlight + "</span>");
                document.getElementById("gblock<?= $marker ?>").innerHTML = txt;
            </script>

        </div>
        <?php
    }
} else {
    echo "Файл не заданий";
}
