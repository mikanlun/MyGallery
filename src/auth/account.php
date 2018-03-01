<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/define.php';
    require_once '../configs/message.php';

    // ログイン認証
    checkLogin();

    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();

        $userId = $_SESSION['userId'];

        $dbh = connectDb();
        $sql = "select * from users where id = :userId";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":userId" => $userId,
        ];

        $stmt->execute($params);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $name = $user['name'];
        $sex = $user['sex'];
        $birthday = explode("-", $user['birthday']);
        list($birthday_year, $birthday_month, $birthday_day) = $birthday;
        $zip = explode("-", $user['zip']);
        list($zip21, $zip22) = $zip;
        $addr21 = $user['address'];
        $tel = explode("-", $user['tel']);
        list($tel00, $tel01, $tel02) = $tel;
        $email = $user['email'];
        $profile = $user['profile'];

        // top url
        $back_url = "http://" . $_SERVER['HTTP_HOST'];

        // アカウント削除
        $account_delete_url = '/src/auth/account_delete.php?userId=' . $userId;

        // テンプレートを使用
        $template = $twig->load("auth/account.html");

        // レンダリング
        echo $template->render([
            "userName" => $_SESSION['userName'],
            "startYear" => $startYear,
            "endYear" => date('Y'),
            "name" => $name,
            "sex" => $sex,
            "birthday_year" => $birthday_year,
            "birthday_month" => $birthday_month,
            "birthday_day" => $birthday_day,
            "zip21" => $zip21,
            "zip22" => $zip22,
            "addr21" => $addr21,
            "tel00" => $tel00,
            "tel01" => $tel01,
            "tel02" => $tel02,
            "email" => $email,
            "password" => "",
            "profile" => $profile,
            "login" => true,
            "back_url" => $back_url,
            "account_delete_url" => $account_delete_url,
            "token" => $_SESSION['token'],
        ]);

    } else {
        // CSRF対策
        checkToken();
        // 確認ボタン押下後の処理
        // パラメーターを取得
        $name = (string)filter_input( INPUT_POST, 'name' );
        $sex = (string)filter_input( INPUT_POST, 'sex' ); 
        $birthday_year = (string)filter_input( INPUT_POST, 'birthday_year' );
        $birthday_month = (string)filter_input( INPUT_POST, 'birthday_month' );
        $birthday_day = (string)filter_input( INPUT_POST, 'birthday_day' );
        $zip21 = (string)filter_input( INPUT_POST, 'zip21' );
        $zip22 = (string)filter_input( INPUT_POST, 'zip22' );
        $addr21 = (string)filter_input( INPUT_POST, 'addr21' );
        $tel00 = (string)filter_input( INPUT_POST, 'tel00' );
        $tel01 = (string)filter_input( INPUT_POST, 'tel01' );
        $tel02 = (string)filter_input( INPUT_POST, 'tel02' );
        $email = (string)filter_input( INPUT_POST, 'email' );
        $password = (string)filter_input( INPUT_POST, 'password' );
        $profile = (string)filter_input( INPUT_POST, 'profile' );

        // 会員登録
        $params = [
            "name" => $name,
            "sex" => $sex,
            "birthday" => [
                "birthday_year" => $birthday_year,
                "birthday_month" => $birthday_month,
                "birthday_day" => $birthday_day,
            ],
            "zip" => [
                "zip1" => $zip21,
                "zip2" => $zip22,
            ],
            "address" => $addr21,
            "tel" => [
                "tel00" => $tel00,
                "tel01" => $tel01,
                "tel02" => $tel02,
            ],
            "email" => $email,
            "password" => $password,
            "profile" => $profile,
        ];
        $_SESSION['params'] = $params;

        $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/account_edit.php";
        header('Location: ' . $url);
        exit();

    }

