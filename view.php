<?php

    require_once 'pdo.php';
    require_once 'util.php';

    session_start();

     // If the user requested cancel go back to index.php
     if (isset($_POST['cancel'])){
        header('Location: index.php');
        return;
    }    
   
    if ( ! isset($_SESSION['user_id']) ) {
        die("ACCESS DENIED");
        return;
    }  
    
    if(! isset($_REQUEST['profile_id'])){
        $_SESSION['error'] = "Missing profile_id";
        header('Location: index.php');
        return;
    }
   
    // echo("PROFILE ID: " .$_REQUEST['profile_id']);
    $profile = loadProfile($pdo, $_REQUEST['profile_id']);    
  
    if($profile !== false){

        $profile_id = $_REQUEST['profile_id'];
        $user_id = $_SESSION['user_id'];
        $first_name = $profile['first_name'];
        $last_name = $profile['last_name'];
        $email = $profile['email'];
        $headline = $profile['headline'];
        $summary = $profile['summary'];   

    }
    
?>

<!DOCTYPE html>
<html lang="en">

    <head>

        <meta charset="UTF-8">

        <title>CHRISTOPHER CLARKE Resume Registry</title>

    </head>

    <body>

        <h1>Profile Information:</h1>

        <div id="container">           

            <p>First Name: &nbsp; <?= $first_name ?> </p>
            <p>Last Name: &nbsp; <?= $last_name ?> </p>
            <p>Email: &nbsp; <?= $email ?> </p>
            <p>Headline: &nbsp; <?= $headline ?> </p>
            <p>Summary: <?= $summary ?> </p>
            
            <?php             

                // Show all education entries if they exist                
                echo('<p>Education:</p>');
                echo('<div id="education_fields">');
                echo("\n");

                $schools = loadEducation($pdo, $_REQUEST['profile_id']);

                if(count($schools) > 0){

                    echo('<ul>');

                    foreach($schools as $school){
                                                             
                        echo('<li>Year:&nbsp;' .$school['year']);
                        echo('&nbsp;' .$school['name'] .'</li>');                                              
                                            
                    }

                    echo('</ul>');

                }
                
                echo('</div>');
                echo("\n");              

                // Show all position entries if they exist
                echo('<p>Positions:</p>');
                echo('<div id="position_fields">');
                echo("\n");

                $positions = loadPosition($pdo, $_REQUEST['profile_id']);

                if(count($positions) > 0){

                    echo('<ul>');

                    foreach($positions as $position){

                        echo('<li>Year:&nbsp;' .$position['year']);
                        echo('&nbsp;' .$position['description'] .'</li>');

                    }

                    echo('</ul>');

                }

                echo('</div>');
                echo("\n");

            ?>

            <form method="post" action="view.php">

                <input type="submit" name="cancel" value="Cancel">

            </form>

        </div>

    </body>

</html>
                                                   



