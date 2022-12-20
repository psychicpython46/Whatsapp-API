<?php

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: POST');
require("connect.php");

if (isset($_POST['Registration']))
{
    $json = $_POST['Registration'];
    $json = json_decode($json, true);
    $firstname = $json['FirstName'];
    $lastname = $json['LastName'];
    $username = $json['UserName'];
    $userpassword = $json['UserPassword'];
    $token = $json['Token'];


    $sql = "select username from users where username='$username'";
    $result = $conn->query($sql);

	if ($result !== false && $result -> num_rows > 0)
	{
        echo "fout";
    }
    else
    {
        $sql = "INSERT INTO users (firstname, lastname, username, userpassword, token) VALUES ('$firstname', '$lastname', '$username' , '$userpassword' , '$token')";
        $result = $conn->query($sql);


        $sql = "SELECT max(id) AS maxid FROM users;";
        $result = $conn->query($sql);
    
        if ($result !== false && $result -> num_rows > 0)
        {
        
        while($row = $result->fetch_assoc()) 
        {
             $id = $row['maxid'];
        } 
             echo "$id";
        }

    }
}



if (isset($_POST['Contact']))
{
    $json = $_POST['Contact'];
    $json = json_decode($json, true);
    $id = $json['Id'];
    $username = $json['UserName'];

    $sql = "SELECT id, username FROM users WHERE id <> '$id'";
	$result = $conn->query($sql);
	
	if ($result !== false && $result -> num_rows > 0)
	{
			$arr = [];
			$inc = 0;
		
		while($row = $result->fetch_assoc()) 
		{
              $username = $row['username'];
			  $id = $row['id'];
			  
			  $jsonArrayObject = (array('UserName' => $username,
                                        'Id' => $id	   
										));
                
			  $arr[$inc] = $jsonArrayObject;
              $inc++;
		} 
	}
		$json_array = json_encode($arr);
        echo $json_array;
}



if (isset($_POST['chat']))
{
	$json = $_POST['chat'];
    $json = json_decode($json, true);
    $idfrom = $json['IdFrom'];
    $idto = $json['IdTo'];
    
	  //$sql = "select * from chats";
	$sql = "SELECT idfrom, idto, message FROM chats WHERE (idfrom='$idfrom' OR idfrom='$idto') AND (idto='$idto' OR idto='$idfrom')";
	$result = $conn->query($sql);

	if ($result !== false && $result -> num_rows > 0)
	{
			$arr = [];
			$inc = 0;

		while($row = $result->fetch_assoc()) 
		{
              $idfrom = $row['idfrom'];
              $idto = $row['idto'];
              $message = $row['message'];
  
  
				$jsonArrayObject = (array('IdFrom' => $idfrom,
									'IdTo' => $idto,
									'Message' => $message
									));
                
				$arr[$inc] = $jsonArrayObject;
				$inc++;
		} 
	}
	$json_array = json_encode($arr);
	echo $json_array;
}



if (isset($_POST['chatsend']))
{
    $json = $_POST['chatsend'];
    $json = json_decode($json, true);

    $idfrom = $json['IdFrom'];
    $idto = $json['IdTo'];
    $message = $json['Message'];

    $sql = "insert into chats (idfrom, idto, message ) VALUES ('$idfrom', '$idto', '$message')";
    $result = $conn->query($sql);

    $sql = "SELECT token FROM users WHERE id= '$idto'";
	$result = $conn->query($sql);

    while($row = $result->fetch_assoc()) 
    {
          $token = $row['token'];
    }

    $sql = "SELECT username FROM users WHERE id = '$idfrom'";
    $result = $conn->query($sql);

    while($row = $result->fetch_assoc())
    {
          $username = $row['username'];
    }
    
    $serverkey = "AAAAV0rrDVE:APA91bEWDQVwSKvOZ1z8i5rWaXeuYwDItLWndFlN4LVkk-dbMIYx5C-ozu_ChDot5kIvC0lFGpQ3WSRljVOL5kGfVxMGX3PVWirsNzIQe7zqs2lK-dbJn1J14o1HWN9I8Umt5c5j2be-";

    $url = "https://fcm.googleapis.com/fcm/send";
    $target = $token;
    $data = (array ('idto' => $idto,
                   'idfrom' => $idfrom,
                   'username' => $username,
                   'message' => $message
                   ));
    $fields = array();
    $fields['to'] = $target;
    

    $notifData = [
        'title' => $username,
        'body' => $message
    ];

    $fields = [
        'notification' => $notifData,
        'to' => $token
    ];

    $headers = array( 'Authorization: key='.$serverkey, 'Content-Type: application/json');

    $fields['data'] = $data;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch); 

    print($result);

    curl_close($ch);
}



if (isset($_POST['tokenRefresh']))
{   
    $json = $_POST['tokenRefresh'];
    $json = json_decode($json, true);

    $id = $json['Id'];
    $token = $json['Token'];

	$sql = "UPDATE users SET token = '$token' WHERE id = '$id'";
	$result = $conn->query($sql); 
	
	$Result = json_encode($result);
	echo $Result;
}


?>