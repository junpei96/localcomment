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
 
if(empty($_POST)) {
	header("Location: registration_mail_form.php");
	exit();
}else{
	//POSTされたデータを変数に入れる
	$mail = $_POST['mail'];
	
	//メール入力判定
	if ($mail == ''){
		$errors['mail'] = "メールが入力されていません。";
	}else{
		if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $mail)){
			$errors['mail_check'] = "メールアドレスの形式が正しくありません。";
		}
		
		/*
		ここで本登録用のmemberテーブルにすでに登録されているmailかどうかをチェックする。
		$errors['member_check'] = "このメールアドレスはすでに利用されております。";
		*/
	}
}
 
if (count($errors) === 0){
	
	$urltoken = hash('sha256',uniqid(rand(),1));
	$url = "http://co-369.99sv-coco.com/registration_form.php"."?urltoken=".$urltoken;
	
	//ここでデータベースに登録する
	try{
		
		$sql = "INSERT INTO pre_member (urltoken,mail,date) VALUES (urltoken,mail,now() )";

		$stmh = $pdo->prepare($sql);
        $stmh->execute();
			

	}catch (PDOException $e){
		print('Error:'.$e->getMessage());
		die();
	}
	
	//メールの宛先
	$mailTo = $mail;
	$_SESSION['mail'] = $mailTo;
 
	//Return-Pathに指定するメールアドレス
	$returnMail = 'web@sample.com';
 
	$name = "掲示板";
	$mail = 'web@sample.com';
	$subject = "【簡易掲示板】会員登録用URLのお知らせ";
 
$body = <<< EOM
24時間以内に下記のURLからご登録下さい。
{$url}
EOM;
 
	mb_language('ja');
	mb_internal_encoding('UTF-8');
 
	//Fromヘッダーを作成
	$header = 'From: ' . mb_encode_mimeheader($name). ' <' . $mail. '>';
 
	if (mb_send_mail($mailTo, $subject, $body, $header, '-f'. $returnMail)) {
	
	 	//セッション変数を全て解除
		$_SESSION = array();
	
		//クッキーの削除
		if (isset($_COOKIE["PHPSESSID"])) {
			setcookie("PHPSESSID", '', time() - 1800, '/');
		}
	
 		//セッションを破棄する
 		session_destroy();
 	
 		$message = "メールをお送りしました。24時間以内にメールに記載されたURLからご登録下さい。";
 	
	 } else {
		$errors['mail_error'] = "メールの送信に失敗しました。";
	}	
}
 
?>
 
<!DOCTYPE html>
<html>
<head>
<title>メール確認画面</title>
<meta charset="utf-8">
</head>
<body>
<h1>メール確認画面</h1>
 
<?php if (count($errors) === 0): ?>
 
<p><?=$message?></p>
 
 
<?php elseif(count($errors) > 0): ?>
 
<?php
foreach($errors as $value){
	echo "<p>".$value."</p>";
}
?>
 
<input type="button" value="戻る" onClick="history.back()">
 
<?php endif; ?>
 
</body>
</html>