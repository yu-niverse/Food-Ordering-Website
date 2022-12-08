<?php 
include 'connect.php';
if (empty($_SESSION['u_account'])) header('Location: index.html');
$u_account = $_SESSION['u_account'];
$search_info = $_SESSION['search_info'];
$input_data = $_SESSION['input_data'];

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

    <!-- nav tabs -->
    <ul class="nav nav-tabs">
      <li class="active"><a href="nav.php">Home</a></li>
      <li><a href="shop.php">Shop</a></li>
      <li><a href="my_order.php">My Order</a></li>
      <li><a href="shop_order.php">Shop Order</a></li>
      <li><a href="transaction_record.php">Transaction Record</a></li>
    </ul>

    <div class="tab-content">
      <div id="home" class="tab-pane fade in active">

        <h3>Profile</h3>
        <div class="row">
          <div class="col-xs-10">
            <p>Account: <?php echo $u_account ?></p>
            <p>User Name: <?php echo $info['u_name'] ?></p>
            <p>Role: <?php echo $info['u_role'] ?></p>
            <p>Phone Number: <?php echo $info['u_phone'] ?></p>
            <p>Location: <?php echo $info['u_longitude'].' , '.$info['u_latitude'] ?>
              <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal" data-target="#location">Edit Location</button>
            </p>
            <p>Wallet Balance: <?php echo $info['u_wallet_balance'] ?>
                <button type="button " style="margin-left: 5px;" class=" btn btn-info " data-toggle="modal" data-target="#rechargeModal">Recharge</button>
            </p><hr>

            <!-- Modal Edit Location -->
            <div class="modal fade" id="location"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true"> 
              <div class="modal-dialog  modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Edit Location</h4>
                  </div>
                  <form action="edit_location.php" method="post">
                    <div class="modal-body">
                      <div class="form-group">
                        <label class="control-label " for="latitude">Latitude</label>
                        <input type="text" class="form-control" id="latitude" placeholder="Enter latitude" name="new_latitude">
                      </div>
                      <div class="form-group">
                        <label class="control-label " for="longitude">Longitude</label>
                        <input type="text" class="form-control" id="longitude" placeholder="Enter longitude" name="new_longitude">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-default">Edit</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Modal Recharge -->
            <div class="modal fade" id="rechargeModal"  data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
              <div class="modal-dialog  modal-sm">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Recharge</h4>
                  </div>
                  <form action="recharge.php" method="post">
                    <div class="modal-body">
                      <input type="number" name="money" class="form-control" placeholder="Enter Add Value">
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-default recharge">Add</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>
        </div>


        <h3>Search</h3><br>
        <div class="row col-xs-10">
          <form class="form-horizontal" action="search.php" method="post">

            <div class="form-group">
              <label class="control-label col-sm-1" for="Shop">Shop</label>
              <div class="col-sm-5">
                <input type="text" class="form-control" placeholder="Enter Shop Name" name="shop" value="<?php echo $input_data[0]; ?>">
              </div>

              <label class="control-label col-sm-1" for="distance">Distance</label>
              <div class="col-sm-5">
                <select class="form-control" id="sel1" name="distance">
                  <option <?php if ($input_data[1] == 'None') { ?> selected="true" <?php };?> >None</option>
                  <option <?php if ($input_data[1] == 'Near') { ?> selected="true" <?php };?> >Near</option>
                  <option <?php if ($input_data[1] == 'Medium') { ?> selected="true" <?php };?> >Medium </option>
                  <option <?php if ($input_data[1] == 'Far') { ?> selected="true" <?php };?> >Far</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-sm-1" for="Price">Price</label>
              <div class="col-sm-2">
                <input type="text" class="form-control" name="low" value="<?php echo $input_data[2]; ?>">
              </div>
              <label class="control-label col-sm-1" for="~">~</label>
              <div class="col-sm-2">
                <input type="text" class="form-control" name="high" value="<?php echo $input_data[3]; ?>">
              </div>

              <label class="control-label col-sm-1" for="Meal">Meal</label>
              <div class="col-sm-5">
                <input type="text" list="Meals" class="form-control" id="Meal" placeholder="Enter Meal" name="meal" value="<?php echo $input_data[4]; ?>">
                <datalist id="Meals">
                  <option value="Hamburger">
                  <option value="Coffee">
                </datalist>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-sm-1" for="category">Category</label>
              <div class="col-sm-5">
                <input type="text" list="categories" class="form-control" id="category" placeholder="Enter Shop Category" name="s_category" value="<?php echo $input_data[5]; ?>">
                <datalist id="categories">
                  <option value="Fast Food">
                  <option value="Italian">
                  <option value="Hotpot">
                </datalist>
              </div>

              <label class="control-label col-sm-1" for="sort">Sort By</label>
              <div class="col-sm-3">
                <select class="form-control" id="sort" name="sort">
                  <option <?php if ($input_data[6] == 'Shop Name') { ?> selected="true" <?php };?> >Shop Name</option>
                  <option <?php if ($input_data[6] == 'Category') { ?> selected="true" <?php };?> >Category</option>
                  <option <?php if ($input_data[6] == 'Distance') { ?> selected="true" <?php };?> >Distance</option>
                </select>
              </div>
              <div class="col-sm-2">
                <select class="form-control" id="sort2" name="sort2">
                  <option <?php if ($input_data[7] == 'Ascending') { ?> selected="true" <?php };?> >Ascending</option>
                  <option <?php if ($input_data[7] == 'Descending') { ?> selected="true" <?php };?> >Descending</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <br>
              <button type="submit" style="margin-left: 18px;"class="btn btn-primary col-sm-1">Search</button>
            </div>

          </form>
        </div>

        <div class="row col-xs-10">
          <!-- pages -->
          <?php
              $data_nums = count($search_info); 
              $per = 5;
              $pages = ceil($data_nums / $per);
              if (!isset($_GET["page"])) $page = 1;
              else $page = intval($_GET["page"]);

              $start = ($page - 1) * $per;
              $stmt = $conn->prepare($_SESSION['search_sql'].' limit '.$start.', '.$per);
              $stmt->execute($_SESSION['sql_data']);
              $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <table class="table" style=" margin-top: 15px;">
            <thead>
              <tr>
                <th scope="col">#</th>
                <th scope="col">Shop Name</th>
                <th scope="col">Shop Category</th>
                <th scope="col">Distance</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < count($result); $i++): ?>
                <tr>
                  <th scope="row"><?php echo $i + $start + 1; ?></th>
                  <td><?php echo $result[$i]['s_name']; ?></td>
                  <td><?php echo $result[$i]['s_category']; ?></td>
                  <td>
                    <?php 
                      if ($result[$i]['distance'] <= 2000) { echo 'Near ( ' . $result[$i]['distance'] . ' m )';}
                      else if ($result[$i]['distance'] > 2000 && $result[$i]['distance'] <= 5000) { echo 'Medium ( ' . $result[$i]['distance']. ' m )';}
                      else if ($result[$i]['distance'] > 5000) { echo 'Far ( ' . $result[$i]['distance'] . ' m )';}
                    ?>
                  </td>
                  <td> <input type="button" class="btn btn-info open_menu" name="open" value="Open Menu" id="<?php echo $result[$i]['s_name'];?>" > </td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
          <br>
          <?php
            if ($page + 1 <= $pages) $next_page = $page + 1;
            else $next_page = $page;

            if ($page - 1 > 0) $pre_page = $page - 1;
            else $pre_page = $page;

            echo "<a href=?page=".$pre_page.">上ㄧ頁</a> ";
            echo "第 ";
            for ($j = 1; $j <= $pages; $j++) :
              echo "<a href=?page=".$j.">".$j."</a> ";
            endfor;
            echo " 頁 <a href=?page=".$next_page.">下一頁</a> <br> <br>";
          ?>

          <!-- Modal Order-->
          <!-- Modify modal attribute -->
          <div class="modal" id="collapse" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document" >
              <!-- Modal content-->
              <div id="menu-detail"></div>
            </div>
          </div>
          
          <!-- Modal Order Details -->
          <!-- Modify modal attribute -->
          <div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog" role="document" >
              <!-- <div class="modal-body"> -->
                <div class="modal-content">
                  <form action="confirm_order.php" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Order Preview</h4>
                    </div>
                    <div id="confirm_order_list">
                  </form>
                </div>
              <!-- </div> -->
            </div>
          </div>


          
        </div>
      </div>
    </div>
  </div>

  <!-- Option 1: Bootstrap Bundle with Popper -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script> -->
  <script>
    $(document).ready(function () {
      $(".nav-tabs a").click(function () {
        $(this).tab('show');
      });
    });
  </script>

  <script>
    function incrementValue(id) {
      var value = document.getElementById($(id).attr("id")).value;
      value = isNaN(value) ? 0 : value;
      value++;
      document.getElementById($(id).attr("id")).value = value;
    }  
    function decrementValue(id) {
      var value = document.getElementById($(id).attr("id")).value;
      value = isNaN(value) ? 0 : value;
      value--;
      document.getElementById($(id).attr("id")).value = value;
    }  
  </script>

  <script>
    $(document).ready(function() {
      $('.open_menu').click(function() {
        var s_name = $(this).attr("id");
        $.ajax({
          url: "select.php",
          method: "post",
          data: { s_name: s_name },
          success: function(data) {
            $('#menu-detail').html(data);
            $('#collapse').modal("show");
          }
        })
      })
    });
  </script>

<!-- Add CSS -->
  <style>
    .modal-dialog {
        overflow-y: initial;
        height:600px;
    }
    .modal-body{
        max-height: 70vh;
        overflow-y: auto;
    }
    .modal-body::-webkit-scrollbar {
      display: none;
    }
    .modal-dialog::-webkit-scrollbar {
      display: none;
    }
    #collapse {
      overflow: hidden;
    }
  </style>

  <!-- Option 2: Separate Popper and Bootstrap JS -->
  <!--
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js" integrity="sha384-7+zCNj/IqJ95wo16oMtfsKbZ9ccEh31eOz1HGyDuCQ6wgnyJNSYdrPa03rtR1zdB" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>
    -->
</body>
</html>