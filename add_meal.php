<?php 

include 'connect.php';
$s_name = $_SESSION['s_name'];

try {
    # check for empty columns
    if (empty($_POST['m_name']) || empty($_POST['m_price'])
        || empty($_POST['m_quantity']) || $_FILES['m_image']['size'] <= 0) {
            throw new Exception('Please input all the columns !!');
        }

    # assign variables
    $m_name = $_POST['m_name'];
    $m_price = $_POST['m_price'];
    $m_quantity = $_POST['m_quantity'];

    if (!preg_match('/^[0-9]*$/', $m_price)) throw new Exception('Wrong price format !!');
    if (!preg_match('/^[0-9]*$/', $m_quantity)) throw new Exception('Wrong quantity format !!');

    $file = fopen($_FILES["m_image"]["tmp_name"], "rb");
    $fileContents = fread($file, filesize($_FILES["m_image"]["tmp_name"])); 
    fclose($file);
    $fileContents = base64_encode($fileContents);

    # check if the shop name is unique
    $stmt = $conn->prepare("select * from meals where shop_name = :s_name and m_name = :m_name");
    $stmt->execute(array('s_name' => $s_name, 'm_name' => $m_name));
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("insert into meals (m_name, shop_name, m_price, m_quantity, m_image, m_image_type) 
                        values (:m_name, :shop_name, :m_price, :m_quantity, :m_image, :m_image_type);");
        $stmt->execute(array('m_name' => $m_name, 'shop_name' => $s_name, 'm_price' => $m_price, 'm_quantity' => $m_quantity, 'm_image' => $fileContents, 'm_image_type' => $_FILES['m_image']['type']));

        # pop up message
        echo <<< EOT
                <!DOCTYPE html>
                <html> 
                    <body>
                        <script>
                            alert("Meal Added !!");
                            window.location.replace('shop.php');
                        </script>
                    </body> 
                </html> 
            EOT;
        exit();
    } else 
        throw new Exception("The meal has been added !!");
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
                        window.location.replace('shop.php');
                    </script>
                </body> 
            </html> 
        EOT;
}
?>