<?php

    require_once "pdo.php";
    
    session_start();

    if ( ! isset($_SESSION['user_id']) ) {
        die("Not logged in");
    }    

    if(isset($_POST['cancel'])){
        header('Location: index.php');
        return;
    }

    $stmt = $pdo->prepare("SELECT user_id, first_name, last_name FROM profile WHERE profile_id = :profile_id");
    $stmt->execute(array(":profile_id" => $_GET['profile_id']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $user_id = $row['user_id'];
    //echo("this is current user_id: " .$row['user_id']);

    if($row === false){
        $_SESSION['error'] = "Bad value for profile_id";
        header('Location: index.php');
        return;
    }
  
    $profile_id = $_GET['profile_id'];
    $logged_in_user_id = $_SESSION['user_id'];

  //  echo("<p>LOGGED:" .$logged_in_user_id ."</p>");
            
    if(isset($_POST['delete']) && isset($_POST['profileID'])){

        if($user_id == $logged_in_user_id){

           $sql = "DELETE FROM profile WHERE profile_id = :profile_id";

            $stmt = $pdo->prepare($sql);

            $stmt ->execute(array(":profile_id" => $_POST['profileID']));

            $_SESSION['success'] = "Profile deleted";
            header('Location: index.php');
            return;
            
        }
        else{
            
            $_SESSION['error'] = "Access denided. User_id and profile_id do not match current user";
            header('Location: delete.php?profile_id=' .$profile_id);
            return;

        }

    }

  
                
?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="UTF-8">

        <title>CHRISTOPHER CLARKE Delete Profile From Database</title>

    </head>

    <body>

        <h1>Deleting Profile:</h1>

        <?php

            if(isset($_SESSION['error'])){
                echo('<p style="color:red;">' .$_SESSION['error'] ."</p>\n");
                unset($_SESSION['error']);
            }

        ?>

        <p>First Name: <?= $row['first_name'] ?></p>

        <p>Last Name: <?= $row['last_name'] ?></p>

        <form method="post">

            <input type="hidden" name="profileID" value="<?= $profile_id ?>">

            <input type="submit" name="delete" value="Delete"> &nbsp;

            <input type="submit" name="cancel" value="Cancel">

        </form>

    </body>

</html>