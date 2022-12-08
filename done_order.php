<?php
    include 'connect.php';

    try {
       if ($_POST["bonus"] == "yes") {
            foreach ($_POST["selected_orders"] as $i) {
                $stmt = $conn->prepare("select * from orders where o_ID = :o_ID");
                $stmt->execute(array('o_ID' => $i));
                $state = $stmt->fetch()["o_state"];
                if ($state == "Cancelled") {
                    echo <<< EOT
                                <!DOCTYPE html>
                                <html> 
                                    <body> 
                                        <script>
                                            alert("Some order has been cancelled");
                                        </script>
                                    </body> 
                                </html> 
                            EOT;
                            break;
                }
                $stmt = $conn->prepare("update orders set o_state='Finished', o_end_date=CURRENT_TIMESTAMP, 
                                    o_end_time=CURRENT_TIMESTAMP where o_ID = :o_ID");
                $stmt->execute(array('o_ID' => $i));
            }
        } else {
            $stmt = $conn->prepare("select * from orders where o_ID = :o_ID");
            $stmt->execute(array('o_ID' => $_POST['o_ID']));
            $state = $stmt->fetch()["o_state"];
            # Cancelled or Canceled ?
            if ($state == "Cancelled")
                throw new Exception('The order has already been cancelled !!');
            $stmt = $conn->prepare("update orders set o_state='Finished', o_end_date=CURRENT_TIMESTAMP, 
                                    o_end_time=CURRENT_TIMESTAMP where o_ID = :o_ID");
            $stmt->execute(array('o_ID' => $_POST['o_ID']));
            # move to end
            header('Location: shop_order.php'); 
        }
    }
    # catch the exceptions
    catch(Exception $e) {
        $msg = $e->getMessage();
        # pop up the error message
        echo <<< EOT
                <!DOCTYPE html>
                <html> 
                    <body> 
                        <script>
                            alert("$msg");
                            window.location.replace('shop_order.php');
                        </script>
                    </body> 
                </html> 
            EOT;
    }
?>