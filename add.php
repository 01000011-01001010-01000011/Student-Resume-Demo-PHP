<?php

    require_once 'pdo.php';
    require_once 'util.php';
   
    session_start();

    if ( ! isset($_SESSION['user_id']) ) {
        die("ACESS DENIED");
        return;
    }    

    if(isset($_POST['cancel'])){
        header('Location: index.php');
        return;
    }

    function setError($msg){

        $_SESSION['error'] = $msg;
        header('Location: add.php');
      
    }

    // Handle incoming data
    if(isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary'])){

        // Validate Profile        
        $msg = validateProfile();

        if(is_string($msg)){setError($msg); return; }

        // Validate Position
        $msg = validatePos();

        if(is_string($msg)){ setError($msg); return; }

        // Validate Education   
        $msg = validateEducation();

        if(is_string($msg)){ setError($msg); return; }
     
        $stmt = $pdo->prepare('INSERT INTO Profile(user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :he, :su)');
        $stmt->execute(array(   

            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'])
                
        );

        $profile_id = $pdo->lastInsertId(); 

        // Insert the education and position data in the databases
        insertEducation($pdo, $profile_id);
        insertPosition($pdo, $profile_id);       
    
        $_SESSION['success'] = "Profile added";
        header("Location: index.php");
        return;
       
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
        
            <h1>Adding Profile for UMSI</h1>
            
            <?php flashMessages(); ?>     
             
            <form method="post">
        
                <p>First Name: <input type="text" name="first_name" size="60"/></p>
                <p>Last Name: <input type="text" name="last_name" size="60"/></p>
                <p>Email: <input type="text" name="email" size="30"/></p>
                <p>Headline:<br/>
                <input type="text" name="headline" size="80"/></p>
                <p>Summary:<br/>
                <textarea name="summary" rows="8" cols="80"></textarea></p>        

                <div>

                    <p>Education: <input type="submit" name="addEdu" id="addEdu" value="+"></p>
                    
                    <div id="education_fields"></div>

                </div>
               
                <div>
 
                    <p>Position: <input type="submit" name="addPos" id="addPos" value="+"></p>
 
                    <div id="position_fields"></div>
                 
                </div>     
                            
                <div>     

                    <input type="submit" name="add" value="Add">
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
                        <p>Year: <input type="text" name="edu_year' + countEdu + '" value="" /> \
                        <input type="button" value="-" onclick="$(\'#education' + countEdu + '\').remove();return false;"/></p> \
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
                        <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea> \
                    </div>'

                );

            });

        });            

    </script>              

</html>
