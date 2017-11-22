<?php

session_start();


try {

 $dsn='データベース名';
 $username='ユーザー名';
 $password='パスワード';

 $pdo = new PDO($dsn,$username,$password);

 $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);


} catch (PDOException $e) {
 exit('データベース接続失敗。'.$e->getMessage());
}

//名前コメントなどのテーブルの作成
function createTable($tableName,$pdo){
 $sql="CREATE TABLE IF NOT EXISTS $tableName(
  num int NOT NULL PRIMARY KEY,
  name  varchar(128),
  comment varchar(128),
  pass varchar(128),
  jikan varchar(128)
 )";
 $stmt=$pdo -> prepare($sql);
 $stmt -> execute();
 }

//画像動画ファイルのテーブルの作成
function createImageTable($tableName,$pdo){
  $sql="CREATE TABLE IF NOT EXISTS $tableName(
    id int UNSIGNED NOT NULL PRIMARY KEY,
    name varchar(255),
    type tinyint(2),
    raw_data mediumblob,
    thumb_data blob
  )
  ";
  $stmt=$pdo -> prepare($sql);
  $stmt -> execute();
 }

//
function tableInsert($tableName,$num,$name,$comment,$pass,$jikan,$pdo){
  $sql="
  INSERT INTO {$tableName} (num,name,comment,pass,jikan) VALUES(:num,:name,:comment,:pass,:jikan)
  ";

  $stmt=$pdo -> prepare($sql);
  $stmt->bindParam(':num', $num, PDO::PARAM_INT);
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
  $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
  $stmt->bindParam(':jikan', $jikan, PDO::PARAM_STR);
  $ret1= $stmt -> execute();

 }
//
function imageTableInsert($tableName,$id,$name,$type,$raw_data,$thumb_data,$pdo){
  $sql="
  INSERT INTO {$tableName} (id,name,type,raw_data,thumb_data) VALUES(:id,:name,:type,:raw_data,:thumb_data)
  ";
 
  $stmt=$pdo -> prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':type', $type, PDO::PARAM_INT);
  $stmt->bindParam(':raw_data', $raw_data, PDO::PARAM_STR);
  $stmt->bindParam(':thumb_data', $thumb_data, PDO::PARAM_STR);
  $ret2= $stmt -> execute();

 }



//名前コメントなどのテーブル名
$table="toukou2";
//画像動画ファイルのテーブル名
$imageTable="media2";
//名前コメントなどのテーブルの作成
createTable($table,$pdo);
//画像動画ファイルのテーブルの作成
createImageTable($imageTable,$pdo);


$blank=0;
//文字コードの設定
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding("utf-8");

