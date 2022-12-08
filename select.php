<?php
    include 'connect.php';

    if (isset($_POST["s_name"])) {
        $s_name = $_POST['s_name'];

        # fetch shop info
        $stmt = $conn->prepare("select * from meals where binary shop_name = :shop_name");
        $stmt->execute(array('shop_name' => $s_name));
        $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        # fetch meal info
        $stmt = $conn->prepare("select m_name from meals where binary shop_name = :shop_name");
        $stmt->execute(array('shop_name' => $s_name));
        $meal_names = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $all_meal_names = "";
        foreach ($meal_names as $key => $value) {
            foreach ($meal_names[$key] as $key1 => $value1) {
                $m_str = str_replace(" ", "_", $value1);
                $all_meal_names .= $m_str."/";
            }
        }
        
        $s_name_str = str_replace(" ", "_", $s_name);
        $output = '
        <div class="modal-content order-shop" id="'.$s_name_str.'">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">'.$s_name.' Menu</h4>
          </div>
          <div class="modal-body row">
            <div class="col-xs-12">
              <table class="table" style=" margin-top: 15px;">
                <thead>
                  <tr>
                      <th scope="col">#</th>
                      <th scope="col">Picture</th>
                      <th scope="col">Meal Name</th>
                      <th scope="col">Price</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Order</th>
                  </tr>
                </thead>
                <tbody>';
        
        for ($j = 0; $j < count($meals); $j++): 
            $str = str_replace(" ", "_", $meals[$j]['m_name']);
            $next_j = $j + 1;
            $output .= '
                  <tr>
                    <th scope="row">'.$next_j.'</th>
                    <td><img src="data:'.$meals[$j]['m_image_type'].';base64,'.$meals[$j]['m_image'].'" width="100" height="100" /></td>
                    <td>'.$meals[$j]['m_name'].'</td>
                    <td>'.$meals[$j]['m_price'].'</td>
                    <td>'.$meals[$j]['m_quantity'].'</td>
                    <td><input type="button" onclick="decrementValue(num_'.$str.')" value="-">
                        <input type="number" id="num_'.$str.'" name="num_'.$str.'" value=0 style="width: 40px">
                        <input type="button" onclick="incrementValue(num_'.$str.')" value="+"></td>
                  </tr>';
        endfor;

        $output .= '
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <div class="row col-sm-6 flex-container" style="margin-top: 5px">
              <div class="col-sm-2" style="margin-bottom: 5px">Type : </div>
              <div class="col-sm-6">
                <select class="form-control" id="order_type" name="type">
                  <option value="Delivery" selected="true">Delivery</option>
                  <option value="Pick-up">Pick-up</option>
                </select>
              </div>
            </div>
            <button type="button" id="'.$all_meal_names.'"class="btn btn-default order_preview" data-toggle="modal" data-target="#orderModal">Calculate the price</button>
          </div>
        </div>';

        echo $output;
    }
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <title>My Pot</title>
</head>

<body>
  <script>
    $(document).ready(function(){
      $('.order_preview').click(function(){
        var all_meal_names = $(this).attr("id");
        var a = document.getElementById('order_type');
        var value = a.options[a.selectedIndex].value;
        var shop = document.getElementsByClassName('order-shop')[0].id;
        all_meal_names = all_meal_names.split("/");
        all_meal_names.pop();
        dic = {};
        for (i = 0; i < all_meal_names.length; i++) {
            if (document.getElementById('num_'+all_meal_names[i]).value != 0) {
                dic[all_meal_names[i]] = document.getElementById('num_'+all_meal_names[i]).value;
            }
        }        
        $.ajax({
          url: "show_order.php",
          method: "post",
          data: { meal_names : dic, selected : value, order_shop : shop },
          success: function(data){
            $('#confirm_order_list').html(data);
            $('#orderModal').modal("show");
          }
        })
      })
    });   
  </script>
</body>
</html>
