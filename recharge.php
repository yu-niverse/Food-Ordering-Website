<?php
include 'connect.php';
$u_account = $_SESSION['u_account'];
$stmt = $conn->prepare("select * from users where u_account = :u_account");
$stmt->execute(array('u_account' => $u_account));
$info = $stmt->fetch();
$t = time();
$time = date('Y-m-d H:i:s', $t);

try {
    if (empty($_POST['money'])) 
        throw new Exception('Please input the amount of recharge !!');

    # assign variables
    $money = $_POST['money'];
    if ($money <= 0) 
        throw new Exception('Wrong recharge amount format !!');

    $stmt = $conn->prepare("update users set u_wallet_balance = :money where u_account = :u_account");
    $stmt->execute(array('money' => $money + $info['u_wallet_balance'], 'u_account' => $u_account));

    $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
                        (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
    $stmt->execute(array('user_account' => $u_account, 't_action' => "Recharge", 't_amount' => $money, 
                        't_date' => $time, 't_time' => $time, 't_trader' => $u_account));
    
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