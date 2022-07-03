<?php

    require_once 'pdo.php';
    require_once 'util.php';
       
    session_start();

    if ( ! isset($_SESSION['user_id']) ) {
        die("ACCESS DENIED");
        return;
    }    

    if(isset($_POST['cancel'])){
        header('Location: index.php');
        return;
    }          

    if(! isset($_REQUEST['profile_id'])){

        $_SESSION['error'] = "Missing profile_id";
        header('Location: index.php');
        return;

    }

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

    /*$profile_id = $_REQUEST['profile_id'];
    $user_id = $_SESSION['user_id'];
    $first_name = $profile['first_name'];
    $last_name = $profile['last_name'];
    $email = $profile['email'];
    $headline = $profile['headline'];
    $summary = $profile['summary'];   */
    
  //  echo($profile_id ." " .$user_id ." " .$first_name ." " .$last_name ." " .$email ." " .$headline . " " .$summary);
   
    function setErrorMessage($msg){

        $_SESSION['error'] = $msg;
        header('Location: edit.php?profile_id=' .$_REQUEST['profile_id']);
  
    }

    // Handle incoming data
    if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])){

        // Validate Profile        
        $msg = validateProfile();

        if(is_string($msg)){ setErrorMessage($msg); return; }

        // Validate Position
        $msg = validatePos();

        if(is_string($msg)){ setErrorMessage($msg); return; }

        // Validate Education
        $msg = validateEducation();

        if(is_string($msg)){ setErrorMessage($msg); return; }
 

        $sql = "UPDATE profile SET profile_id = :pid, user_id = :uid, first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :su WHERE profile_id = :pid";
        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(

            ':pid' => $_REQUEST['profile_id'],
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])       

        );    
        
        //Clear the old education entries
        $stmt=$pdo->prepare("DELETE FROM education WHERE profile_id = :pid");
        $stmt->execute(array(':pid' => $_REQUEST['profile_id']));

        // Insert the new education entries
        insertEducation($pdo, $_REQUEST['profile_id']);

        //Clear the old position entries
        $stmt=$pdo->prepare("DELETE FROM position WHERE profile_id = :pid");
        $stmt->execute(array(':pid' => $_REQUEST['profile_id']));
          
        // Insert the new position entries
        insertPosition($pdo, $_REQUEST['profile_id']);

        $_SESSION['success'] = "Profile updated";
        header("Location: index.php");
        return;
       
        // Load education and position entries
        $schools = loadEducation($pdo, $_REQUEST['profile_id']);
        $positions = loadPosition($pdo, $_REQUEST['profile_id']);      

    }
  
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        
        <meta charset="UTF-8">
  
        <!-- Latest compiled and minified CSS -->
          <link rel="stylesheet" 
            href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" 
            integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" 
            crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" 
            href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css"  
            integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r"     
            crossorigin="anonymous">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css">                                     

        <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"> 

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> 
        <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <!--  <script src="https://code.jquery.com/jquery-1.12.4.js"></script> -->           

        <title>CHRISTOPHER CLARKE Resume Registry</title>     

    </head>

    <body>

        <div class="container">

            <h1>Editing Profile For &nbsp; <?= htmlentities($_SESSION['name']) ?> </h1>

            <?php flashMessages(); ?>    
             
            <form method="post" action="edit.php">

                <input type="hidden"name="profile_id" value="<?= htmlentities($_REQUEST['profile_id']); ?>" />

                <p>First Name: <input type="text" name="first_name" size="60" value="<?php echo($first_name); ?>"/></p>
                <p>Last Name: <input type="text" name="last_name" size="60" value="<?php echo($last_name); ?>"/></p>
                <p>Email: <input type="text" name="email" size="30" value="<?php echo($email); ?>"/></p>
                <p>Headline:<br/>
                <input type="text" name="headline" size="80" value="<?php echo($headline); ?>"/></p>
                <p>Summary:<br/>
                <textarea name="summary" rows="8" cols="80"><?php echo($summary); ?></textarea></p>

                <div>

                    <p>Education: <input type="submit" name="addEdu" id="addEdu" value="+"></p>

                    <div id="education_fields"></div>

                </div>

                <div>

                    <p>Position: <input type="submit" name="addPos" id="addPos" value="+"></p>

                    <div id="position_fields"></div>

                </div>     
 
                <?php

                    // Show all education entries if they exist
                    $countEdu = 0;

                    $schools = loadEducation($pdo, $_REQUEST['profile_id']);

                    if(count($schools) > 0){

                        foreach($schools as $school){

                            $countEdu++;
                            echo('<div id="edu' .$countEdu .'">');
                            echo('<p>Year: <input type="text" name="edu_year' .$countEdu .'" value="' .$school['year'] .'"/>');
                            echo('<input type="button"  value="-" onclick="$(\'#edu' .$countEdu .'\').remove();return false;"></p>');
                            echo('<p>School: <input type="text" size="80" name="edu_school' .$countEdu .'" class="school" value="' .htmlentities($school['name']) .'"/>');
                            echo('</p>');
                            echo("\n");                           

                        }
                     
                    }

                    // Show all position entries if they exist
                    $countPos = 0;

                    $positions = loadPosition($pdo, $_REQUEST['profile_id']);

                    if(count($positions) > 0){

                        foreach($positions as $position){
                            
                            /*echo("TOTAL POSITIONS: " .count($positions));
                            echo("POSITION YEAR:" .$position['year']);
                            echo("POSITION DESC:" .$position['description']);*/
                      
                            $countPos++;
                            echo('<div id="pos' .$countPos .'">');

                            echo('<p>Year: <input type="text" name="year'.$countPos.'" value="'.$position['year'].'"/>');
                            echo('<input type="button" value="-" onclick="$(\'#pos' .$countPos .'\'' .').remove(); return false;)"/></p>');
                            echo('<p>Position:</p>');
                            echo('<textarea name="desc' .$countPos .'" rows="8" cols="80">' .htmlentities($position['description']) .'</textarea>');
                            echo("\n");

                        }
                     
                    }
                                    
                ?>           
 
                <div>
             
                    <input type="submit" name="save" value="Save">
                    <input type="submit" name="cancel" value="Cancel">

                </div>        

            </form>

          
        </div>

        <footer id="references">
    
            <script type="text/javascript" src="//jqueryui.com/wp-includes/js/comment-reply.min.js?ver=4.5.2"></script>
            <script type="text/javascript" src="//jqueryui.com/wp-includes/js/wp-embed.min.js?ver=4.5.2"></script>
            <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/docsearch.js@2/dist/cdn/docsearch.min.js" onload="document.querySelector('input[name=\'s\']') &amp;&amp; docsearch({apiKey: '2fce35e56784bbb48c78d105739190c2',
                indexName: 'jqueryui',
                inputSelector: 'input[name=\'s\']',
                debug: true // Set debug to true if you want to inspect the dropdown
                })" async=""></script>

        </footer>

    </body>

    
    <script>
 
 countPos = 0;

 countEdu = 0;

 $(document).ready(function(){

     console.log("Document ready called");

     $('#addEdu').click(function(event){

         event.preventDefault();

         if(countEdu >= 9){

             alert("Maximum of nine education entries exceeded");

         }

         countEdu++;

         console.log("Adding education " + countEdu);

         $('#education_fields').append(

             '<div id="education' + countEdu + '"> \
                 <p>Year: <input type="text" name="edu_year' + countEdu + '" value=""/> \
                 <input type="button" value="-" onclick="$(\'#education' + countEdu + '\').remove();return false;"></p> \
                 <p>School: <input type="text" size="80" name="edu_school' + countEdu + '" class="school"/></p> \
             </div>'                               
           
         );                                            

         // Add the event handler to the new entries                                                               
         $(".school").autocomplete({ source: "school.php" });

     });

     $(".school").autocomplete({ source: "school.php" });
                  
     $('#addPos').click(function(event){

         event.preventDefault();

         if(countPos >= 9){

             alert("Maximum of nine position entries exceeded");

         return;

         }   

         countPos++;

         console.log("Adding position " + countPos);

         $('#position_fields').append(

             '<div id="position' + countPos + '"> \
                 <p>Year: <input type="text" name="year' + countPos +'" value="" /> \
                 <input type="button" value="-" onclick="$(\'#position' + countPos + '\').remove();return false;"></p> \
                 <p>Description:</p> \
                 <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
             </div>'

         );  

     });

 });

</script>

  
</html>