<?php

function get_os_type(){
    if (stristr(PHP_OS, 'WIN')) {
        return 'win';
    } else {
        return 'lin';
    }
}

function get_server_cpu_usage(){
    if (get_os_type() == 'win') {
        // Windowsの場合のCPU使用率取得処理
        $cpuUsage = shell_exec('wmic cpu get loadpercentage /Value');
        $cpuUsage = explode("=", $cpuUsage)[1];
        return trim($cpuUsage);
    } else {
        // Linuxの場合のCPU使用率取得処理
        $load = sys_getloadavg();
        return $load[0]; // 1分間の平均負荷
    }
}

function get_server_memory_usage(){
    if (get_os_type() == 'win') {
        // Windowsの場合のメモリ使用率取得処理
        $memory = shell_exec('wmic OS get FreePhysicalMemory /Value');
        $totalMemory = shell_exec('wmic computersystem get TotalPhysicalMemory /Value');

        $freeMemory = explode("=", $memory)[1];
        $totalMemory = explode("=", $totalMemory)[1];

    } else {
        // Linuxの場合のメモリ使用率取得処理
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match_all('/\w+:\s+(\d+)/', $meminfo, $matches);
        $meminfo = array_combine($matches[0], $matches[1]);

        $totalMemory = $meminfo['MemTotal'];
        $freeMemory = $meminfo['MemFree'] + $meminfo['Buffers'] + $meminfo['Cached'];
    }
    return round((1 - $freeMemory / $totalMemory) * 100, 2);
}


function get_server_disk_space(){
    if (get_os_type() == 'win') {
        // Windowsの場合のディスク使用率取得処理
        $diskSpace = shell_exec('wmic LogicalDisk Where DriveType="3" Get Size, FreeSpace /Value');
        $diskSpace = explode("\n", $diskSpace);
        $totalSpace = 0;
        $freeSpace = 0;
        foreach($diskSpace as $line){
            if(strpos($line, "Size") !== false){
                $totalSpace += explode("=", $line)[1];
            }
            if(strpos($line, "FreeSpace") !== false){
                $freeSpace += explode("=", $line)[1];
            }
        }
    } else {
        // Linuxの場合のディスク使用率取得処理
        $diskSpace = shell_exec('df -P | grep -vE "^Filesystem|tmpfs|cdrom"');
        $lines = explode("\n", $diskSpace);
        $totalSpace = 0;
        $freeSpace = 0;
        foreach($lines as $line){
            $parts = preg_split('/\s+/', $line);
            if(count($parts) > 1){
                $totalSpace += $parts[1];
                $freeSpace += $parts[3];
            }
        }
    }

    return round((1 - $freeSpace / $totalSpace) * 100, 2);
}

function get_gpu_usage(){
    $gpuUsage = shell_exec('nvidia-smi --query-gpu=utilization.gpu --format=csv,noheader,nounits');
    if ($gpuUsage === null) {
        return "N/A";
    }
    return trim($gpuUsage);
}

function get_network_status(){
    $status = shell_exec('ping -n 1 google.com');
    if (strpos($status, 'Packets: Sent = 1, Received = 1, Lost = 0') !== false) {
        return "Network is UP";
    } else {
        return "Network is DOWN";
    }
}

function get_list_files($dir, $indent = 0) {
    $result = '';
    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $path = $dir . '/' . $entry;
                $result .= str_repeat('&nbsp;', $indent * 4) . ' -' . $entry . '<br>';
                if (is_dir($path)) {
                    $result .= get_list_files($path, $indent + 1);
                }
            }
        }
        closedir($handle);
    }
    return $result;
}


function get_status_class_and_text($usage) {
    if ($usage === "N/A") {
        return ['unknown-usage', 'Unknown'];
    }
    $usage = (float) $usage;
    if ($usage < 50) {
        return ['low-usage', 'Normal'];
    } elseif ($usage < 75) {
        return ['medium-usage', 'Moderate'];
    } else {
        return ['high-usage', 'Critical'];
    }
}

function print_status($usage) {
    list($class, $text) = get_status_class_and_text($usage);
    echo "<td class=\"$class\">$text</td>";
}
?>

<?php
function get_server_process_list() {
    $processList = shell_exec('wmic process get description, processid /format:csv');
    $processes = explode("\n", $processList);
    array_shift($processes);  // ヘッダー行を削除

    $result = '<table class="process-list">';
    $result .= '<tbody style="height: 500px; overflow-y: auto; display: block;">';

    foreach ($processes as $process) {
        if (trim($process) != "") {
            list($node, $description, $processId) = explode(",", $process);
            $result .= "<tr style=\"display: table; width: 100%;\"><td>$description</td><td>$processId</td></tr>";
        }
    }
    $result .= '</tbody></table>';
    return $result;
}
?>


<?php
$pc_name = gethostname();
$ssid = shell_exec('netsh wlan show interfaces | findstr SSID');
$ssid = explode(':', $ssid);
$ssid = trim(end($ssid));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Status</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #ACACAC;
        }
        .high-usage {
            background-color: #ffaaaa;
        }
        .medium-usage {
            background-color: #ffffaa;
        }
        .low-usage {
            background-color: #aaffaa;
        }
        .file-list {
            display: block; /* 初期状態でリストを表示 */
        }
    </style>

    <style>
        .process-list {
            border-collapse: collapse;
            width: 100%;
        }
        .process-list th, .process-list td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            width: 50%;
        }
        .process-list thead th {
            position: sticky;
            top: 0;
            background-color: #f9f9f9;
        }
        .process-list tbody {
            display: block;
            height: 200px;
            overflow-y: auto;
        }
        .process-list tbody tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
    </style>
</head>
<body>
<h1>Server Status</h1>
<p>
    <strong>PC Name:</strong> <?php echo $pc_name; ?>
    <br>
    <strong>OS Type:</strong> <?php echo get_os_type()?>
</p>

<table>
    <thead>
        <tr>
            <th>Metric</th>
            <th>Value</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>CPU Usage</td>
            <td><?php $cpuUsage = get_server_cpu_usage(); echo $cpuUsage; ?>%</td>
            <?php print_status($cpuUsage); ?>
        </tr>
        <tr>
            <td>Memory Usage</td>
            <td><?php $memoryUsage = get_server_memory_usage(); echo $memoryUsage; ?>%</td>
            <?php print_status($memoryUsage); ?>
        </tr>
        <tr>
            <td>Disk Space Usage</td>
            <td><?php $diskUsage = get_server_disk_space(); echo $diskUsage; ?>%</td>
            <?php print_status($diskUsage); ?>
        </tr>
    </tbody>
</table>

<h2>Process List</h2>
<table>
    <?php echo get_server_process_list(); ?>
</table>
</body>
</html>
