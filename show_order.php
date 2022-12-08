<?php
include 'connect.php';

$s_name = str_replace("_", " ", $_POST["order_shop"]);
$meal_names = $_POST["meal_names"];
$_SESSION["meal_names"] = $meal_names;
$delivery_type = $_POST["selected"];
$type = $_POST["selected"];
$u_account = $_SESSION["u_account"];

try {

    $output = '
    <div class="col-xs-12">
        <table class="table" style=" margin-top: 15px;">
            <thead>
                <tr>
                    <th scope="col">Picture</th>
                    <th scope="col">Meal name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Order Quantity</th>
                </tr>
            </thead>
            <tbody>';

    $subtotal = 0;
    foreach ($meal_names as $m_name => $quantity) {
        $m_name = str_replace("_", " ", $m_name);
        if ($quantity < 0) throw new Exception('Wrong order quantity format');
        $stmt = $conn->prepare("select * from meals where m_name = :m_name and shop_name = :s_name");
        $stmt->execute(array('m_name' => $m_name, 's_name' => $s_name));
        $meal = $stmt->fetch();
        $subtotal += $meal['m_price'] * $quantity;
        $output .= '
                <tr>
                    <td><img src="data:'.$meal['m_image_type'].';base64,'.$meal['m_image'].'" width="100" height="100" /></td>
                    <td>'.$meal['m_name'].'</td>
                    <td>'.$meal['m_price'].'</td>
                    <td>'.$quantity.'</td>
                    <input type="hidden" value="'.$quantity.'" name="'.$m_name.'">
                </tr>';
    }        
    
    if ($subtotal == 0) throw new Exception('Please order something');
    if ($delivery_type == "Pick-up") {
        $delivery_fee = 0;
        $distance = 0;
    } else {
        $stmt = $conn->prepare("select * from users where u_account = :u_account");
        $stmt->execute(array('u_account' => $u_account));
        $user_info = $stmt->fetch();
        $u_longitude = $user_info['u_longitude'];
        $u_latitude = $user_info['u_latitude'];
        $stmt = $conn->prepare("select st_distance_sphere(point(:u_longitude, :u_latitude), point(s_longitude, s_latitude)) from shops where s_name = :s_name");
        $stmt->execute(array('u_longitude' => $u_longitude, 'u_latitude' => $u_latitude, 's_name' => $s_name));
        $distance = $stmt->fetch()[0];
        $delivery_fee = round($distance / 100);
        if ($delivery_fee < 10) $delivery_fee = 10;
    }
    $total_price = $subtotal + $delivery_fee;

    $output .= '
            </tbody>
        </table>
    </div>
    </div> </div>
    <div class="modal-footer">
        <p>Subtotal $'.$subtotal.'</p>
        <p>Delivery fee $'.$delivery_fee.'</p>
        <strong>Total Price $'.$total_price.'</strong>
        <br><br>
        <input type="hidden" value="'.$s_name.'" name="order_shop">
        <input type="hidden" value="'.$distance.'" name="distance">
        <input type="hidden" value="'.$subtotal.'" name="subtotal">
        <input type="hidden" value="'.$delivery_fee.'" name="delivery_fee">
        <input type="hidden" value="'.$total_price.'" name="total_price">
        <input type="hidden" value="'.$type.'" name="type">
        <button type="submit" style="margin-right:5px" class="btn btn-default row">Order</button>
    </div>';
    echo $output;
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
                        window.location.replace('nav.php');
                    </script>
                </body> 
            </html> 
        EOT;
}

?>