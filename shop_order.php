<?php
include 'connect.php';
if (empty($_SESSION['u_account'])) header('Location: index.html');
$u_account = $_SESSION['u_account'];
$s_name = $_SESSION['s_name'];
$state = $_POST['state_filter'];

# fetch order info & filter by order state
if ($state == "All" || $state == "") {
    $stmt = $conn->prepare("select * from orders where shop_name = :s_name");
    $stmt->execute(array('s_name' => $s_name));
    $my_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num_orders = $stmt->rowCount();
} else {
    $stmt = $conn->prepare("select * from orders where shop_name = :s_name and o_state = :o_state");
    $stmt->execute(array('s_name' => $s_name, 'o_state' => $state));
    $my_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num_orders = $stmt->rowCount();
}

# fetch user info
$stmt = $conn->prepare("select * from users where u_account = :u_account");
$stmt->execute(array('u_account' => $_SESSION['u_account']));
$info = $stmt->fetch();
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Bootstrap CSS -->

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
  <title>My Pot</title>
</head>

<body>
 
  <nav class="navbar navbar-inverse">
    <div class="container-fluid">
      <div class="navbar-header">
        <a class="navbar-brand " href="#">My Pot</a>
        <a type="button" class="btn btn-default navbar-btn" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container">

    <ul class="nav nav-tabs">
      <li><a href="nav.php">Home</a></li>
      <li><a href="shop.php">Shop</a></li>
      <li><a href="my_order.php">My Order</a></li>
      <li class="active"><a href="shop_order.php">Shop Order</a></li>
      <li><a href="transaction_record.php">Transaction Record</a></li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
            <h3>Shop Order</h3>
            <!-- Check if the user is a manager -->
            <?php if ($info['u_role'] == 'User') : ?>
              <br>
              <h4>Please Start a Business First</h4>
            <?php else : ?>
            <div class="row col-sm-4">
                <form action="shop_order.php" method="post">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="state_filter">Status</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="state_filter" name="state_filter" onchange="this.form.submit()">
                                <option value="All" <?php if ($state != "Finished" || $state != "Not Finished" || $state != "Cancelled") echo 'selected="true"'; ?> >All</option>
                                <option value="Finished" <?php if ($state == "Finished") echo 'selected="true"'; ?> >Finished</option>
                                <option value="Not Finished" <?php if ($state == "Not Finished") echo 'selected="true"'; ?> >Not Finished</option>
                                <option value="Cancelled" <?php if ($state == "Cancelled") echo 'selected="true"'; ?> >Cancelled</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <br>
            <div class="col-sm-5">
              <input type="button" class="btn btn-success col-sm-4 get_num_orders" value="Done Selected" id="<?php echo $num_orders; ?>">
              <form action="cancel_order.php" method="post" id="cancel-selected">
                <input type="hidden" value="yes" name="bonus">
                <!-- move from line 124 to here -->
                <input type="hidden" value="shop_order" name="from">
                <!-- move -->
                <input type="submit" class="btn btn-danger col-sm-4" value="Cancel Selected" style="margin-left: 5px">
              </form>
            </div>
            <div class="row col-xs-11">
                <table class="table" style=" margin-top: 15px;">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Order ID</th>
                    <th scope="col">State</th>
                    <th scope="col">Start</th>
                    <th scope="col">End</th>
                    <th scope="col">Shop name</th>
                    <th scope="col">Total Price</th>
                    <th scope="col">Order Details</th>
                    <th scope="col">Action</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($my_orders); $i++): ?>
                    <tr>
                    <td>
                        <?php
                          if ($my_orders[$i]['o_state'] == 'Not Finished') {
                            echo '<input type="checkbox" form="cancel-selected" id="selected_'.$my_orders[$i]['o_ID'].'" name="bonus_'.$my_orders[$i]['o_ID'].'" value="'.$my_orders[$i]['o_ID'].'">';
                            echo '<input form="cancel-selected" type="hidden" value="'.$my_orders[$i]['shop_name'].'" name="bonus_'.$my_orders[$i]['o_ID'].'_order_shop">
                                  <input form="cancel-selected" type="hidden" value="'.$my_orders[$i]['o_total_price'].'" name="bonus_'.$my_orders[$i]['o_ID'].'_total_price">
                                  <input form="cancel-selected" type="hidden" value="'.$my_orders[$i]['user_account'].'" name="bonus_'.$my_orders[$i]['o_ID'].'_user">';
                          }
                        ?>
                    </td>
                    <th scope="row"><?php echo $my_orders[$i]['o_ID']; ?></th>
                    <td><?php echo $my_orders[$i]['o_state']; ?></td>
                    <td><?php echo $my_orders[$i]['o_start_date']." ".$my_orders[$i]['o_start_time']; ?></td>
                    <td><?php echo $my_orders[$i]['o_end_date']." ".$my_orders[$i]['o_end_time']; ?></td>
                    <td><?php echo $my_orders[$i]['shop_name']; ?></td>
                    <td><?php echo $my_orders[$i]['o_total_price']; ?></td>
                    <td><input type="button" class="btn btn-info order_detail" name="open" value="Order Details" id="<?php echo $my_orders[$i]['o_ID'];?>" > </td>
                    <td>
                      <?php
                          if ($my_orders[$i]['o_state'] == 'Not Finished') {
                            echo '<form action="done_order.php" method="post">
                                    <input type="hidden" value="'.$my_orders[$i]['o_ID'].'" name="o_ID">
                                    <button type="submit" class="btn btn-success" name="done" id="'.$result[$i]['o_ID'].'">Done</button>
                                  </form>';
                          }
                      ?>
                    </td>
                    <td>
                        <?php
                          if ($my_orders[$i]['o_state'] == 'Not Finished') {
                            echo '<form action="cancel_order.php" method="post" id="cancel'.$my_orders[$i]['o_ID'].'">
                                    <input form="cancel'.$my_orders[$i]['o_ID'].'" type="hidden" value="'.$my_orders[$i]['o_ID'].'" name="o_ID">
                                    <input form="cancel'.$my_orders[$i]['o_ID'].'" type="hidden" value="shop_order" name="from">
                                    <input form="cancel'.$my_orders[$i]['o_ID'].'" type="hidden" value="'.$my_orders[$i]["user_account"].'" name="user">
                                    <input form="cancel'.$my_orders[$i]['o_ID'].'" type="hidden" value="'.$my_orders[$i]["shop_name"].'" name="order_shop">
                                    <input form="cancel'.$my_orders[$i]['o_ID'].'" type="hidden" value="'.$my_orders[$i]['o_total_price'].'" name="total_price">
                                    <button form="cancel'.$my_orders[$i]['o_ID'].'" type="submit" class="btn btn-danger" name="cancel" id="'.$result[$i]['o_ID'].'">Cancel</button>
                                  </form>';
                          }
                        ?>
                    </td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
          <br>

          <!-- Order Detail Modal -->
          <div class="modal fade" id="collapse" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
              <!-- Modal content-->
                <div class="modal-body" id="order-detail"></div>
            </div>
          </div>


        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function(){
      $('.order_detail').click(function(){
        var o_ID = $(this).attr("id");
        $.ajax({
          url: "order_detail.php",
          method: "post", 
          data: { o_ID: o_ID },
          success: function(data){
            $('#order-detail').html(data);
            $('#collapse').modal("show");
          }
        })
      })
    })
  </script>
  <script>
    $(document).ready(function(){
      $('.get_num_orders').click(function(){
        var num_orders = $(this).attr("id");
        var selected_orders = [];
        for (i = 0; i <= 5 * num_orders; i++) {
          checked = document.getElementById('selected_' + i);
          if (checked != null) {
            if (checked.checked)
              selected_orders.push(i);
          }
        }
        $.ajax({
          url: "done_order.php",
          method: "post", 
          data: { selected_orders: selected_orders, bonus: "yes" },
          success: function(data) { 
            console.log(data);
            $('body').html(data);
            location.reload();
          }
        })
      })
    })
  </script>
  </body>
</html>