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
 
if(empty($_GET)) {
	header("Location: registration_mail_form.php");
	exit();
}else{
	//GETデータを変数に入れる
	$urltoken = isset($_GET[urltoken]) ? $_GET[urltoken] : NULL;
	//メール入力判定
	if ($urltoken == ''){
		$errors['urltoken'] = "もう一度登録をやりなおして下さい。";
	}else{
		try{
			
			//flagが0の未登録者・仮登録日から24時間以内
			$statement = "SELECT mail FROM pre_member WHERE urltoken='urltoken' AND flag =0 AND date > now() - interval 24 hour";

			$stmh = $pdo->prepare($statement);
            $stmh->execute();
			
			/*レコード件数取得
			$row_count = $statement->rowCount();
			
			//24時間以内に仮登録され、本登録されていないトークンの場合
			if( $row_count ==1){
				$mail_array = $statement->fetch();
				$mail = $mail_array[mail];
				$_SESSION['mail'] = $mail;
			}else{
				$errors['urltoken_timeover'] = "このURLはご利用できません。有効期限が過ぎた等の問題があります。もう一度登録をやりなおして下さい。";

			}*/
			
			//データベース接続切断
			
			
		}catch (PDOException $e){
			print('Error:'.$e->getMessage());
			die();
		}
	}
}
 
?>
 
<!DOCTYPE html>
<html>
<head>
<title>会員登録画面</title>
<meta charset="utf-8">
</head>
<body>
<h1>会員登録画面</h1>
 
<?php if (count($errors) === 0): ?>
 
<form action="registration_check.php" method="post">
 
<p>メールアドレス：<?=htmlspecialchars($mail, ENT_QUOTES, 'UTF-8')?></p>
<p>ユーザー名：<input type="text" name="account"></p>
<p>パスワード：<input type="text" name="password"></p>


<input type="submit" value="確認する">
 
</form>
 
<?php elseif(count($errors) > 0): ?>
 
<?php
foreach($errors as $value){
	echo "<p>".$value."</p>";
}
?>
 
<?php endif; ?>
 
</body>
</html>