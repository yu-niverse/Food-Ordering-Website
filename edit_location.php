<?php
include 'connect.php';
$u_account = $_SESSION['u_account'];
$stmt = $conn->prepare("select * from users where u_account = :u_account");
$stmt->execute(array('u_account' => $u_account));
$info = $stmt->fetch();

try {

    if ($_POST['new_longitude']=="" || $_POST['new_latitude']=="") {
        throw new Exception('Please input all the columns');
    }

    $new_longitude = $_POST['new_longitude'];
    $new_latitude = $_POST['new_latitude'];

    $new_position = "POINT($new_longitude $new_latitude)";

    if ($new_latitude < -90 || $new_latitude > 90) throw new Exception('Wrong latitude format !!');
    if ($new_longitude < -180 || $new_longitude > 180) throw new Exception('Wrong longitude format !!');

    $stmt = $conn->prepare("update users set u_longitude = :u_longitude, u_latitude = :u_latitude 
        , u_position = ST_GeomFromText(:u_position) where u_account = :u_account");
    $stmt->execute(array('u_longitude' => $new_longitude, 'u_latitude' => $new_latitude, 
        'u_position' => $new_position, 'u_account' => $u_account));
    
    echo <<< EOT
    <!DOCTYPE html>
    <html> 
        <body>
            <script>
                window.location.replace('nav.php');
            </script
        </body> 
    </html> 
    EOT;
    exit();
}
catch(Exception $e) {
    $msg = $e->getMessage();
    # pop up the error message
    echo <<< EOT
            <!DOCTYPE html>
            <html> 
                <body> 
                    <script>
                        alert("$msg");
                        window.location.replace('nav.php');
                    </script>
                </body> 
            </html> 
        EOT;
}
?>