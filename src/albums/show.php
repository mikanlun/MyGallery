<?php
    require_once "../functions/twig_bootstrap.php";
    require_once "../functions/function.php";

    // ログイン認証
    $login = checkLogin(true);

    $err_mesgs = [];
    if ((string)filter_input( INPUT_SERVER, "REQUEST_METHOD" ) == "GET") {
        // Albums 取得
        $albumId = filter_input( INPUT_GET, "albumId" );
        $dbh = connectDb();
        // アルバム情報取得
        $sql = "select user_id, title, path, image, comment, modified from albums where id = :albumId limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":albumId" => $albumId,
        ];

        $stmt->execute($params);
        $album = $stmt->fetch(PDO::FETCH_ASSOC);

        $userId = $album['user_id'];
        $title = $album['title'];
        $imagePath = $album['path'];
        $image = $album['image'];
        $comment = $album['comment'];
        $modified = $album['modified'];

        // ユーザー情報取得
        $sql = "select name from users where id = :userId limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":userId" => $userId,
        ];

        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $name = $user['name'];

        // 認証済みか
        if ($login === true) {
            $userName = $_SESSION["userName"];
        } else {
            $userName = '';
        }

    } else {
        echo "不正なPOSTが行われました。";
        exit();
    }

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("albums/show.html");
    // レンダリング
    echo $template->render([
        "albumId" => $albumId,
        "title" => $title,
        "imagePath" => $imagePath,
        "image" => $image,
        "comment" => $comment,
        "name" => $name,            // 作者
        "modified" => $modified,
        "userName" => $userName,    // ヘッダー用
        "back_url" => $back_url,
        "login" => $login,
    ]);

