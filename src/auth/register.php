<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/define.php';
    require_once '../configs/validation.php';

    $err_mesgs = [];
    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();
        if ( !filter_input( INPUT_GET, 'register_back' )) {
            // パラメーター初期設定
            $name = "";
            $sex = 1;
            $birthday_year = $startYear;
            $birthday_month = 1;
            $birthday_day = 1;
            $zip21 = "";
            $zip22 = "";
            $addr21 = "";
            $tel00 = "";
            $tel01 = "";
            $tel02 = "";
            $email = "";
            $email_conf = "";
            $password = "";
            $profile = "";
            $password_conf = "";

        } else {
            // 確認画面からの戻り
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
            $email_conf = $_SESSION['params']['email_conf'];
            $password = $_SESSION['params']['password'];
            $password_conf = $_SESSION['params']['password_conf'];
            $profile = $_SESSION['params']['profile'];
        }

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
        $email_conf = (string)filter_input( INPUT_POST, 'email_conf' );
        $password = (string)filter_input( INPUT_POST, 'password' );
        $password_conf = (string)filter_input( INPUT_POST, 'password_conf' );
        $profile = (string)filter_input( INPUT_POST, 'profile' );

        // エラーチェック
        // 氏名
        if ($name == "") {
            $err_mesgs[] = $errMsg['required']['name'];
        } elseif (mb_strlen($name) > 20) {
            $err_mesgs[] = $errMsg['range']['name'];
        }

        // 生年月日
        if ( !checkdate($birthday_month, $birthday_day, $birthday_year)) {
            $err_mesgs[] = $errMsg['format']['date'];
        }

        // 郵便番号
        if ($zip21 == "" or $zip22 == "") {
            $err_mesgs[] = $errMsg['required']['zip'];
        } elseif (!preg_match("/^\d{3}\-\d{4}$/", ($zip21 . '-' . $zip22))) {
            $err_mesgs[] = $errMsg['format']['zip'];
        }

        // 住所
        if ($addr21 == "") {
            $err_mesgs[] = $errMsg['required']['address'];
        } elseif (mb_strlen($addr21) > 50) {
            $err_mesgs[] = $errMsg['range']['address'];
        }

        // 電話番号
        if ($tel00 == "" or $tel01 == "" or $tel02 == "") {
            $err_mesgs[] = $errMsg['required']['tel'];
        } elseif (!preg_match('/^0\d{1,4}-\d{1,4}-\d{4}$/', ($tel00 . "-" . $tel01 . "-" . $tel02))) {
            $err_mesgs[] = $errMsg['format']['tel'];
        }

        // メールアドレス
        if ($email == "") {
            $err_mesgs[] = $errMsg['required']['email'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err_mesgs[] = $errMsg['format']['email'];
        } elseif ($email != $email_conf) {
            $err_mesgs[] = $errMsg['match']['email'];
        } else {
            // メールアドレスは既に登録済みか
            $dbh = connectDb();
            $sql = "select email from users where email = :email limit 1";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":email" => $email,
            ];
            $stmt->execute($params);
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $err_mesgs[] = $errMsg['registered']['email'];
            }
        }

        // パスワード
        if ($password == "") {
            $err_mesgs[] = $errMsg['required']['password'];
        } elseif (strlen($password) < 5 || strlen($password) > 10) {
            $err_mesgs[] = $errMsg['range']['password'];
        } elseif ($password != $password_conf) {
            $err_mesgs[] = $errMsg['match']['password'];
        } elseif (!preg_match("/^[a-zA-Z0-9]+$/", $password)) {
            $err_mesgs[] = $errMsg['format']['password'];
        }

        // プロフィール
        if ($profile == "") {
            $err_mesgs[] = $errMsg['required']['profile'];
        } elseif (mb_strlen($profile) > 100) {
            $err_mesgs[] = $errMsg['range']['profile'];
        }

        // エラーはあるか
        if (count($err_mesgs) === 0) {
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
                "email_conf" => $email_conf,
                "password" => $password,
                "password_conf" => $password_conf,
                "profile" => $profile,
            ];
            $_SESSION['params'] = $params;

            $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/register_confirm.php";
            header('Location: ' . $url);
            exit();
        }
    }

    // CSRF対策
    setToken();

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("auth/register.html");
    // レンダリング
    echo $template->render([
        "err_mesgs_cnt" => count($err_mesgs),
        "err_mesgs" => $err_mesgs,
        "startYear" => $startYear,
        "endYear" => date('Y'),
        "login" => false,
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
        "email_conf" => $email_conf,
        "password" => $password,
        "password_conf" => $password_conf,
        "profile" => $profile,
        "back_url" => $back_url,
        "token" => $_SESSION['token'],
    ]);

