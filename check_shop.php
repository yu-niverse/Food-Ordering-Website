<?php
include 'connect.php';

if (isset($_POST['s_name'])) {
    $s_name = $_POST['s_name'];
    $stmt = $conn->prepare("select s_name from shops where binary s_name = :s_name");
    $stmt->execute(array('s_name' => $s_name));

    if ($stmt->rowCount() == 1) {
        $response = "<span style='color: red'>Account has been registered</span>";
    }else {
        $response = "<span style='color: green'>Avaliable</span>";
    }

    echo $response;
    die;
}
?>