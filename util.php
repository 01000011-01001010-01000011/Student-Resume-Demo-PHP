<?php

    function flashMessages(){

        if(isset($_SESSION['error'])){

            echo('<p style="color:red;">' .htmlentities($_SESSION['error']) ."</p>\n");
            unset($_SESSION['error']);

        }

        if(isset($_SESSION['success'])){

            echo('<p style="color:green;">' .htmlentities($_SESSION['success']) ."</p>\n");
            unset($_SESSION['success']);

        }

    }

    function insertPosition($pdo, $profile_id){
        // Insert the position data

        $rank = 1;

        for($i = 1; $i <= 9; $i++){
   
            if(! isset($_POST['year' .$i])) continue;
            if(! isset($_POST['desc' .$i])) continue;
   
            $year = $_POST['year' .$i];
            $desc = $_POST['desc' .$i];
   
            $stmt = $pdo->prepare("INSERT INTO position (profile_id, rank, year, description) VALUES (:pid, :rank, :year, :desc)");                          
            $stmt -> execute(array(
   
                ':pid' => $profile_id,
                ':rank'=> $rank,
                ':year' => $year,
                ':desc' => $desc)
   
            );
   
            $rank++;
   
        }    
   

    }

    function insertEducation($pdo, $profile_id){

        $rank = 1;

        for($i = 1; $i <= 9; $i++){

            if(! isset($_POST['edu_year' .$i])) continue;
            if(! isset($_POST['edu_school' .$i])) continue;

            $year = $_POST['edu_year' .$i];
            $school = $_POST['edu_school' .$i];

            // Look up school
            $institution_id = false;
            
            $stmt = $pdo->prepare("SELECT institution_id FROM institution WHERE name = :name");
            $stmt->execute(array(':name' => $school));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($row !== false){

                $institution_id = $row['institution_id'];

            }

            // If there is not a matching institution insert it
            if($institution_id === false){

                $stmt = $pdo->prepare("INSERT INTO institution (name) VALUES (:name)");
                $stmt->execute(array(':name' => $school));
                $institution_id =$pdo->lastInsertId();

            }

            $stmt=$pdo->prepare("INSERT INTO education (profile_id, rank, year, institution_id) VALUES(:pid, :rank, :year, :iid)");
            $stmt->execute(array(

                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $year,
                ':iid' => $institution_id)   
            
            );

            $rank++;

        }

    }

    function loadEducation($pdo, $profile_id){

        $stmt = $pdo->prepare("SELECT year, name FROM education JOIN institution ON 
            education.institution_id = institution.institution_id WHERE profile_id = :prof
            ORDER BY rank");

        $stmt->execute(array(':prof' => $profile_id));
        $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $education;

    }

    function loadPosition($pdo, $profile_id){

        $stmt = $pdo->prepare('SELECT * FROM position WHERE profile_id = :prof ORDER BY rank');

        $stmt->execute(array(':prof' => $profile_id));
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

     /*  
        $positions = array();
     
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $positions[] = $row;

        }*/

        return $positions;

    }

    function loadProfile($pdo, $profile_id){

         // Load the profile in question         
        $stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :prof AND user_id = :uid");
        $stmt->execute(array(":prof" => $profile_id, ":uid" => $_SESSION['user_id']));
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if($profile === false){
            $_SESSION['error'] = "Could not load profile";
            header("Location: index.php");
            return;
        }   

        return $profile;

    }

    function validateEducation(){

        for($i = 1; $i <= 9; $i++){

            if(! isset($_POST['edu_year'])) continue;
            if(! isset($_POST['edu_school'])) continue;
    
            $year = $_POST['edu_year' .$i];
            $school = $_POST['edu_school' .$i];
    
            if(strlen($year) == 0 || strlen($school) == 0){
    
                return "All fields are required";
    
            }
    
            if(! is_numeric($year)){
    
                return "Education year must be numeric";
    
            }    
       
            return true;

        }

    }


    function validatePos(){

        for($i = 1; $i <= 9; $i++){

            if(! isset($_POST['year'])) continue;
            if(! isset($_POST['desc'])) continue;

            $year = $_POST['year' .$i];
            $desc = $_POST['desc' .$i];

            if(strlen($year) == 0 || strlen($desc) == 0){

                return "All fields are required";

            }

            if(! is_numeric($year)){

                return "Position year must be numeric";

            } 

        }

        return true;

    }


    function validateProfile(){

        if(strlen($_POST['first_name']) == 0 || strlen($_POST['last_name']) == 0 || strlen($_POST['email']) == 0 ||
            strlen($_POST['headline']) == 0 || strlen($_POST['summary']) == 0){

                return "All fields are required";

            }

        if(strPos($_POST['email'], '@') === false){

            return "Email address must contain @";

        }

        return true;

    }
