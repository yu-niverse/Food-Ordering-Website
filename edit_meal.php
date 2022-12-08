<?php
include 'connect.php';

$shop_name = $_SESSION['s_name'];
$stmt = $conn->prepare("select * from meals where binary m_name = :m_name and shop_name = :shop_name");
$stmt->execute(array('m_name' => $_POST['m_name'], 'shop_name' => $shop_name));
$info = $stmt->fetch();

try {

    if (empty($_POST['new_price']) && empty($_POST['new_quantity'])) {
        throw new Exception('Please input at least one column');
    }

    if (!preg_match('/^[0-9]*$/', $_POST['new_price'])) throw new Exception('Wrong price format !!');
    if (!preg_match('/^[0-9]*$/', $_POST['new_quantity'])) throw new Exception('Wrong quantity format !!');

    # assign variables
    if (empty($_POST['new_price'])) {
        $new_price = $info['m_price'];
    } else $new_price = $_POST['new_price'];
    
    if (empty($_POST['new_quantity'])) {
        $new_quantity = $info['m_quantity'];
    } else $new_quantity = $_POST['new_quantity'];


    $stmt = $conn->prepare("update meals set m_price = :m_price, m_quantity = :m_quantity 
                    where binary m_name = :m_name and shop_name = :shop_name");
    $stmt->execute(array('m_price' => $new_price, 'm_quantity' => $new_quantity, 
        'm_name' => $_POST['m_name'], 'shop_name' => $shop_name));
    
    echo <<< EOT
    <!DOCTYPE html>
    <html> 
        <body>
            <script>
                alert('Edit Success !!');
                window.location.replace('shop.php');
            </script>
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
                        window.location.replace('shop.php');
                    </script>
                </body> 
            </html> 
        EOT;
}
?>