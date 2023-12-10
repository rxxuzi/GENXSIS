<?php
function list_files($dir) {
    $folders = array();
    $files = array();

    // ディレクトリが存在するか確認
    if (is_dir($dir)) {
        // ディレクトリハンドルをオープン
        if ($dh = opendir($dir)) {
            // ディレクトリからファイルを読み込む
            while (($file = readdir($dh)) !== false) {
                if ($file != "." && $file != "..") {
                    // ファイルかフォルダか判断
                    if (is_dir($dir . '/' . $file)) {
                        $folders[] = array("name" => $file, "type" => "folder");
                    } else {
                        $files[] = array("name" => $file, "type" => "file");
                    }
                }
            }
            closedir($dh);
        }
    }
    sort($folders);
    sort($files);
    // フォルダを先に、その後にファイルをリストする
    return array_merge($folders, $files);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>GENXSIS GUI</title>
    <style>
        /* 全体のレイアウト */
        main {
            display: flex;
            flex-direction: row;
            align-items: stretch;
            height: 100vh;
        }

        #file-explorer {
            width: 15%;
            min-width: 15%;
            overflow-y: auto;
            background-color: #f0f0f0;
            padding: 10px;
            box-sizing: border-box;
        }

        #file-viewer {
            flex-grow: 1;
            overflow-y: auto;
            background-color: #fff;
            padding: 10px;
            box-sizing: border-box;
        }

        #file-explorer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        #file-explorer li {
            cursor: pointer;
            margin-bottom: 5px;
        }

        #file-explorer li:hover {
            background-color: #e0e0e0;
        }

        #file-viewer pre,
        #file-viewer code {
            font-family: 'Courier New', monospace;
            word-wrap: break-word;
            white-space: pre-wrap;
            padding: 5px;

            overflow-x: auto;
            display: block;
            max-width: 100%;
            box-sizing: border-box;
        }

        #file-viewer img {
            max-width: 100%;
            max-height: 600px;
            height: auto;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        #file-viewer iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.0/highlight.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script>hljs.highlightAll();</script>

</head>
<body>
<main>
    <div id="file-explorer">
        <?php
        $current_dir = './';
        if (isset($_GET['dir']) && is_dir($_GET['dir'])) {
            $current_dir = $_GET['dir'];
        }
        $items = list_files($current_dir);
        if (count($items) > 0) {
            echo "<ul>";

            if ($current_dir != './') {
                $parent_dir = dirname($current_dir);
                echo "<li onclick='viewDirectory(\"" . htmlspecialchars($parent_dir) . "\")'>..</li>";
            }

            foreach ($items as $item) {
                $style = $item['type'] == "folder" ? "font-weight: bold;" : "";
                if ($item['type'] == "folder") {
                    // フォルダの場合
                    echo "<li onclick='viewDirectory(\"" . htmlspecialchars($current_dir . '/' . $item['name']) . "\")' style='" . $style . "'>" . htmlspecialchars($item['name']) . "</li>";
                } else {
                    // ファイルの場合
                    echo "<li onclick='viewFile(\"" . htmlspecialchars($current_dir . '/' . $item['name']) . "\")' style='" . $style . "'>" . htmlspecialchars($item['name']) . "</li>";
                }
            }
            echo "</ul>";


        } else {
            echo "<p>No files found in the current directory.</p>";
        }
        ?>
    </div>
    <div id="file-viewer"></div>
    <script>
        function viewDirectory(dirPath) {
            window.location.href = '?dir=' + encodeURIComponent(dirPath);
        }
    </script>
</main>
<script>
    const textExtensions = ['txt', 'php', 'js', 'css', 'json', 'xml', 'sql', 'csv'];
    const codeExtensions = [
        'java',  'py', 'c', 'cpp', 'h', 'hpp', 'sh', 'js', 'css', 'json', 'xml', 'scala' ,
        'bat', 'ps1', 'yaml', 'yml', 'ini', 'csv', 'log', 'sql', 'rb', 'perl', 'cs', 'swift', 'kt', 'go' ,
    ];
    const imgExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
    const videoExtensions = ['mp4', 'webm','avi' , 'wmv' , 'flv'];
    const pdfExtensions = ['pdf'];
    const archiveExtensions = ['zip', '7z', 'rar', 'tar', 'gz', 'bz2', 'tar.gz'];
    const audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac'];


    function viewFile(filename) {
        const viewer = document.getElementById('file-viewer');
        // ファイル拡張子を取得
        const fileExtension = filename.split('.').pop();

        // html ファイルの場合、iframe を使用してそのまま表示
        if (fileExtension === 'html') {
            viewer.innerHTML = '<iframe src="' + filename + '"></iframe>';
        }
        // .md ファイルの場合、fetch API を使用して内容を取得し、marked.js で変換して iframe に表示
        else if (fileExtension === 'md') {
            fetch(filename)
                .then(response => response.text())
                .then(text => {
                    // Markdown を HTML に変換
                    const html = marked.parse(text);
                    // Blob を使用して HTML コンテンツを持つオブジェクトURLを生成
                    const blob = new Blob([html], {type: 'text/html'});
                    const url = URL.createObjectURL(blob);
                    // iframe を生成してオブジェクトURLを src に設定
                    viewer.innerHTML = '<iframe src="' + url + '"></iframe>';
                })
                .catch(() => {
                    viewer.innerHTML = '<p>Error loading markdown file.</p>';
                });
        }
        // 画像ファイルの場合
        else if (imgExtensions.includes(fileExtension)) {
            viewer.innerHTML = '<img src="' + filename + '" alt="Image preview" />';
        }
        // 動画ファイルの場合
        else if (videoExtensions.includes(fileExtension)) {
            viewer.innerHTML = '<video controls src="' + filename + '"></video>';
        }
        // PDFファイルの場合
        else if (pdfExtensions.includes(fileExtension)) {
            viewer.innerHTML = '<iframe src="' + filename + '"></iframe>';
        }
        // 音声ファイルの場合
        else if (audioExtensions.includes(fileExtension)) {
            viewer.innerHTML = '<audio controls src="' + filename + '"></audio>';
        }
        // アーカイブファイルの場合
        else if (archiveExtensions.includes(fileExtension)) {
            // ダウンロードリンクを提供
            viewer.innerHTML = `<p>This is an archive file. You can <a href="${filename}" download>download</a> and extract it to view its contents.</p>`;
        }
        // テキストベースのファイルかどうか判断
        else if (codeExtensions.includes(fileExtension) || textExtensions.includes(fileExtension)) {
            fetch(filename)
                .then(response => response.text())
                .then(text => {
                    // テキストをエスケープして表示
                    const escapedText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    if (codeExtensions.includes(fileExtension)) {
                        viewer.innerHTML = '<pre><code class="hljs">' + escapedText + '</code></pre>';
                        // Highlight.jsを適用
                        hljs.highlightBlock(viewer.querySelector('code'));
                    } else {
                        viewer.innerHTML = '<pre><code>' + escapedText + '</code></pre>';
                    }
                })
                .catch(() => {
                    viewer.innerHTML = '<p>Error loading text file.</p>';
                });
        }
        else {
            viewer.innerHTML = '<p>Preview is not available for this file type.</p>';

            console.log("Preview not available for this file type. : " + fileExtension );
        }
    }
</script>
</body>
</html>
