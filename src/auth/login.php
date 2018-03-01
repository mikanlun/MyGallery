<?php
    require_once '../functions/twig_bootstrap.php';
    require_once '../functions/function.php';
    require_once '../configs/validation.php';

    $err_mesgs = [];

    if ((string)filter_input( INPUT_SERVER, 'REQUEST_METHOD' ) == 'GET') {
        // CSRF対策
        setToken();

        # 認証済みかどうかのセッション変数を初期化
        if (! isset($_SESSION['auth'])) {
          $_SESSION['auth'] = false;
        }

        $email = "";
        $password = "";

    } else {
        // CSRF対策
        checkToken();

        // パラメーターを取得
        $email = (string)filter_input( INPUT_POST, 'email' );
        $password = (string)filter_input( INPUT_POST, 'password' );

        // 認証処理
        $dbh = connectDb();
        $sql = "select id, name, email, password, profile from users where email = :email limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":email" => $email,
        ];
        $stmt->execute($params);
        if (! $user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $err_mesgs[] = $errMsg['login']['account_err'];
        } else {
            $hash_passwoord = $user['password'];
            if (!password_verify($password, $hash_passwoord)) {
                $err_mesgs[] = $errMsg['login']['account_err'];
            } else {
                // ログイン 成功
                session_regenerate_id(true);
                $_SESSION['auth'] = true;
                $_SESSION['userId'] = $user['id'];
                $_SESSION['userName'] = $user['name'];
                $_SESSION['profile'] = $user['profile'];
                // Topへリダイレクト
                $url = 'http://' . $_SERVER['HTTP_HOST'];
                header ('Location: ' . $url);
                exit();
            }
        }
        // ログイン 失敗
        setToken();
    }

    // レンダー
    // member_register url
    $userRegisterUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/register.php";

    // Topへ戻る
    $back_url = 'http://' . $_SERVER['HTTP_HOST'];

    // テンプレートを使用
    $template = $twig->load("auth/login.html");

    // レンダリング
    echo $template->render([
        "err_mesgs_cnt" => count($err_mesgs),
        "err_mesgs" => $err_mesgs,
        "login" => false,
        "email" => $email,
        "password" => $password,
        "back_url" => $back_url,
        "token" => $_SESSION['token'],
        "userRegisterUrl" => $userRegisterUrl,
    ]);
