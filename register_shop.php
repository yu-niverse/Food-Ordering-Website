<?php 

include 'connect.php';
$u_account = $_SESSION['u_account'];

try {
    
    # check for empty columns
    if (empty($_POST['s_name']) || empty($_POST['s_category'])
        || $_POST['s_latitude']=="" || $_POST['s_longitude']=="") {
            throw new Exception('Please input all the columns !!');
        }

    # assign variables
    $s_name = $_POST['s_name'];
    $s_category = $_POST['s_category'];
    $s_latitude = $_POST['s_latitude'];
    $s_longitude = $_POST['s_longitude'];
    $s_position = "POINT($s_longitude $s_latitude)";

    if ($s_latitude < -90 || $s_latitude > 90) throw new Exception('Wrong latitude format !!');
    if ($s_longitude < -180 || $s_longitude > 180) throw new Exception('Wrong longitude format !!');

    # check if the user already owns a shop
    $stmt = $conn->prepare("select u_role from users where u_account = :u_account");
    $stmt->execute(array('u_account' => $u_account));
    $info = $stmt->fetch();
    if ($info['u_role'] == 'Manager') 
        throw new Exception('You already registered a shop');

    # check if the shop name is unique
    $stmt = $conn->prepare("select * from shops where binary s_name = :s_name");
    $stmt->execute(array('s_name' => $s_name));
    if ($stmt->rowCount() == 0) {
        $stmt = $conn->prepare("insert into shops (s_name, s_account, s_category, s_longitude, s_latitude, s_position) 
                        values (:s_name, :s_account, :s_category, :s_longitude, :s_latitude, ST_GeomFromText(:s_position));");
        $stmt->execute(array('s_name' => $s_name, 's_account' => $u_account, 's_category' => $s_category, 
            's_longitude' => $s_longitude, 's_latitude' => $s_latitude, 's_position' => $s_position));
        
        $_SESSION['s_name'] = $s_name;

        # pop up message
        echo <<< EOT
                <!DOCTYPE html>
                <html> 
                    <body>
                        <script>
                            alert("Shop Registered !!");
                            window.location.replace('shop.php');
                        </script>
                    </body> 
                </html> 
            EOT;
        exit();
    } else 
        throw new Exception("This shop name has been registered !!");
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
                        window.location.replace('shop.php');
                    </script>
                </body> 
            </html> 
        EOT;
}
?>

