<!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="utf-8">
   <title>掲示板ログイン</title>
 </head>
 <body>
     <h1>掲示板ログイン</h1>
<?php

  header("Content-type: text/html; charset=utf-8");
  mb_internal_encoding("utf-8");

try{
     $dsn='データベース名';
 $username='ユーザー名';
 $password='パスワード';

    $pdo = new PDO($dsn,$username,$password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
}catch(PDOException $Exception){
    die('接続エラー：' .$Exception->getMessage());
}
session_start();

$name=$_POST['name'];
$password=$_POST['password'];

if(isset($name)&&isset($password)){
    $sql = "SELECT * FROM member order by id ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
     while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
         if($name==$row['account']&&$password==$row['password']){
         	$_SESSION['name'] = $name;
         	$_SESSION['password'] = $password;
         	$_SESSION['toukou']="OK";
            $_SESSION['id']=$row['id'];
         	header( "Location: http://co-369.99sv-coco.com/keijiban2.php" ) ;
            exit ;
         }
    }echo "ログインに失敗しました。";
}
 
?>   
     <form action="login.php" method="post">
     ユーザー名
     <input type="text" name="name" ><br>
     パスワード
     <input type="password" name="password" ><br>

     <input type="submit" value="ログイン"><br><br>
     
     閲覧のみの利用は
    <a href="http://co-369.99sv-coco.com/keijiban2.php">こちら</a><br><br>
     ユーザー登録は
     <a href="http://co-369.99sv-coco.com/registration_mail_form.php">こちら</a><br><br>

    ＊ユーザー名:lichi  パスワード:lichi  でログイン可能です。<br>
    　使ってみてください



   </form>

   </body>
 </html>