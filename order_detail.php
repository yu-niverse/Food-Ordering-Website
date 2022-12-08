<?php
    include 'connect.php';

    if (isset($_POST["o_ID"])){
        $o_ID = $_POST["o_ID"];
        // select from orders
        $stmt = $conn->prepare("select * from orders where o_ID = :o_ID");
        $stmt->execute(array('o_ID' => $o_ID));
        $order_info = $stmt->fetch();
        // select from includes
        $stmt = $conn->prepare("select * from includes where order_ID = :o_ID");
        $stmt->execute(array('o_ID' => $o_ID));
        $include_info = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = '<div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">'.'Order ID '.$o_ID.'</h4>
        </div>';
        
        $output = $output.'<div class="modal-body">
        <div class="row">
            <div class="col-xs-12">
            <table class="table" style=" margin-top: 15px;">
                <thead>
                <tr>
                    <th scope="col">Picture</th>
                    <th scope="col">Meal Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Order Quantity</th>
                </tr>
                </thead>
                <tbody>';

        for ($j = 0; $j < count($include_info); $j++): 
            $m_ID = $include_info[$j]['meal_ID'];

            // select from meals
            $stmt = $conn->prepare("select * from meals where m_ID = :m_ID");
            $stmt->execute(array('m_ID' => $m_ID));
            $meal_info = $stmt->fetch();
            $output .= '<tr>
            <td><img src="data:'.$include_info[$j]['m_image_type'].';base64,'.$include_info[$j]['m_image'].'" width="100" height="100" /></td>
            <td>'.$include_info[$j]['m_name'].'</td>
            <td>'.$include_info[$j]['m_price'].'</td>
            <td>'.$include_info[$j]['o_quantity'].'</td>';
        endfor;
        
        $output .= '</tbody>
                </table>
                </div>
            </div></div>
        <div class="modal-footer">
          <p>Subtotal $'.$order_info['o_subtotal'].'</p>
          <p>Delivery fee $'.$order_info['o_delivery_fee'].'</p>
          <strong>Total Price $'.$order_info['o_total_price'].'</strong>
        </div>
      </div>';

      echo $output;
    }    
?>