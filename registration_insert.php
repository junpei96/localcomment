<?php 
//文字コード設定
  header("Content-type: text/html; charset=utf-8");
  mb_internal_encoding("utf-8");

 try{
     $dsn='データベース名';
 $username='ユーザー名';
 $password='パスワード';

     $pdo = new PDO($dsn,$username,$password);

     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

     $sql="CREATE TABLE IF NOT EXISTS member (
           id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
           account VARCHAR(50) NOT NULL,
           mail VARCHAR(50) NOT NULL,
           password VARCHAR(128) NOT NULL,
           flag TINYINT(1) NOT NULL DEFAULT 1
           )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
     $stmt=$pdo -> prepare($sql);
     $stmt->execute();

     $sql="CREATE TABLE IF NOT EXISTS pre_member (
           id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
           urltoken VARCHAR(128) NOT NULL,
           mail VARCHAR(50) NOT NULL,
           date DATETIME NOT NULL,
           flag TINYINT(1) NOT NULL DEFAULT 0
           )ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;";
     $stmt=$pdo -> prepare($sql);
     $stmt->execute();


 }catch(PDOException $Exception){
     die('接続エラー：' .$Exception->getMessage());
 }
 
?>

<?php
session_start();
 
header("Content-type: text/html; charset=utf-8");
 
//エラーメッセージの初期化
$errors = array();
 

 
$mail = $_SESSION['mail'];
$account = $_SESSION['account'];
 
//パスワードのハッシュ化
$password =  $_SESSION['password'];
 
//ここでデータベースに登録する

	//memberテーブルに本登録する
	$sql = "INSERT INTO member (account,mail,password) VALUES ('$account','$mail','$password')";

	$stmh = $pdo->prepare($sql);
    $stmh->execute();
		
	//pre_memberのflagを1にする
	$sql = "UPDATE pre_member SET flag=1 WHERE mail='$mail' ";
	//プレースホルダへ実際の値を設定する
	$stmh = $pdo->prepare($sql);
    $stmh->execute();
	
	//セッション変数を全て解除
	$_SESSION = array();
	
	//セッションクッキーの削除・sessionidとの関係を探れ。つまりはじめのsesssionidを名前でやる
	if (isset($_COOKIE["PHPSESSID"])) {
    		setcookie("PHPSESSID", '', time() - 1800, '/');
	}
	
 	//セッションを破棄する
 	session_destroy();
 	
 	/*
 	登録完了のメールを送信
 	*/

 
?>
 
<!DOCTYPE html>
<html>
<head>
<title>会員登録完了画面</title>
<meta charset="utf-8">
</head>
<body>
 
<?php if (count($errors) === 0): ?>
<h1>会員登録完了画面</h1>
 
<p>登録完了いたしました。ログイン画面からどうぞ。</p>
<p><a href="http://co-369.99sv-coco.com/login.php">ログイン画面</a></p>
 
<?php elseif(count($errors) > 0): ?>
 
<?php
foreach($errors as $value){
	echo "<p>".$value."</p>";
}
?>
 
<?php endif; ?>
 
</body>
</html>