<?php
    require_once "../functions/twig_bootstrap.php";
    require_once "../functions/function.php";
    require_once '../configs/validation.php';

    // ログイン認証
    checkLogin();

    if ((string)filter_input( INPUT_SERVER, "REQUEST_METHOD" ) == "GET") {
        // Albums 取得
        $albumId = filter_input( INPUT_GET, "albumId" );
        $dbh = connectDb();

        // Albums 取得
        $sql = "select title, path, image, comment from albums where id = :albumId limit 1";
        $stmt = $dbh->prepare($sql);
        $params = [
            ":albumId" => $albumId,
        ];

        $stmt->execute($params);
        $album = $stmt->fetch(PDO::FETCH_ASSOC);

        $title = $album['title'];
        $imagePath = $album['path'];
        $oldImageName = $album['image'];
        $comment = $album['comment'];


        $err_mesgs = [];
        try {
            $dir = "../../images/" . $imagePath;
            // 登録済みの画像の削除
            $oldPath = $dir . "/" . $oldImageName;
            if(file_exists($oldPath)){
                if (!unlink($oldPath)) {
                    throw new RuntimeException($errMsg['file']['unDeleted']);
                }
            } else {
                throw new RuntimeException($errMsg['file']['unExisted']);
            }

            // albumsから削除
            $sql = "delete from albums where id = :albumId";
            $stmt = $dbh->prepare($sql);
            $params = [
                ":albumId" => $albumId,
            ];

            $stmt->execute($params);

           // gallery Top
            $url = 'http://' . $_SERVER['HTTP_HOST'];
            header ('Location: ' . $url);
            exit();

        } catch (RuntimeException $e) {

            $err_mesgs[] = $e->getMessage();

        }


    } else {
         echo "不正なPOSTが行われました。";
         exit();
    }
