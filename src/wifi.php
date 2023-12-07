<?php

function toUTF8($output) {
    foreach ($output as $i => $line) {
        $output[$i] = iconv('Shift-JIS', 'UTF-8', $line);
    }
    return $output;
}

// オペレーティングシステムに応じたWi-Fi情報の取得
function getWifiInfo() {
    if (stristr(PHP_OS, 'WIN')) {
        // Windowsの場合のコマンド
        exec('netsh wlan show interfaces', $wifiOutput);
    } elseif (stristr(PHP_OS, 'LINUX')) {
        // Linuxの場合のコマンド（iwconfigまたはnmcli）
        exec('iwconfig', $wifiOutput); // または exec('nmcli dev wifi')
    } else {
        // その他のOSの場合、情報を取得できない
        $wifiOutput = array("このオペレーティングシステムではWi-Fi情報を取得できません。");
    }

    return $wifiOutput;
}

$wifiInfo = getWifiInfo();
$utf8WifiOutput = toUTF8($wifiInfo);

foreach ($utf8WifiOutput as $line) {
    echo htmlspecialchars($line) . "<br>";
}
