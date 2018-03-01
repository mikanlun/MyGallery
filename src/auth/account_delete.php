<?php
    require_once "../functions/twig_bootstrap.php";
    require_once "../functions/function.php";
    require_once '../configs/validation.php';
    require_once '../configs/message.php';

    // ログイン認証
    checkLogin();

    $err_mesgs = [];
    if ((string)filter_input( INPUT_SERVER, "REQUEST_METHOD" ) == "GET") {
        // アカウントID 取得
        $userId = filter_input( INPUT_GET, "userId" );

        // アルバム情報取得
        $dbh = connectDb();
        $sql = "select user_id from albums where user_id = :userId limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":userId" => $userId,
        ];

        $stmt->execute($params);
        $album = $stmt->fetch(PDO::FETCH_ASSOC);

        try {
            // 画像削除
            // 画像を保存dしているディレクトリー
            $userDir = sprintf("user%04d", $userId);
            $dir = "../../images/" . $userDir;

            //「$dir」で指定されたディレクトリが存在するか確認
            if(file_exists($dir)){
                //存在したときの処理
                //ファイルを削除するためにディレクトリー内のファイルを取得する
                $filelist = scandir($dir);
                foreach ($filelist as $file){
                    //is_dir関数でディレクトリー以外のファイル（つまり画像のみ）を調べる
                    if (!is_dir($file)){
                        $deletePath = $dir . "/" . $file;
                        if ( !unlink($deletePath)) {
                            throw new RuntimeException($errMsg['file']['unDeleted']);
                        }
                    }
                }
                //「$dir」で指定されたディレクトリを削除する
                if (!rmdir($dir)) {
                    throw new RuntimeException($errMsg['file']['unDeletedDir']);
                }

            }else{
                //存在しないときの処理
                // 画像が登録済みか
                if ($album != false) {
                    // 画像が登録済み
                    throw new RuntimeException($errMsg['file']['unFindedDir']);
                }
            }

        } catch (RuntimeException $e) {

            $err_mesgs[] = $e->getMessage();

        }

        if (count($err_mesgs) === 0) {
            $dbh = connectDb();
            // 画像が登録済みか
            if ($album != false) {
                // 画像が登録済み
                // albums
                $sql = "delete from albums  where user_id = :userId";
                $stmt = $dbh->prepare($sql);
                $params = [
                    ":userId" => $userId,
                ];
                if (!$stmt->execute($params)) {
                    echo "Not deleted DB ! (albums)";
                    exit();
                }
            }
            // users
            $sql = "delete from users  where id = :userId";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":userId" => $userId,
            ];

            if (!$stmt->execute($params)) {
                echo "Not deleted DB ! (users)";
                exit();
            }
        }

        // セッション、クッキーを削除
        $userName = $_SESSION["userName"];
        sessionDestroy();


    } else {
        echo "不正なPOSTが行われました。";
        exit();
    }

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("auth/account_delete.html");

    // レンダリング
    echo $template->render([
        "err_mesgs_cnt" => count($err_mesgs),
        "err_mesgs" => $err_mesgs,
        "userName" => $userName,
        "message" => $msg['resign']['msg01'] . $userName . $msg['resign']['msg02'],
        "back_url" => $back_url,
        "login" => false,
    ]);

