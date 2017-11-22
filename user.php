

<!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="utf-8">
   <title>掲示板ユーザー登録</title>
    <style type="text/css">
        table, td, th {
            border: solid black 1px;
        }
        table {
            width: 400px;
        }
    </style>
 </head>
 <body>
   <h1>ユーザー登録フォーム</h1><br>
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

  $sql="CREATE TABLE IF NOT EXISTS user(
            id int NOT NULL PRIMARY KEY,
            name varchar(30),
            password varchar(30)
         )";
    $stmt=$pdo -> prepare($sql);

  try{
    $stmt->execute();
  } catch(PDOException $e) {
    echo $e->getMessage();
  }


   $name=$_POST['name'];
   $password=$_POST['password'];
   $mode=0;


  if(isset($name)&&isset($password)){
  	if($name==""){
      echo "ユーザー名を入力してください。"."<br>";
  	}elseif($password==""){
      echo "パスワードを入力してください。"."<br>";
    }else{
    $sql = "SELECT * FROM user order by id ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
     while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
         if($name==$row['name']&&$password==$row['password']){
         	echo "既に存在するユーザーです。";
         	$mode=1;
         }
     }

    if($mode==0){
    $sql = "SELECT * FROM user order by id ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
      while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
         $id=$row['id'];
      }

    $id=$id+1;
    $sql ="INSERT INTO user (id,name,password) 
              VALUES('$id','$name','$password')";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();

    echo "ユーザー登録が完了しました。"."<br>".
         "ユーザー名：$name"."<br>".
        "パスワード：$password"."<br><br>";

   }
  }
 }
    $sql = "SELECT * FROM user";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    
?>
   
   <form action="user.php" method="post">
     ユーザー名
     <input type="text" name="name" ><br>
     パスワード
     <input type="password" name="password" >

     <input type="submit" value="登録"><br><br>


     <a href="http://co-369.99sv-coco.com/login.php">ログイン画面に戻る</a>
   </form>


</tbody></table>
 
 
 </body>
 </html>
