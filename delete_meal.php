<?php
include 'connect.php';
$shop_name = $_SESSION['s_name'];
try {
    if(isset($_POST['delete'])) {

        $stmt = $conn->prepare("select o_state from orders inner join includes on orders.o_ID = includes.order_ID 
            where binary orders.shop_name = :shop_name and includes.meal_ID = :meal_ID");
        $stmt->execute(array('shop_name' => $shop_name, 'meal_ID' => $_POST['m_ID']));
        $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($states); $i++) {
            if ($states[$i]['o_state'] == "Not Finished") {
                throw new Exception("Delete Denied!! The meal is contained in unfinished orders. Try cancel the order instead.");
            }
        }
        
        $stmt = $conn->prepare("delete from meals where binary m_ID = :m_ID and shop_name = :shop_name");
        $stmt->execute(array('m_ID' => $_POST['m_ID'], 'shop_name' => $shop_name));
    }
    header('Location: shop.php');
}
catch (Exception $e) {
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