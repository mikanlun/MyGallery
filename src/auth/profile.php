<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';

    if ((string)filter_input( INPUT_SERVER, "REQUEST_METHOD" ) == "GET") {
        // アカウントID 取得
        $userId = filter_input( INPUT_GET, "userId" );
        $dbh = connectDb();
        $sql = "select name, profile from users where id = :userId limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":userId" => $userId,
        ];

        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 認証済みのユーザーか
        $login = checkLogin(true);

        // Topへ戻る
        $back_url = 'http://' . $_SERVER['HTTP_HOST'];

         // テンプレートを使用
        $template = $twig->load("auth/profile.html");

        // レンダリング
        echo $template->render([
            "userName" => $user['name'],
            "profile" => $user['profile'],
            "back_url" => $back_url,
            "login" => $login,
        ]);

    } else {
        echo "不正なPOSTが行われました。";
        exit();
    }
