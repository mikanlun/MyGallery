<?php

/****************/
/* entry point */
/****************/

$mainUrl = "http://" . $_SERVER['HTTP_HOST'] . "/src/albums/index.php";
header ('Location:' . $mainUrl);
exit();
