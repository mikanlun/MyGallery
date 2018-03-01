<?php
    require_once "../functions/twig_bootstrap.php";
    require_once "../functions/function.php";
    require_once '../configs/validation.php';

    // ログイン認証
    checkLogin();

    $err_mesgs = [];
    if ((string)filter_input( INPUT_SERVER, "REQUEST_METHOD" ) == "GET") {
        // CSRF対策
        setToken();
        // パラメーター初期設定
        $title = "";
        $comment = "";

    } else {
        // CSRF対策
        checkToken();
        // 確認ボタン押下後の処理
        // パラメーターを取得
        $title = (string)filter_input( INPUT_POST, "title" );
        $comment = (string)filter_input( INPUT_POST, "comment" );

        // エラーチェック
        // タイトル
        if ($title == "") {
            $err_mesgs[] = $errMsg['required']['title'];
        } elseif (mb_strlen($title) > 20) {
            $err_mesgs[] = $errMsg['range']['title'];
        }
        // コメント
       if ($comment == "") {
            $err_mesgs[] = $errMsg['required']['comment'];
        } elseif (mb_strlen($comment) > 100) {
            $err_mesgs[] = $errMsg['range']['comment'];
        }
        // 画像
        if (isset($_FILES["image"]["error"]) && is_int($_FILES["image"]["error"])) {
            //画像を保存するディレクトリー
            $userDir = sprintf("user%04d", $_SESSION['userId']);
            $dir = "../../images/" . $userDir;
            //日本語を省くための正規表現
            $pattern = "/^[a-z0-9A-Z\-_]+\.[a-zA-Z]{3,4}$/";
            try {

                // $_FILES["image"]["error"] の値を確認
                switch ($_FILES["image"]["error"]) {
                    case UPLOAD_ERR_OK: // OK
                        break;
                    case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                        throw new RuntimeException($errMsg['file']['unSelected']);
                    case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                    case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                        throw new RuntimeException($errMsg['file']['larger']);
                    default:
                        throw new RuntimeException($errMsg['file']['otherErr']);
                }

                $type = @exif_imagetype($_FILES["image"]["tmp_name"]);
                if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                    throw new RuntimeException($errMsg['file']['unSupported']);
                }
                //ファイル名に日本語が入ってるかチェック
                $imageName = $_FILES["image"]["name"];
                if (!preg_match($pattern,$imageName)) {
                     throw new RuntimeException($errMsg['format']['file']);
                }

                //「$dir」で指定されたディレクトリが存在するか確認
                if(file_exists($dir)){
                    //存在したときの処理
                    //ファイル重複チェックするためにディレクトリー内のファイルを取得する
                    $filelist = scandir($dir);
                    foreach($filelist as $file){
                        //is_dir関数でディレクトリー以外のファイル（つまり画像のみ）を調べる
                        if (!is_dir($file)){
                            if ($imageName == $file){
                                throw new RuntimeException($errMsg['registered']['file']);
                            }
                        }
                    }
                }else{
                    //存在しないときの処理（「$dir」で指定されたディレクトリを作成する）
                    if(mkdir($dir, 0777)){
                        //作成したディレクトリのパーミッションを確実に変更
                        chmod($dir, 0777);
                    }else{
                        throw new RuntimeException($errMsg['file']['unMadeDir']);
                    }
                }
                if (count($err_mesgs) === 0) {
                    $path = $dir . "/" . $imageName;
                    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $path)) {
                        throw new RuntimeException($errMsg['file']['unSaved']);
                    }
                    chmod($path, 0644);
                }
            } catch (RuntimeException $e) {

                $err_mesgs[] = $e->getMessage();

            }

        } else {
            $err_mesgs[] = $errMsg['file']['otherErr'];
        }

        if (count($err_mesgs) === 0) {
            $dbh = connectDb();
            $sql = "insert into albums (user_id, title, path, image, comment, created, modified) values (:user_id, :title, :path, :image,  :comment, now(), now())";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":user_id" => $_SESSION['userId'],
                ":title" => $title,
                ":path" => $userDir,
                ":image" => $imageName,
                ":comment" => $comment,
            ];

            if ($stmt->execute($params)) {
                // gallery Top
                $url = 'http://' . $_SERVER['HTTP_HOST'];
                header ('Location: ' . $url);
                exit();
            } else {
                echo "Not insert DB !";
                exit();
            }
        }
    }

    // CSRF対策
    setToken();

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("albums/register.html");
    // レンダリング
    echo $template->render([
        "err_mesgs_cnt" => count($err_mesgs),
        "err_mesgs" => $err_mesgs,
        "title" => $title,
        "comment" => $comment,
        "userName" => $_SESSION["userName"],
        "back_url" => $back_url,
        "login" => true,
        "token" => $_SESSION["token"],
    ]);