//投稿ボタンが押されたら（名前・コメントの有無の確認）
if(isset($_POST['mainSubmit'])){

  $error_message=array();//エラーメッセージという配列変数を作る

  //名前があったら
  if(isset($_POST['name'])&&!empty($_POST['name'])){
    //$naneに送信された名前を入れる
    $name=$_POST['name'];

  //名前がなかったらエラーメッセージ  
  }else{
    $error_message[]="名前が入力されていません";
    //$nameを空に
    $name=NULL;
  }

  //コメントがあったら
  if(isset($_POST['comment'])&&!empty($_POST['comment'])){
    $comment=$_POST['comment'];

  //コメントがなかったら
  }else{
    $error_message[]="コメントが入力されていません";
    $comment=NULL;
  }

//ファイルエラーの検索
if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error'])) {

    switch ($_FILES['upfile']['error']) {
                case UPLOAD_ERR_OK: // OK
                    $file_ok=1;
                    break;
                case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                    break;
                case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
                     $error_message[]="ファイルサイズが大きすぎます.";
                    break;
                case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                    $error_message[]="ファイルサイズが大きすぎます";
                    break;
                default:
                    $error_message[]="画像に関するなんらかのエラーが発生しました";
    }

//ファイルが使用可能の時
if($file_ok==1){

      //ファイルの拡張子を格納
      $text = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);

      //ファイルの拡張子がmp4の時
      if($text=="mp4"){

        $imageName=$_FILES['upfile']['name'];
        $imageType=0;
        $imageRawData=file_get_contents($_FILES['upfile']['tmp_name']);//仮の名前
        $imageThumbData=NULL;

      //ファイルの拡張子がmp4でない時
      }else{
        //0幅　1高さ　2形式　3文字列
        if (!$info = @getimagesize($_FILES['upfile']['tmp_name'])) {
                    $error_message[]=('有効なファイルを指定してください');
        }

        //画像の拡張子を確認（gif jpeg png）
        $image_types=array(IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG);

        //画像が指定した拡張子でない時
        if (!in_array($info[2],$image_types,true)){
          $error_message[]=('未対応の形式です');
        }

        //画像の情報が確認できたら
        if(isset($info)&&!empty($info)){
          
          $imageName=$_FILES['upfile']['name'];
          $imageType=$info[2];
          $imageRawData=file_get_contents($_FILES['upfile']['tmp_name']);
          
          //文字の変換
          $create = str_replace('/', 'createfrom', $info['mime']);
          $output = str_replace('/', '', $info['mime']);

          //画像のサイズ変更
          if ($info[0] >= $info[1]) {
                  $dst_w = 120;
                  $dst_h = ceil(120 * $info[1] / max($info[0], 1));
          } else {
                  $dst_w = ceil(120 * $info[0] / max($info[1], 1));
                  $dst_h = 120;
          }

          //
          if (!$src = @$create($_FILES['upfile']['tmp_name'])) {
                  $error_message[]=('画像リソースの生成に失敗しました');
          }

              //画像のサイズを格納
              $dst = imagecreatetruecolor($dst_w, $dst_h);
              //画像の伸縮
              imagecopyresampled($dst, $src, 0, 0, 0, 0, $dst_w, $dst_h, $info[0], $info[1]);
              //出力のバッファリングを有効にする
              ob_start();
              //
              $output($dst);
              //バッファを取得し削除
              $imageThumbData=ob_get_clean();
              //解放
              imagedestroy($src);
              //解放
              imagedestroy($dst);
        }
      }
    }

  }else{
    $imageName=NULL;
    $imageType=NULL;
    $imageRawData=NULL;
    $imageThumbData=NULL;
  }

//エラーメッセージがある時
if(count($error_message)){
    echo "エラー！！！<br><br>";
    foreach($error_message as $message){
      print($message."<br>");
    }
    echo "正しく入力してください";
    $blank=1;
    exit;
  }
//エラーメッセージがない時
}else{
  $name=NULL;
  $comment=NULL;
  $pass=NULL;
 }


$inputPass=$_POST['Pass'];
$hensyu=$_POST['hensyu'];

    if(isset($hensyu)){
    $sql = "SELECT * FROM toukou2 order by num ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
      if($row['num']==$hensyu){
        //パスワード正解
          if($row['password']==$inputpass){
            echo "$hensyu"." - 編集中"."<br>";
           }
        //パスワード不正解
        }
      }
    }

$delete=$_POST['delete'];

$date=date("Y/m/d(D)H:i:s");

//ログインしているユーザーの登録番号
$userid=$_SESSION['id'];
$name=$_POST['name'];
$comment=$_POST['comment'];
$password=$_POST['password'];


//$user=tableShow_1("member",$pdo);

if($_SESSION['id']=0){
  $firstname="noname";
  $defaultPass=NULL;
}else{
  $firstname=$_SESSION['name'];
  $defaultPass=$_SESSION['password'];
}

$firstcomment="コメントはこちら";

$mode="NO";

if(isset($_POST['mode'])){
  $mode=$_POST['mode'];
}


if(isset($hensyu)){
 $sql = " SELECT * FROM toukou2 order by num ASC ";
 $stmh = $pdo->prepare($sql);
 $stmh->execute();
   foreach($stmh as $row) {
    if($row['num']==$hensyu){
      if($row['pass']==$inputPass){
        $firstname=$row['name'];
        $firstcomment=$row['comment'];
        $defaultPass=$row['pass'];
      }else
      echo "パスワードが間違っています";
    }
   }
}

