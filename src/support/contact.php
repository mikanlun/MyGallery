<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/validation.php';

    $err_mesgs = [];
    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();
        if ( !filter_input( INPUT_GET, 'contact_back' )) {
            // パラメーター初期設定
            $name = "";
            $email = "";
            $email_conf = "";
            $contact = "";

        } else {
            // 確認画面からの戻り
            // パラメーターを取得
            $name = $_SESSION['params']['name'];
            $email = $_SESSION['params']['email'];
            $email_conf = $_SESSION['params']['email_conf'];
            $contact = $_SESSION['params']['contact'];
        }

    } else {
        // CSRF対策
        checkToken();
        // 確認ボタン押下後の処理
        // パラメーターを取得
        $name = (string)filter_input( INPUT_POST, 'name' );
        $email = (string)filter_input( INPUT_POST, 'email' );
        $email_conf = (string)filter_input( INPUT_POST, 'email_conf' );
        $contact = (string)filter_input( INPUT_POST, 'contact' );

        // エラーチェック
        // 氏名
        if ($name == "") {
            $err_mesgs[] = $errMsg['required']['name'];
        }

        // メールアドレス
        if ($email == "") {
            $err_mesgs[] = $errMsg['required']['email'];
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err_mesgs[] = $errMsg['format']['email'];
        } elseif ($email != $email_conf) {
            $err_mesgs[] = $errMsg['match']['email'];
        }

        // お問い合わせ
        if ($contact == "") {
            $err_mesgs[] = $errMsg['required']['contact'];
        }
        // エラーはあるか
        if (count($err_mesgs) === 0) {
            // 会員登録
            $params = [
                "name" => $name,
                "email" => $email,
                "email_conf" => $email_conf,
                "contact" => $contact,
            ];
            $_SESSION['params'] = $params;

            $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/contact_confirm.php";
            header('Location: ' . $url);
            exit();
        }
    }

    // CSRF対策
    setToken();

    // ログイン認証
    $login = checkLogin(true);

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("support/contact.html");
    // レンダリング
    // 認証済みか
    if ($login) {
        // 認証済み
        echo $template->render([
            "userName" => $_SESSION['userName'],
            "err_mesgs_cnt" => count($err_mesgs),
            "err_mesgs" => $err_mesgs,
            "login" => $login,
            "name" => $name,
            "email" => $email,
            "email_conf" => $email_conf,
            "contact" => $contact,
            "back_url" => $back_url,
            "token" => $_SESSION['token'],
        ]);
    } else {
        // 一般ユーザー
        echo $template->render([
            "err_mesgs_cnt" => count($err_mesgs),
            "err_mesgs" => $err_mesgs,
            "login" => $login,
            "name" => $name,
            "email" => $email,
            "email_conf" => $email_conf,
            "contact" => $contact,
            "back_url" => $back_url,
            "token" => $_SESSION['token'],
        ]);
    }
