<?php

include 'connect.php';
if (empty($_SESSION['u_account'])) header('Location: index.html');
$u_account = $_SESSION['u_account'];
$action = $_POST['action_filter'];

if ($action == "All" || $action == "") {
    $stmt = $conn->prepare("select * from records where user_account = :user_account");
    $stmt->execute(array('user_account' => $u_account));
    $transaction_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("select * from records where user_account = :user_account and t_action = :t_action");
    $stmt->execute(array('user_account' => $u_account, 't_action' => $action));
    $transaction_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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
      <li><a href="shop_order.php">Shop Order</a></li>
      <li class="active"><a href="transaction_record.php">Transaction Record</a></li>
    </ul>

    <div class="tab-content">
        <div id="home" class="tab-pane fade in active">
            <h3>Transaction Record</h3>
            <div class="row col-sm-4">
                <form action="transaction_record.php" method="post">
                    <div class="form-group">
                        <label class="control-label col-sm-2" for="action_filter">Action</label>
                        <div class="col-sm-5">
                            <select class="form-control" id="action_filter" name="action_filter" onchange="this.form.submit()">
                                <option value="All" <?php if ($action != "Payment" || $action != "Receive" || $action != "Recharge") echo 'selected="true"'; ?> >All</option>
                                <option value="Payment" <?php if ($action == "Payment") echo 'selected="true"'; ?> >Payment</option>
                                <option value="Receive" <?php if ($action == "Receive") echo 'selected="true"'; ?> >Receive</option>
                                <option value="Recharge" <?php if ($action == "Recharge") echo 'selected="true"'; ?> >Recharge</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row col-xs-10">
                <table class="table" style=" margin-top: 15px;">
                <thead>
                <tr>
                    <th scope="col">Record ID</th>
                    <th scope="col">Action</th>
                    <th scope="col">Time</th>
                    <th scope="col">Trader</th>
                    <th scope="col">Amount Change</th>
                </tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < count($transaction_records); $i++): ?>
                    <tr>
                    <th scope="row"><?php echo $transaction_records[$i]['t_ID']; ?></th>
                    <td><?php echo $transaction_records[$i]['t_action']; ?></td>
                    <td><?php echo $transaction_records[$i]['t_date']." ".$transaction_records[$i]['t_time']; ?></td>
                    <td><?php echo $transaction_records[$i]['t_trader']; ?></td>
                    <td><?php if ($transaction_records[$i]['t_amount'] > 0) echo "+"; ?><?php echo $transaction_records[$i]['t_amount']; ?></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
          <br>
        </div>
      </div>
    </div>
  </div>
  </body>
</html>