if($mode=="OK"){

  $edit=$_POST['edit'];

    $sql = " SELECT * FROM toukou2 order by num ASC ";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();

     foreach($stmh as $row) {
      if($row['num']==$edit) {
            $row['comment']=$comment;
    $sql2="UPDATE toukou2 SET comment='{$row['comment']}' where num='{$row['num']}' ";
    $stmh2 = $pdo->prepare($sql2);
    $stmh2->execute();
    }
  }
    /*
    $sql = " SELECT * FROM media2 order by id ASC ";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();

     foreach($stmh as $row){
        if($row['id']==$edit){
            $row['name']=$imageName;
            $row['type']=$imageType;
            $row['raw_data']=$imageRawData;
            $row['thumb_data']=NULL;


           $sql2="UPDATE media2 SET name='{$row['name']}' where id='{$row['id']}' ";
           $stmh2 = $pdo->prepare($sql2);
           $stmh2->execute();

           $sql2="UPDATE media2 SET type='{$row['type']}' where id='{$row['id']}' ";
           $stmh2 = $pdo->prepare($sql2);
           $stmh2->execute();

           $sql2="UPDATE media2 SET raw_data='{$row['raw_data']}' where id='{$row['id']}' ";
           $stmh2 = $pdo->prepare($sql2);
           $stmh2->execute();

           $sql2="UPDATE media2 SET thumb_data='{$row['thumb_data']}' where id='{$row['id']}' ";
           $stmh2 = $pdo->prepare($sql2);
           $stmh2->execute();  
        }
      } 
      */
    }else{ 
//投稿プログラム
if(isset($name)&&isset($comment)&&isset($password)){
     
    $sql = "SELECT * FROM toukou2 order by num ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
      while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
      $num=$row['num'];
    }$num=$num+1;

    tableInsert($table,$num,$name,$comment,$password,$date,$pdo);

    $sql = "SELECT * FROM toukou2 order by num ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
      while($row = $stmh->fetch(PDO::FETCH_ASSOC)){
      $num=$row['num'];
    }

    imageTableInsert($imageTable,$num,$imageName,$imageType,$imageRawData,NULL,$pdo);
  }
  }

//削除プログラム
if(isset($delete)){

    $sql = "SELECT * FROM toukou2 order by num ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
     foreach($stmh as $row){
      if($row['num']==$delete){
        $sql3 = "DELETE FROM toukou2 WHERE num='{$row['num']}' ";
        $stmh3 = $pdo->prepare($sql3);
        $stmh3->execute();
      }
    }

    $sql = "SELECT * FROM media2 order by id ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
     foreach($stmh as $row){
      if($row['id']==$delete){
        $sql3 = "DELETE FROM media2 WHERE id='{$row['id']}' ";
        $stmh3 = $pdo->prepare($sql3);
        $stmh3->execute();
      }
    }
  }

   $array=array();
    $array=array();
    $sqln = "SELECT * FROM toukou2 order by num ASC";
    $stmhn = $pdo->prepare($sqln);
    $stmhn->execute();
    foreach($stmhn as $row){
      $array[]=$row['num'];
     }


 ?>


 <!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="utf-8">
   <title>簡易掲示板</title>
   <style>
   </style>
   <script type="text/javascript">
   function disp(){
     if(confirm('本当に削除しますか？')){
       return true;
     }else{
       alert('キャンセルされました');
       return false;
     }
   }
   </script>
 </head>
 <body>
<h1>掲示板</h1>

<?php
 if($_SESSION['toukou']=="OK"){
?>  
     <a>↓↓↓投稿はこちら↓↓↓</a><br><br>
<?php
    }else{
?>     
     <a>※ログインした方のみ投稿できます。</a><br><br>
<?php
    }
  
