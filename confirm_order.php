<?php
include 'connect.php';

try {
    $user_account = $_SESSION["u_account"];
    $order_shop = $_POST["order_shop"];
    $subtotal = $_POST["subtotal"];
    $delivery_fee = $_POST["delivery_fee"];
    $type = $_POST["type"];
    $distance = $_POST["distance"];
    $t = time();
    $time = date('Y-m-d H:i:s', $t);
    $meal_names = $_SESSION["meal_names"];

    # fetch meal info
    $stmt = $conn->prepare("select * from meals where shop_name = :shop_name");
    $stmt->execute(array('shop_name' => $order_shop));
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    # fetch shop owner's user account
    $stmt = $conn->prepare("select s_account from shops where s_name = :shop_name");
    $stmt->execute(array('shop_name' => $order_shop));
    $shop_owner = $stmt->fetch()[0];

    # fetch shop owner's wallet balance
    $stmt = $conn->prepare("select u_wallet_balance from users where u_account = :s_account");
    $stmt->execute(array('s_account' => $shop_owner));
    $shop_wallet_money = $stmt->fetch()[0];

    # fetch user's wallet balance & name
    $stmt = $conn->prepare("select u_name, u_wallet_balance from users where u_account = :user_account");
    $stmt->execute(array('user_account' => $user_account));
    $info = $stmt->fetch();
    $user_name = $info['u_name'];
    $wallet_money = $info['u_wallet_balance'];

    # get the new order ID
    $stmt = $conn->prepare("select * from orders");
    $stmt->execute();
    $o_ID = $stmt->rowCount() + 1;

    # check if all meals exist
    foreach ($meal_names as $m_name => $quantity) {
        $m_name = str_replace("_", " ", $m_name);
        $stmt = $conn->prepare("select exists (select m_name from meals where m_name = :m_name and shop_name = :shop_name)");
        $stmt->execute(array('m_name' => $m_name, 'shop_name' => $order_shop));
        $result = $stmt->fetch()[0];
        if ($result == 0) throw new Exception("The order contains deleted meals!!"); 
    }

    # check if the storage is enough
    $error_meals = "";
    $cnt = 0;
    $count_money = 0;
    for ($i = 0; $i < count($meals); $i++) {
        $order_name = str_replace(" ", "_", $meals[$i]['m_name']);
        $m_ID = $meals[$i]["m_ID"];
        $order_quantity = $_POST[$order_name];
        # check if the storage is enough
        if ($order_quantity > $meals[$i]['m_quantity']) {
            if ($cnt == 0) {
                $error_meals .= $meals[$i]['m_name'];
                $cnt += 1;
            } else $error_meals .= ", ".$meals[$i]['m_name'];
        }else if ($order_quantity > 0) {
            $count_money += $order_quantity * $meals[$i]['m_price'];
        }
    }
    if ($error_meals != "") throw new Exception('Insufficient Products : '.$error_meals);

    # check if the money is enough
    $total_price = $count_money + $delivery_fee;
    if ($total_price > $wallet_money) throw new Exception('Insufficient Balance !!');

    $conn->beginTransaction(); 
    #################### if something wrong in this block it will roll back #####################
    # update user wallet balance
    $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :u_account");
    $stmt->execute(array('new_balance' => $wallet_money - $total_price, 'u_account' => $user_account));

    # update shop wallet balance
    if ($user_account == $shop_owner) {
        # if user_account = shop_owner -> recover its original wallet balance
        $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :u_account");
        $stmt->execute(array('new_balance' => $shop_wallet_money, 'u_account' => $shop_owner));
    }else {
        $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :u_account");
        $stmt->execute(array('new_balance' => $shop_wallet_money + $total_price, 'u_account' => $shop_owner));
    }

    # insert into orders
    $stmt = $conn->prepare("insert into orders (o_ID, user_account, shop_name, o_state, o_start_date, o_start_time, o_end_date, 
                            o_end_time, o_distance, o_total_price, o_subtotal, o_delivery_fee, o_type) values 
                            (:o_ID, :user_account, :shop_name, :o_state, :o_start_date, :o_start_time, :o_end_date, 
                            :o_end_time, :o_distance, :o_total_price, :o_subtotal, :o_delivery_fee, :o_type)");
    $stmt->execute(array('o_ID' => $o_ID, 'user_account' => $user_account, 'shop_name' => $order_shop, 'o_state' => "Not Finished", 
        'o_start_date' => $time, 'o_start_time' => $time, 'o_end_date' => NULL, 
        'o_end_time' => NULL, 'o_distance' => $distance, 'o_total_price' => $total_price, 'o_subtotal' => $count_money, 
        'o_delivery_fee' => $delivery_fee, 'o_type' => $type));

    # insert into records
    $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
        (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
    $stmt->execute(array('user_account' => $user_account, 't_action' => "Payment", 't_amount' => -$total_price, 
        't_date' => $time, 't_time' => $time, 't_trader' => $order_shop));

    $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
        (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
    $stmt->execute(array('user_account' => $shop_owner, 't_action' => "Receive", 't_amount' => $total_price, 
        't_date' => $time, 't_time' => $time, 't_trader' => $user_account));


    for ($i = 0; $i < count($meals); $i++) {
        $order_name = str_replace(" ", "_", $meals[$i]['m_name']);
        $m_ID = $meals[$i]["m_ID"];
        $order_quantity = $_POST[$order_name];
        if ($order_quantity > 0) {
            # update meal quantity
            $new_quantity = $meals[$i]["m_quantity"] - $order_quantity;
            $stmt = $conn->prepare("update meals set m_quantity = :m_quantity where m_name = :m_name and shop_name = :shop_name");
            $stmt->execute(array('m_quantity' => $new_quantity, 'm_name' => $meals[$i]["m_name"], 'shop_name' => $order_shop));
            # insert into includes
            $stmt = $conn->prepare("insert into includes (order_ID, meal_ID, o_quantity, m_price, m_name, m_image, m_image_type) values 
                (:order_ID, :meal_ID, :o_quantity, :m_price, :m_name, :m_image, :m_image_type)");
            $stmt->execute(array('order_ID' => $o_ID, 'meal_ID' => $m_ID, 'o_quantity' => $order_quantity, 
                'm_price' => $meals[$i]['m_price'], 'm_name' => $meals[$i]['m_name'], 'm_image' => $meals[$i]['m_image'], 'm_image_type' => $meals[$i]['m_image_type']));
        }
    }
    ######################################################################################
    $conn->commit();

    if ($count_money != $subtotal) throw new Exception('Order Success, but price has updated!!');
    throw new Exception('Order Success !!');
}
catch(Exception $e) {
    # catch transaction error and roll back
    if ($conn->inTransaction()) $conn->rollBack();
    $msg = $e->getMessage();
    # pop up the error message
    if ($msg == 'Order Success !!' || $msg == 'Order Success, but price has updated!!') {
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
    } else {
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
}
?>