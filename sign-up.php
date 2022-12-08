<?php

include 'connect.php';
$_SESSION['Authenticated'] = false;

try {
    
    # check for empty columns
    if ($_POST['u_name']=="" || $_POST['u_phone']=="" || $_POST['u_account']==""
        || $_POST['u_password']=="" || $_POST['u_password2']=="" 
        || $_POST['u_latitude']=="" || $_POST['u_longitude']=="") {
            throw new Exception('Please input all the columns');
        }

    # assign variables
    $u_name = $_POST['u_name'];
    $u_phone = $_POST['u_phone'];
    $u_account = $_POST['u_account'];
    $u_password = $_POST['u_password'];
    $u_password2 = $_POST['u_password2'];
    $u_latitude = $_POST['u_latitude'];
    $u_longitude = $_POST['u_longitude'];
    $u_position = "POINT($u_longitude $u_latitude)";
    
    # checking constraints :
    # 1. make sure re-type password = password
    if ($u_password != $u_password2) throw new Exception('Please re-type the password correctly !!');
    # 2. make sure the formats are correct
    if (!preg_match('/^[a-zA-Z0-9]*$/', $u_account)) throw new Exception('Wrong account format !!');
    if (!preg_match('/^[a-zA-Z0-9]*$/', $u_password)) throw new Exception('Wrong password format !!');
    if (!preg_match('/^[a-zA-Z]*$/', $u_name)) throw new Exception('Wrong name format !!');
    if (!preg_match('/^[0-9]{10}$/', $u_phone)) throw new Exception('Wrong phone format !!');
    if ($u_latitude < -90 || $u_latitude > 90) throw new Exception('Wrong latitude format !!');
    if ($u_longitude < -180 || $u_longitude > 180) throw new Exception('Wrong longitude format !!');

    # check if the account number is unique
    $stmt = $conn->prepare("select u_account from users where u_account = :u_account");
    $stmt->execute(array('u_account' => $u_account));
    if ($stmt->rowCount() == 0) {

        $salt = strval(rand(1000, 9999));
        $hashvalue = hash('sha256', $salt.$u_password);
        $stmt = $conn->prepare("insert into users (u_account, u_password, salt, u_name, u_phone, u_longitude, u_latitude, u_position, u_role, u_wallet_balance) 
                        values (:u_account, :u_password, :salt, :u_name, :u_phone, :u_longitude, :u_latitude, ST_GeomFromText(:u_position), :u_role, :u_wallet_balance);");
        $stmt->execute(array('u_account' => $u_account, 'u_password' => $hashvalue, 'u_name' => $u_name,
                             'u_phone' => $u_phone, 'u_longitude' => $u_longitude, 'u_latitude' => $u_latitude, 'u_position' => $u_position, 'salt' => $salt, 'u_role' => 'User', 'u_wallet_balance' => 0));
        
        # sign up successfully
        $_SESSION['Authenticated'] = true;
        $_SESSION['u_account'] = $u_account;
        # pop up the message
        echo <<< EOT
                <!DOCTYPE html>
                <html> 
                    <body>
                        <script>
                            alert("Register success !!");
                            window.location.replace('index.html');
                        </script>
                    </body> 
                </html> 
            EOT;
        exit();
    } else
        throw new Exception("Account has been registered !!");
} 

# catch the exceptions
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
                        window.location.replace('sign-up.html');
                    </script>
                </body> 
            </html> 
        EOT;
}

?>


