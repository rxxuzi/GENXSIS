# GENXSIS

GENXSISは、PHPベースのウェブアプリケーションで、サーバーの情報を表示し、簡単にファイル操作を行うことができるユーティリティです。
このプロジェクトでは、サーバーの現在の状態を確認し、特定の操作を簡単に実行するための一連のスクリプトが提供されています。

## 特徴

- **サーバー情報の表示**: サーバーの基本情報、接続されているWi-Fiの詳細、オペレーティングシステムの名前などを表示します。
- **ファイル操作の簡略化**: ファイルのアップロード、ダウンロード、エンコーディングの変換などが行えます。
- **ユーザーフレンドリーなインターフェース**: シンプルで直感的な操作を提供します。

## コンポーネント

- `genxsis.php`: ファイルのアップロードやダウンロード、コマンドの実行などを処理します。
- `status.php`: サーバーの現在の状態（CPU、メモリ、ディスク、GPUの使用率など）を表示するスクリプトです。
- `wifi.php`: Wi-Fiの接続情報を表示します。
- `gui.php`: ファイルとディレクトリのリスティングを行うスクリプトで、サーバー上のファイル管理を容易にします。

### 主な機能
- **ディレクトリの内容の表示**: 指定されたディレクトリ内のファイルとフォルダのリストを表示します。
- **ユーザーフレンドリーな表示**: ファイルとフォルダは、簡単に識別できるように整理されて表示されます。
- **柔軟なファイル操作**: ユーザーは、表示されたリストから直接ファイルやフォルダにアクセスし、必要な操作を行うことができます。

## 注意事項

- GENXSISはオープンソースですが、使用する際は自己責任でご利用ください。
- サーバーのセキュリティ設定によっては、一部の機能が制限される場合があります。
- `shell_exec` などのコマンドを使用する際は、セキュリティリスクを考慮してください。

## ライセンス

このプロジェクトは[MITライセンス](LICENSE)の下で提供されています。