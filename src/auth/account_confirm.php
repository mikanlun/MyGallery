<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/define.php';

    // ログイン認証
    checkLogin();

    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();
        if (!isset($_SESSION['params'])) {
            echo "Illegal access!";
            exit();
        }
        // パラメーターを取得
        $name = $_SESSION['params']['name'];
        $sex = $_SESSION['params']['sex'];
        $birthday_year = $_SESSION['params']['birthday']['birthday_year'];
        $birthday_month = $_SESSION['params']['birthday']['birthday_month'];
        $birthday_day = $_SESSION['params']['birthday']['birthday_day'];
        $zip21 = $_SESSION['params']['zip']['zip1'];
        $zip22 = $_SESSION['params']['zip']['zip2'];
        $addr21 = $_SESSION['params']['address'];
        $tel00 = $_SESSION['params']['tel']['tel00'];
        $tel01 = $_SESSION['params']['tel']['tel01'];
        $tel02 = $_SESSION['params']['tel']['tel02'];
        $email = $_SESSION['params']['email'];
        $password = $_SESSION['params']['password'];
        $profile = $_SESSION['params']['profile'];

        // register url
        $back_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/account_edit.php?register_back=on";

        // テンプレートを使用
        $template = $twig->load("auth/account_confirm.html");

        // レンダリング
        echo $template->render([
            "startYear" => $startYear,
            "endYear" => date('Y'),
            "userName" => $_SESSION['userName'],
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
            "password" => $password,
            "profile" => $profile,
            "token" => $_SESSION['token'],
            "back_url" => $back_url,
            "login" => true,
        ]);

    } else {
        // CSRF対策
        checkToken();

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

        // 登録
        $birthday = sprintf("%4d-%02d-%02d", $birthday_year, $birthday_month, $birthday_day);
        $zip = sprintf("%s-%s", $zip21, $zip22);
        $tel = sprintf("%s-%s-%s", $tel00, $tel01, $tel02);

        $dbh = connectDb();
        // newパスワード設定されてか
        if ($password != "") {
            // newパスワード設定
            $sql = "update users set name = :name, sex = :sex, birthday = :birthday, zip = :zip, address = :address, tel = :tel, email = :email, password = :password, profile = :profile, modified = now() where id = :userId";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":name" => $name,
                ":sex" => $sex,
                ":birthday" => $birthday,
                ":zip" => $zip,
                ":address" => $addr21,
                ":tel" => $tel,
                ":email" => $email,
                ":password" => password_hash($password, PASSWORD_DEFAULT),
                ":profile" => $profile,
                ":userId" => $_SESSION['userId'],
            ];
        } else {
            // newパスワード流用
            $sql = "update users set name = :name, sex = :sex, birthday = :birthday, zip = :zip, address = :address, tel = :tel, email = :email, profile = :profile, modified = now() where id = :userId";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":name" => $name,
                ":sex" => $sex,
                ":birthday" => $birthday,
                ":zip" => $zip,
                ":address" => $addr21,
                ":tel" => $tel,
                ":email" => $email,
                ":profile" => $profile,
                ":userId" => $_SESSION['userId'],
            ];
        }

        if ($stmt->execute($params)) {
            // ユーザー名、プロフィールを再設定
            $_SESSION['userName'] = $name;
            $_SESSION['profile'] = $profile;

            $url = "http://" . $_SERVER['HTTP_HOST'];
            header('Location: ' . $url);
            exit();
        } else {
            echo "Not update DB !";
            var_dump($stmt->errorInfo());
            exit();
        }
    }

