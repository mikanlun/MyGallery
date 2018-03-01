<?php

    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/message.php';

    $login = false;
    $dbh = connectDb();
    $startFlg = false;

    // 認証済みのユーザーか
    if (checkLogin(true) === true) {

        // 認証済みのユーザーのアルバム 取得
        $login = true;
        $userName = $_SESSION['userName'];

        $sql = "select u.name, a.id, a.title, a.path, a.image, a.comment, a.modified from  users u, albums a where u.id = :userId and  u.id = a.user_id order by a.modified desc";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":userId" => $_SESSION['userId'],
        ];

        $stmt->execute($params);
        $albums = $stmt->fetchAll();

        // Render
        $template = $twig->load('albums/index.html');
        // 認証済みのユーザーのアルバムは登録済みか
        if (!count($albums)) {
            // 認証済みのユーザーのアルバムは未登録
            $startFlg = true;
            $starts = [];
            $starts['title'] = $msg['wwllcome']['title'];
            $starts['comment'] = $userName . $msg['wwllcome']['comment'];
            echo $template->render([
                'startFlg' => $startFlg,
                'starts' => $starts,
                'register_url' => '/src/albums/register.php',
                'login' => $login,
                'userName' => $userName,
                'btn_title' => "アルバム登録",
            ]);
        } else {
            // 認証済みのユーザーのアルバムは登録済み
            $startFlg = false;
            echo $template->render([
                'startFlg' => $startFlg,
                'albums' => $albums,
                'show_url' => '/src/albums/show.php',
                'login' => $login,
                'userName' => $userName,
                'userCnt' => 1,         // ユーザー数は、認証済みユーザーの一人のみ
                'userId'  => $_SESSION['userId'],
            ]);
        }
    } else {

        // 一般ユーザーのアルバム 取得
        $login = false;
        $sql = "select u.name, a.id, a.title, a.path, a.image, a.comment, a.modified from  users u, albums a where u.id = a.user_id order by a.modified desc";
        $stmt = $dbh->prepare($sql);

        $stmt->execute();
        $albums = $stmt->fetchAll();

        // ユーザーごとの最新の更新日を取得
        $userInfo = [];
        $sql = "select user_id, max(modified) as latest_modified from albums group by  user_id";
        foreach ($dbh->query($sql) as $row) {
            $userInfo[$row['user_id']] = $row['latest_modified'];
        }
        // ユーザーを最新の更新日順に並べ替える
        arsort($userInfo);
        // ユーザーごとにアルバム 取得
        $dbh = connectDb();
        $sql = "select u.name, a.id, a.title, a.path, a.image, a.comment, a.modified from  users u, albums a where u.id = a.user_id and u.id = :userId order by a.modified desc";
        $stmt = $dbh->prepare($sql);
        $thumbnails =  [];
        foreach ($userInfo as $userId => $latest_modified) {
            $params = [
                ":userId" => $userId,
            ];
            $stmt->execute($params);
            $thumbnail['userId'] = $userId;
            $thumbnail['userInfo'] = $stmt->fetchAll();
            $thumbnails[] = $thumbnail;
        }

        // Render
        $template = $twig->load('albums/index.html');
        // 一般ユーザーのアルバムは登録済みか
        $startFlg = false;
        if (!count($albums)) {
            // 一般ユーザーのアルバムは未登録
            $startFlg = true;
            $starts = [];
            $starts['title'] = $msg['lucky']['title'];
            $starts['comment'] = $msg['lucky']['comment'];
            echo $template->render([
                'startFlg' => $startFlg,
                'starts' => $starts,
                'register_url' => '/src/auth/register.php',
                'login' => $login,
                'btn_title' => "新規登録",
            ]);
        } else {
            // 一般ユーザーのアルバムは登録済み
            $startFlg = false;
            echo $template->render([
                'startFlg' => $startFlg,
                'albums' => $albums,
                'thumbnails' => $thumbnails,
                'show_url' => '/src/albums/show.php',
                'login' => $login,
                'userCnt' => count($thumbnails),     // ユーザー数
            ]);
        }
    }
