<?php
include 'connect.php';
$u_account = $_SESSION['u_account'];

try {

    # assign variables
    $shop = $_POST['shop'];
    $distance = $_POST['distance'];
    $low = $_POST['low'];
    $high = $_POST['high'];
    $meal = $_POST['meal'];
    $s_category = $_POST['s_category'];
    $sort = $_POST['sort'];
    $sort2 = $_POST['sort2'];
    $_SESSION['input_data'] = array($shop, $distance, $low, $high, $meal, $s_category, $sort, $sort2);

    if (!preg_match('/^[0-9]*$/', $low)) throw new Exception('Wrong low format !!');
    if (!preg_match('/^[0-9]*$/', $high)) throw new Exception('Wrong high format !!');

    # get user's current position
    $stmt = $conn->prepare("select * from users where u_account = :u_account");
    $stmt->execute(array('u_account' => $u_account));
    $user_info = $stmt->fetch();
    $u_longitude = $user_info['u_longitude'];
    $u_latitude = $user_info['u_latitude'];
    $sql_data['u_longitude'] = $u_longitude;
    $sql_data['u_latitude'] = $u_latitude;

    $condition = "select distinct shops.s_name, shops.s_category, st_distance_sphere(point(:u_longitude, :u_latitude), point(shops.s_longitude, shops.s_latitude)) as distance
         from shops left outer join meals on shops.s_name = meals.shop_name where shops.s_name like '%'";
    
    if ($shop) {
        $condition = $condition . " and shops.s_name like :shop";
        $sql_data['shop'] = '%'.$shop.'%';
    }
    if ($distance != 'None') {
        if ($distance == 'Near')
            $condition = $condition . " and st_distance_sphere(point(:u_longitude, :u_latitude), point(shops.s_longitude, shops.s_latitude)) <= 2000";
        if ($distance == 'Medium')
            $condition = $condition . " and st_distance_sphere(point(:u_longitude, :u_latitude), point(shops.s_longitude, shops.s_latitude)) > 2000 
                and st_distance_sphere(point(:u_longitude, :u_latitude), point(shops.s_longitude, shops.s_latitude)) <= 5000";     
        if ($distance == 'Far')
            $condition = $condition . " and st_distance_sphere(point(:u_longitude, :u_latitude), point(shops.s_longitude, shops.s_latitude)) > 5000";
    }
    if ($low) {
        $condition = $condition . " and meals.m_price >= :low";
        $sql_data['low'] = $low;
    }
    if ($high) {
        $condition = $condition . " and meals.m_price <= :high";
        $sql_data['high'] = $high;
    }
    if ($meal) {
        $condition = $condition . " and meals.m_name like :meal";
        $sql_data['meal'] = '%'.$meal.'%';
    }
    if ($s_category) {
        $condition = $condition . " and shops.s_category like :s_category";
        $sql_data['s_category'] = '%'.$s_category.'%';
    }

    # Sort
    if ($sort == 'Shop Name') $condition = $condition . " order by shops.s_name";
    if ($sort == 'Category') $condition = $condition . " order by shops.s_category";
    if ($sort == 'Distance') $condition = $condition . " order by st_distance_sphere(point($u_longitude, $u_latitude), point(shops.s_longitude, shops.s_latitude))";
    if ($sort2 == 'Descending') $condition = $condition . " desc";
    
    $stmt = $conn->prepare($condition);
    $stmt->execute($sql_data);
    $search_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['search_sql'] = $condition;
    $_SESSION['sql_data'] = $sql_data;
    $_SESSION['search_info'] = $search_info;

    echo <<< EOT
    <!DOCTYPE html>
    <html> 
        <body>
            <script>
                window.location.replace('nav.php');
            </script>
        </body> 
    </html> 
    EOT;
    exit();
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
                        window.location.replace('nav.php');
                    </script>
                </body> 
            </html> 
        EOT;
}
?>