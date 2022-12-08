<?php 
include 'connect.php';
if (empty($_SESSION['u_account'])) header('Location: index.html');
$u_account = $_SESSION['u_account'];

# update user role
$stmt = $conn->prepare("select * from shops where s_account = :u_account");
$stmt->execute(array('u_account' => $u_account));
if ($stmt->rowCount() == 1) {
    $sql = $conn->prepare("update users set u_role = :u_role where u_account = :u_account");
    $sql->execute(array('u_role' => 'Manager', 'u_account' => $u_account));
} else {
    $sql = $conn->prepare("update users set u_role = :u_role where u_account = :u_account");
    $sql->execute(array('u_role' => 'User', 'u_account' => $u_account));
}

# fetch user info
$stmt = $conn->prepare("select * from users where u_account = :u_account");
$stmt->execute(array('u_account' => $u_account));
$info = $stmt->fetch();

# fetch shop info
$stmt = $conn->prepare("select * from shops where s_account = :u_account");
$stmt->execute(array('u_account' => $u_account));
if ($stmt->rowCount() == 1)
  $shop_info = $stmt->fetch();

# fetch shop menu
$stmt = $conn->prepare("select * from meals where binary shop_name = :s_name");
$stmt->execute(array('s_name' => $shop_info['s_name']));
if ($stmt->rowCount() != 0) {
    $flag = true;
    $meal_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else $flag = false;

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
        <li class="active"><a href="shop.php">Shop</a></li>
        <li><a href="my_order.php">My Order</a></li>
        <li><a href="shop_order.php">Shop Order</a></li>
        <li><a href="transaction_record.php">Transaction Record</a></li>
        </ul>
        <div class="tab-content">
            <div id="menu1" class="tab-pane fade in active">
                <?php if ($info['u_role'] == 'User') : ?>
                    <h3> Start a Business </h3>

                    <form action="register_shop.php" method="post">
                    <div class="row">
                        <div class="col-xs-3">
                        <label for="ex5">Shop Name</label>
                        <input class="form-control shop_name" id="ex5" placeholder="Enter a shop name" type="text" name="s_name">
                        <div id="shop_name_response"></div>
                        </div>
                        <div class="col-xs-2">
                        <label for="ex5">Shop Category</label>
                        <input class="form-control" id="ex5" placeholder="Enter category" type="text" name="s_category">
                        </div>
                        <div class="col-xs-2">
                        <label for="ex6">Longitude</label>
                        <input class="form-control" id="ex6" placeholder="Enter longitude" type="text" name="s_longitude">
                        </div>
                        <div class="col-xs-2">
                        <label for="ex8">Latitude</label>
                        <input class="form-control" id="ex8" placeholder="Enter latitude" type="text" name="s_latitude">
                        </div>
                    </div>

                    <div class=" row" style=" margin-top: 25px;">
                        <div class=" col-xs-3">
                        <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </div>
                    </form>
                    <hr>
                <?php else: ?>
                    <h3><?php echo $shop_info['s_name']; ?></h3>
                    <form action="register_shop.php" method="post">
                    <div class="row">
                        <div class="col-xs-2">
                        <label for="ex5">Shop Name</label>
                        <input class="form-control" id="ex5" placeholder="<?php echo $shop_info['s_name']; ?>" type="text" name="s_name" disabled="disabled">
                        </div>
                        <div class="col-xs-2">
                        <label for="ex5">Shop Category</label>
                        <input class="form-control" id="ex5" placeholder="<?php echo $shop_info['s_category']; ?>" type="text" name="s_category" disabled="disabled">
                        </div>
                        <div class="col-xs-2">
                        <label for="ex6">Longitude</label>
                        <input class="form-control" id="ex6" placeholder="<?php echo $shop_info['s_longitude']; ?>" type="text" name="s_longitude" disabled="disabled">
                        </div>
                        <div class="col-xs-2">
                        <label for="ex8">Latitude</label>
                        <input class="form-control" id="ex8" placeholder="<?php echo $shop_info['s_latitude']; ?>" type="text" name="s_latitude" disabled="disabled">
                        </div>
                    </div>

                    <div class=" row" style=" margin-top: 25px;">
                        <div class=" col-xs-3">
                        <button type="submit" class="btn btn-primary" disabled="disabled">Register</button>
                        </div>
                    </div>
                    </form>
                    <hr>
                    <h3>ADD</h3>
                    <form class="form-group" action="add_meal.php" method="post" Enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-xs-6">
                        <label for="ex3">Meal Name</label>
                        <input class="form-control" id="ex3" type="text" name="m_name">
                        </div>
                    </div>
                    <div class="row" style=" margin-top: 15px;">
                        <div class="col-xs-3">
                        <label for="ex7">Price</label>
                        <input class="form-control" id="ex7" type="text" name="m_price">
                        </div>
                        <div class="col-xs-3">
                        <label for="ex4">Quantity</label>
                        <input class="form-control" id="ex4" type="text" name="m_quantity">
                        </div>
                    </div>
            
                    <div class="row" style=" margin-top: 25px;">
                        <div class=" col-xs-3">
                        <label for="ex12">上傳圖片</label>
                        <input id="myFile" type="file" name="m_image" multiple class="file-loading">
                        </div>
                        <div class=" col-xs-3">
                        <button style=" margin-top: 15px;" type="submit" class="btn btn-primary">Add</button>
                        </div>
                    </div>
                    </form>

                    <div class="row">
                    <div class="  col-xs-8">
                    <table class="table" style=" margin-top: 15px;">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Picture</th>
                            <th scope="col">Meal Name</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Edit</th>
                            <th scope="col">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php for ($i = 0; $i < count($meal_info); $i++): ?>
                        <tr>
                            <?php $str = str_replace(" ", "_", $meal_info[$i]['m_name']); ?>
                            <th scope="row"><?php echo $i + 1; ?></th>
                            <td><?php echo '<img src="data:'.$meal_info[$i]['m_image_type'].';base64,'.$meal_info[$i]['m_image'].'" width="80" height="80" />'; ?></td>
                            <td><?php echo $meal_info[$i]['m_name']; ?></td>
                            <td><?php echo $meal_info[$i]['m_price']; ?></td>
                            <td><?php echo $meal_info[$i]['m_quantity']; ?></td>
                            <td><button type="button" class="btn btn-info" data-toggle="modal" data-target="<?php echo '#' . $str; ?>">Edit</button></td>

                            <!-- Modal -->
                            <div class="modal fade" id="<?php echo $str; ?>" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form action="edit_meal.php" method="post">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="staticBackdropLabel"><?php echo $meal_info[$i]['m_name'] . ' Edit'; ?></h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row" >
                                                    <div class="col-xs-6">
                                                        <label for="ex71">Price</label>
                                                        <input class="form-control" id="ex71" type="text" name="new_price">
                                                    </div>
                                                    <div class="col-xs-6">
                                                        <label for="ex41">Quantity</label>
                                                        <input class="form-control" id="ex41" type="text" name="new_quantity">
                                                    </div>
                                                    <input type="hidden" value="<?php echo $meal_info[$i]['m_name']; ?>" name="m_name">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-secondary">Edit</button>    
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <td>
                                <form action="delete_meal.php" method="post">
                                    <input type="hidden" value="<?php echo $meal_info[$i]['m_ID']; ?>" name="m_ID">
                                    <button type="submit" class="btn btn-danger" name="delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endfor; ?>
                        </tbody>
                    </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function () {
      $(".nav-tabs a").click(function () {
        $(this).tab('show');
      });
    });

    $(document).ready(function() {
        $('.shop_name').keyup(function() {
            var s_name = $(this).val().trim();

            if (s_name != '') {
                $.ajax({
                    url:'check_shop.php',
                    type: 'post',
                    data: { s_name: s_name },
                    success: function(response) {
                        $('#shop_name_response').html(response);
                    }
                });
            }else {
                $('#shop_name_response').html("");
            }
        });
    });
    </script>
</body>
</html>