if($_SESSION['toukou']=="OK"){
    ?>
 
 <form action="keijiban2.php" method="post" enctype="multipart/form-data">
     <fieldset>
     <fieldset>
       名前　　　
       <input type="text" name="name" cols="50" readonly="readonly" <?php echo "value=".$firstname; ?>><br>
       コメント　
       <input type="text" name="comment" cols="50" <?php echo "value=".$firstcomment; ?>><br>
       <?php
       if(isset($hensyu)){
          echo '<input type="hidden" name="mode" value="OK">';
          echo '<input type="hidden" name="edit" value='.$hensyu.'>';

        }
        ?>
       パスワード
     <input type="password" name="password" cols="50" readonly="readonly" value=<?php
     if(isset($defaultPass)){ echo $defaultPass;}?>><br>

       画像動画
       <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
       <input type="file" name="upfile"><br>

       <input type="submit" name="mainSubmit" value="投稿">
   </fieldset>
   </form>

 
　　<fieldset>
   <form action="keijiban2.php" method="post">
     編集番号
     <select name="hensyu">
       <option value="">未選択</option>
       <<?php
       foreach ($array as $value) {
           echo "<option value={$value}>{$value}</option>";
       }
        ?>
     </select>

     <input type="hidden" name="Pass" <?php echo "value=".$defaultPass; ?>>
     <input type="submit" value=送信>
   </form>
   <form action="keijiban2.php" method="post" onsubmit="return disp()">
     削除番号
     <select name="delete">
       <option value="">未選択</option>
       <<?php
       foreach ($array as $value) {
           echo "<option value={$value}>{$value}</option>";
       }
        ?>
     </select>
     <input type="hidden" name="Pass" <?php echo "value=".$defaultPass; ?>>
     <input type="submit" value=送信>
   </form></fieldset><br>

   <form action keijiban2.php method="post">
   　<a href="http://co-369.99sv-coco.com/logout.php">ログアウト</a><br><br>
   </form>

    <fieldset>
   *注意事項*<br>
       ・名前/パスワードの変更不可<br>
       ・画像はGIF,JPEG,PNG,動画はMP4のみ対応<br>
       ・１度投稿した画像や動画の変更/編集はできません
  </fieldset>

    <?php
    }else{
?>     
      　<a href="http://co-369.99sv-coco.com/login.php">ログイン画面に戻る</a><br><br>
<?php
    }
    echo "</fieldset>";
?>   
    
   </form>
 </body>
 </html>

   <?php
   echo "<fieldset>";
   
   echo "<br>";

    $fid=array();
    $fname=array();
    $ftype=array();
    $fraw_data=array();
    $fthumb_data=array();

     $sql="SELECT * FROM media2 ORDER BY id;";
    $result=$pdo -> query($sql);
    $result->execute();
    foreach($result as $row){

    $fid[]=$row['id'];
    $fname[]=$row['name'];
    $ftype[]=$row['type'];
    $fraw_data[]=$row['raw_data'];
    $fthumb_data[]=$row['thumb_data'];
   }


    $sqln = "SELECT * FROM toukou2 order by num ASC";
    $stmhn = $pdo->prepare($sqln);
    $stmhn->execute();
    foreach($stmhn as $row){

    echo "<fieldset>";
    echo $row['num'];
    echo " - ".$row['name'];
    echo " - ".$row['jikan'];
    echo "<br>".$row['comment']."<br>";


   for($i=0;$i<$row['num'];$i++){
   if($row['num']==$fid[$i]){
    if(isset($ftype[$i])){
         if($ftype[$i]==0){
           echo '<div content="Content-Type: video/mp4">
                    <video width="600" height="480" controls="controls" poster="image" preload="metadata">
                      <source src="data:video/mp4;base64,'.base64_encode($fraw_data[$i]).'"/>;
                    </video>
                </div>';
         }else{
           $gazou=sprintf(
               '<a href="?id=%d"><img src="data:%s;base64,%s" alt="%s" /></a>',
               $fid[$i],
               image_type_to_mime_type($ftype[$i]),
               base64_encode($fraw_data[$i]),
               htmlspecialchars($fname[$i], ENT_QUOTES, 'UTF-8')
             );
             echo $gazou;
         }

       }
     }
   }

  
       echo "</fieldset>";

       echo "<br><br>";
  }
  echo "</fieldset>";

    ?>
