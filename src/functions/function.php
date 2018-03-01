<?php

    session_start();

    // エスケープ
    function h($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    // ログイン認証
    function checkLogin($flg = false) {
        // $flg : false -> 不正アクセスのチェック
        // $flg : true -> 認証済みかどうかの判断をする
        if (!isset($_SESSION['auth']) || (isset($_SESSION['auth']) && $_SESSION['auth'] !== true)) {
            if ($flg) {
                return false;
            } else {
                $url = 'http://' . $_SERVER['HTTP_HOST'] . '/src/auth/login.php';
                header('Location: ' . $url);
                exit();
            }
        } else {
            return true;
        }
    }

    // トークンをセッションにセット
    function setToken() {
        $_SESSION['token'] = sha1(uniqid(mt_rand(), true));
    //        echo "sessionSet = " . $_SESSION['token'] . "<br>";
    }

    // トークンをセッションから取得
    function checkToken() {
        if ((string)filter_input( INPUT_POST, "token" ) != $_SESSION['token']) {
            echo "不正なPOSTが行われました。";
            exit();
        }
    }

    // データベース接続
    function connectDb() {
        try {
            $dsn = "mysql:host=localhost;dbname=xxxxxx";
            $user = "xxxxxx";
            $password = "xxxxxx";

            $dbh = new PDO($dsn, $user, $password);
            return $dbh;
        } catch(PDOException $e) {
            echo "DB accsess error ! : " . $e->getMessage();
            exit();
        }
    }

    // セッション破棄
    function sessionDestroy() {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600);
        }

        session_destroy();
    }
