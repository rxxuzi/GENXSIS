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

    console.log(filename);
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
            .catch(err => {
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
            .catch(err => {
                viewer.innerHTML = '<p>Error loading text file.</p>';
            });
    }
    else {
        viewer.innerHTML = '<p>Preview not available for this file type.</p>';

        console.log("Preview not available for this file type. : " + fileExtension );
    }
}