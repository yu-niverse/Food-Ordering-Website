<?php
    include 'connect.php';
    if (empty($_POST["from"])) $back = $_POST["bonus_from"].'.php';
    else $back = $_POST["from"].'.php';

    
    try {
        
    if ($_POST["bonus"] == "yes") {

        $stmt = $conn->prepare("select * from orders");
        $stmt->execute();
        $num_order = $stmt->rowCount();

        for ($i = 1; $i <= $num_order; $i++) {
            # if selected
            if ($_POST["bonus_".$i]) { 
                $t = time();
                $time = date('Y-m-d H:i:s', $t);   
                $order_shop = $_POST["bonus_".$i."_order_shop"];
                $total_price = $_POST["bonus_".$i."_total_price"];
                $user = $_POST["bonus_".$i."_user"];

                # ckeck state
                $stmt = $conn->prepare("select * from orders where o_ID = :o_ID");
                $stmt->execute(array('o_ID' => $_POST['bonus_'.$i]));
                $order_state = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["o_state"];
                if ($order_state == "Finished") 
                    throw new Exception("Order ".$i." has already been finished, Please try again !!");
                elseif ($order_state == "Cancelled")
                    throw new Exception("Order ".$i." has already cancelled, Please try again !!");

                # fetch shop owner's info
                $stmt = $conn->prepare("select s_account from shops where s_name = :shop_name");
                $stmt->execute(array('shop_name' => $order_shop));
                $shop_owner = $stmt->fetch()[0];

                $stmt = $conn->prepare("select u_wallet_balance from users where u_account = :s_account");
                $stmt->execute(array('s_account' => $shop_owner));
                $shop_wallet_money = $stmt->fetch()[0];

                # fetch customer info
                $stmt = $conn->prepare("select u_name, u_wallet_balance from users where u_account = :user_account");
                $stmt->execute(array('user_account' => $user));
                $info = $stmt->fetch();
                $user_name = $info['u_name'];
                $wallet_money = $info['u_wallet_balance'];

                # update orders end time & status
                $stmt = $conn->prepare("update orders set o_state='Cancelled', o_end_date=CURRENT_TIMESTAMP, 
                o_end_time=CURRENT_TIMESTAMP where o_ID = :o_ID");
                $stmt->execute(array('o_ID' => $_POST['bonus_'.$i]));

                # insert into records : user collection
                $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
                    (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
                $stmt->execute(array('user_account' => $user, 't_action' => "Receive", 't_amount' => $total_price, 
                    't_date' => $time, 't_time' => $time, 't_trader' => $order_shop));
                
                # insert into records : shop payment
                $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
                    (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
                $stmt->execute(array('user_account' => $shop_owner, 't_action' => "Payment", 't_amount' => -$total_price, 
                    't_date' => $time, 't_time' => $time, 't_trader' => $user));

                # update user's wallet balance
                $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :u_account");
                $stmt->execute(array('new_balance' => $wallet_money + $total_price, 'u_account' => $user));

                # update shop owner's wallet balance
                $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :s_account");
                $stmt->execute(array('new_balance' => $shop_wallet_money - $total_price, 's_account' => $shop_owner));

                # update meal storage
                $stmt = $conn->prepare("select * from includes where order_ID = :o_ID");
                $stmt->execute(array('o_ID' => $_POST['bonus_'.$i]));
                $includes_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $num_includes = $stmt->rowCount();
                
                for ($j = 0; $j < $num_includes; $j++) {
                    $stmt = $conn->prepare("select m_quantity from meals where m_ID = :meal_ID");
                    $stmt->execute(array('meal_ID' => $includes_info[$j]['meal_ID']));
                    $quantity = $stmt->fetch()[0];

                    $stmt = $conn->prepare("update meals set m_quantity = :original_quantity where m_ID = :meal_ID");
                    $stmt->execute(array('original_quantity' => $quantity + $includes_info[$j]['o_quantity'], 'meal_ID' => $includes_info[$j]['meal_ID']));
                }    
            }
        }
        # redirect to the previous page
        if ($_POST["bonus_from"] == "my_order") header('Location: my_order.php');
        else header('Location: shop_order.php');

    } else {
        $t = time();
        $time = date('Y-m-d H:i:s', $t);   
        $order_shop = $_POST["order_shop"];
        $total_price = $_POST["total_price"];
        $user = $_POST["user"];

        # ckeck state
        $stmt = $conn->prepare("select * from orders where o_ID = :o_ID");
        $stmt->execute(array('o_ID' => $_POST['o_ID']));
        $order_state = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["o_state"];
        if ($order_state == "Finished") 
            throw new Exception("Shop owner has already finished !!");
        elseif ($order_state == "Cancelled")
            throw new Exception("User has already cancelled !!");


        # fetch shop owner's info
        $stmt = $conn->prepare("select s_account from shops where s_name = :shop_name");
        $stmt->execute(array('shop_name' => $order_shop));
        $shop_owner = $stmt->fetch()[0];

        $stmt = $conn->prepare("select u_wallet_balance from users where u_account = :s_account");
        $stmt->execute(array('s_account' => $shop_owner));
        $shop_wallet_money = $stmt->fetch()[0];

        # fetch customer info
        $stmt = $conn->prepare("select u_name, u_wallet_balance from users where u_account = :user_account");
        $stmt->execute(array('user_account' => $user));
        $info = $stmt->fetch();
        $user_name = $info['u_name'];
        $wallet_money = $info['u_wallet_balance'];

        # update orders end time & status
        $stmt = $conn->prepare("update orders set o_state='Cancelled', o_end_date=CURRENT_TIMESTAMP, 
        o_end_time=CURRENT_TIMESTAMP where o_ID = :o_ID");
        $stmt->execute(array('o_ID' => $_POST['o_ID']));

        # insert into records : user collection
        $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
            (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
        $stmt->execute(array('user_account' => $user, 't_action' => "Receive", 't_amount' => $total_price, 
            't_date' => $time, 't_time' => $time, 't_trader' => $order_shop));
        
        # insert into records : shop payment
        $stmt = $conn->prepare("insert into records (user_account, t_action, t_amount, t_date, t_time, t_trader) values 
            (:user_account, :t_action, :t_amount, :t_date, :t_time, :t_trader)");
        $stmt->execute(array('user_account' => $shop_owner, 't_action' => "Payment", 't_amount' => -$total_price, 
            't_date' => $time, 't_time' => $time, 't_trader' => $user));

        # update user's wallet balance
        $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :u_account");
        $stmt->execute(array('new_balance' => $wallet_money + $total_price, 'u_account' => $user));

        # update shop owner's wallet balance
        $stmt = $conn->prepare("update users set u_wallet_balance = :new_balance where u_account = :s_account");
        $stmt->execute(array('new_balance' => $shop_wallet_money - $total_price, 's_account' => $shop_owner));

        # update meal storage
        $stmt = $conn->prepare("select * from includes where order_ID = :o_ID");
        $stmt->execute(array('o_ID' => $_POST['o_ID']));
        $includes_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        for ($i = 0; $i < count($includes_info); $i++) {
            $stmt = $conn->prepare("select m_quantity from meals where m_ID = :meal_ID");
            $stmt->execute(array('meal_ID' => $includes_info[$i]['meal_ID']));
            $quantity = $stmt->fetch()[0];

            $stmt = $conn->prepare("update meals set m_quantity = :original_quantity where m_ID = :meal_ID");
            $stmt->execute(array('original_quantity' => $quantity + $includes_info[$i]['o_quantity'], 'meal_ID' => $includes_info[$i]['meal_ID']));
        }

        # redirect to the previous page
        if ($_POST["from"] == "my_order") header('Location: my_order.php');
        else header('Location: shop_order.php');
        }
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
                            window.location.replace("$back");
                        </script>
                    </body> 
                </html> 
            EOT;
    }
?>