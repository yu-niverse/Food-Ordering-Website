<?php

include 'connect.php';
$_SESSION['Authenticated'] = false;

try {
    
    # check for empty columns
    if ($_POST['u_account']=="" || $_POST['u_password']=="") {
        throw new Exception('Please input all the columns !!');
    }

    $u_account = $_POST['u_account'];
    $u_password = $_POST['u_password'];

    $stmt = $conn->prepare("select * from users where u_account = :u_account");
    $stmt->execute(array('u_account' => $u_account));
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        if ($row['u_password'] == hash('sha256', $row['salt'].$_POST['u_password'])) {
            $_SESSION['Authenticated'] = true;
            $_SESSION['u_account'] = $row['u_account'];
            if ($row['u_role'] == 'Manager') {
                $stmt = $conn->prepare("select s_name from shops where s_account = :u_account");
                $stmt->execute(array('u_account' => $row['u_account']));
                $s_name = $stmt->fetch();
                $_SESSION['s_name'] = $s_name['s_name'];
            }
            # pop up the message
            echo <<< EOT
                    <!DOCTYPE html>
                    <html> 
                        <body>
                            <script>
                                window.location.replace('nav.php');
                            </script>
                        </body> 
                    </html> 
                EOT;
            exit();
        } else 
            throw new Exception('Try another password !!');
    } else 
        throw new Exception('No such account !!');
}

catch(Exception $e) {
    $msg = $e->getMessage();
    session_unset();
    session_destroy();
    # pop up the error message
    echo <<< EOT
            <!DOCTYPE html>
            <html> 
                <body> 
                    <script>
                        alert("$msg");
                        window.location.replace('index.html');
                    </script>
                </body> 
            </html> 
        EOT;
}

?>

