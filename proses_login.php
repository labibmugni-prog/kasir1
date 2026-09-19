<?php
session_start();
include "koneksi.php";

$username = $_POST['username'];
$password = $_POST['password'];

$query = mysqli_query($conn, 
"SELECT * FROM user WHERE username='$username'");

if(mysqli_num_rows($query) > 0){

    $data = mysqli_fetch_assoc($query);

    if($password == $data['password']){

        $_SESSION['login'] = true;
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama'] = $data['nama'];

        header("Location: index.php");
exit;
        exit;

    }else{

        echo "<script>
        alert('Password salah');
        window.location='login.php';
        </script>";

    }

}else{

    echo "<script>
    alert('Username tidak ditemukan');
    window.location='login.php';
    </script>";

}

?>