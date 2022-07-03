<?php

    require_once 'pdo.php';
    require_once 'util.php';

    session_start();
    
    unset($_SESSION['name']);
    unset($_SESSION['user_id']);

     // If the user requested cancel go back to index.php
     if (isset($_POST['cancel'])){
        header('Location: index.php');
        return;
    }    

    $salt = 'XyZzy12*_';
    $stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1'; //php123
    
    $md5 = hash('md5', 'XyZzy12*_php123');   
   
    $message = false;
   
    // Check for POST data
    if ( isset($_POST['email']) && isset($_POST['pass']) ) {
           
        if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        
            $_SESSION["error"] = "Email and password are required";
            header('Location: login.php');
            return;
        }

        $check = hash('md5', $salt .$_POST['pass']);
        $stmt = $pdo->prepare('SELECT name, user_id FROM users WHERE email = :em and password = :pw');
        $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row !== false){

            $_SESSION['name'] = $row['name'];
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['success'] = "Logged In";
            header('Location: index.php');
            return;

        }else{

            $_SESSION['error'] = "Incorrect password";
            header('Location: login.php');
            return;

        }

    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="UTF8">

        <title>CHRISTOPHER CLARKE Resume Database Login</title>

        <script>

            function doValidate(){

                console.log('Validating...');

                try{

                    email = document.getElementById('email').value;

                    password = document.getElementById('id_1723').value;

                    console.log("Validating email = " + email + "password = " + password);

                    if(email == null || email == "" || password == null || password == ""){

                        alert("Both fields must be filled out");
                        return false;

                    }else{

                        if(email.indexOf('@') == -1){
                            
                            alert("Invalid email address");
                            return false;

                        }else{

                            return true;
                        }
                        
                    }

                }catch(e){

                    return false;

                }              
              
            }

        </script>

    </head>

    <body>

        <div class="container">

            <h1>Resume Database Login</h1> 

            <?php
                 
               flashMessages();

            ?>

            <form method="post" action="login.php">

                <label for="email">Email:</label>
                <input type="text" name="email" id="email"><br/>
                <label for="id_1723">Password:</label>
                <input type="password" name="pass" id="id_1723"><br/>
                <input type="submit" onclick="return doValidate();" value="Log In"> &nbsp; 
                <input type="submit" name="cancel" value="Cancel">

            </form>

        </div>

    </body>

</html>