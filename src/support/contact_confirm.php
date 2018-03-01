<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/define.php';
    require_once '../configs/message.php';

    // ログイン認証
    $login = checkLogin(true);

    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();
        if (!isset($_SESSION['params'])) {
            echo "Illegal access!";
            exit();
        }
        // パラメーターを取得
        $name = $_SESSION['params']['name'];
        $email = $_SESSION['params']['email'];
        $contact = $_SESSION['params']['contact'];

        // contact url
        $back_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/contact.php?contact_back=on";

        // テンプレートを使用
        $template = $twig->load("support/contact_confirm.html");

        // レンダリング
        // 認証済みか
        if ($login) {
            // 認証済み
            echo $template->render([
                "userName" => $_SESSION['userName'],
                "name" => $name,
                "email" => $email,
                "contact" => $contact,
                "token" => $_SESSION['token'],
                "back_url" => $back_url,
                "login" => $login,
            ]);
        } else {
            // 一般ユーザー
            echo $template->render([
                "name" => $name,
                "email" => $email,
                "contact" => $contact,
                "token" => $_SESSION['token'],
                "back_url" => $back_url,
                "login" => $login,
            ]);
        }

    } else {
        // CSRF対策
        checkToken();

        // パラメーターを取得
        $name = trim((string)filter_input( INPUT_POST, 'name' ));
        $email = trim((string)filter_input( INPUT_POST, 'email' ));
        $contact = (string)filter_input( INPUT_POST, 'contact' );

        // mail送信
        mb_language("Japanese");
        mb_internal_encoding("UTF-8");

        $to = $support_email;
        $subject = "お問い合わせ [ " . h($name) . " 様]";
        $message = h($contact);
        $headers = "From: " . h($email) . "\r\n";

        mb_send_mail($to, $subject, $message, $headers);

        // Topへ戻る
        $back_url = 'http://' . $_SERVER['HTTP_HOST'];

         // テンプレートを使用
        $template = $twig->load("support/contact_commit.html");

        // レンダリング
        // 認証済みか
        if ($login) {
            // 認証済み
            echo $template->render([
                "userName" => $_SESSION['userName'],
                "commitMsg" =>  $name . $msg['mail']['commit'],
                "back_url" => $back_url,
                "login" => $login,
            ]);
        } else {
            // 一般ユーザー
            echo $template->render([
                "commitMsg" =>  $name . $msg['mail']['commit'],
                "back_url" => $back_url,
                "login" => $login,
            ]);
        }
    }

