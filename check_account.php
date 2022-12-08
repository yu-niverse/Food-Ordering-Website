<?php
include 'connect.php';

if (isset($_POST['u_account'])) {
    $account = $_POST['u_account'];
    $stmt = $conn->prepare("select u_account from users where binary u_account = :u_account");
    $stmt->execute(array('u_account' => $account));

    if ($stmt->rowCount() == 1) {
        $response = "<span style='color: red'>Account has been registered</span>";
    }else {
        $response = "<span style='color: green'>Avaliable</span>";
    }

    echo $response;
    die;
}
?>