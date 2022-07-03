<?php

    require_once "pdo.php";
    require_once "util.php";

    session_start();

    $colCount = 0;

    $totalProfiles = 0;

    $colNames= array();

    $loadSearchArray = false;
           
    function LoadProfilesArray(){

        include "pdo.php";

        $profilesArray = array();

        $stmt=$pdo->query("SELECT * FROM profile");
        $stmt->execute();        
        $colCount = $stmt->columnCount();
       //  $rowCount = $stmt->rowCount();  // Get number for columns in table using SQL

        $row=$stmt->fetch(PDO::FETCH_ASSOC);
              
        if($row !== false){                    

            $colNames = GetColumnNames();
        
            $rowNum = 0;

            $stmt=$pdo->query("SELECT * FROM profile");
            $stmt->execute();              
          
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){           
        
                for($i = 0; $i < $colCount; $i++){

                    $profilesArray[$rowNum][$i] = $row[$colNames[$i]];
                
                }              

                $rowNum = $rowNum + 1;
                
            }
                           
        }

        return $profilesArray;

    }

    function LoadFoundProfilesArray($searchForValue){
           
        include "pdo.php";

        $stmt=$pdo->query("SELECT * FROM profile");
        $stmt->execute();        
        $colCount = $stmt->columnCount();
        
        $foundProfilesArray = array();

        $profilesArray = LoadProfilesArray();

        $totalProfiles = count($profilesArray);

        if($totalProfiles > 0){

            for($i = 0; $i < $totalProfiles; $i++){
              
                for($j = 0; $j < $colCount; $j++){
              
                    $compareValue = $profilesArray[$i][$j];

                    if(strtoupper($searchForValue) === strtoupper($compareValue) || str_contains(strtoupper($compareValue), strtoupper($searchForValue))){

                        for($k = 0; $k < $colCount; $k++){
    
                            $foundProfilesArray[$i][$k] = $profilesArray[$i][$k];

                        }

                        break;

                    }

                }

            }

        }

        /* Do not delete for course

        if($colCount > 0){                   

            echo("<br/>COLCOUNT: " .$colCount);

            $colNames = GetColumnNames();

            $rowNum = 0;

            for($i = 0; $i < $colCount; $i++){                  
    
                $colName = "'$colNames[$i]'";

                $strValue = "'%$searchForValue%'";

                // Find exact matches
                $sql = "SELECT * FROM profile WHERE" ." " .$colName ." " ."LIKE" ." " .$strValue ."";

                // Find all matches
                $sql = "SELECT * FROM profile WHERE CONTAINS($colName, $strValue)";

                    $stmt = $pdo->query($sql);
               
                while($row=$stmt->fetch(PDO::FETCH_ASSOC)){                                 

                    for($i = 0; $i < $colCount; $i++){

                        $profilesArray[$rowNum][$i] = $row[$colNames[$i]];

                    }
                    
                }

                $rowNum = $rowNum + 1;
              
            }
         
        }*/
        
        return $foundProfilesArray;

    }                

    function GetColumnNames(){       
        // Get the column names from the selected table                

        include "pdo.php";

        $columns = array();

        $stmt = $pdo->prepare('SHOW COLUMNS FROM profile');
        $stmt->execute();
            
        // Get the column names in the selected table
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){

            $columns[] = $row['Field'];   

        }

        return $columns;

    }

    function GetTotalProfiles($profiles){

        $totalProfiles = count($profiles);

        return $totalProfiles;
    }

    function GetTotalPagesToDisplay($totalNumProfiles){
      
        $maxPages = ceil($totalNumProfiles / 50);

        return $maxPages;
       
    }

    function GetStartingValues($totalNumProfiles){
        
        if(! isset($_SESSION['totalPages'])){
                              
            $_SESSION['totalPages'] = GetTotalPagesToDisplay($totalNumProfiles);
                         
        }else{

            unset($_SESSION['totalPages']);
           $_SESSION['totalPages'] = GetTotalPagesToDisplay($totalNumProfiles);

        }                    
        
        if(! isset($_SESSION['pageNumber'])){
       
            $_SESSION['pageNumber'] = 1;   
          
        }

        if(! isset($_SESSION['startProfileNum'])){

            $_SESSION['startProfileNum'] = 0;

        }

        if(! isset($_SESSION['endProfileNum'])){

            $_SESSION['endProfileNum'] = 9;

        }

    }

?>

<!DOCTYPE html>
<html>
    <head>

        <title>CHRISTOPHER CLARKE Resume Registry</title>
        
        <?php require_once 'head.php'; ?>

        <style>

            #profilesTable{

                width: 75%;

                table-layout: auto;

            }

            #profilesTable th{

                text-align: center;

            }

            #profilesTable th:nth-child(1){

                text-align: left;

            }

            #profilesTable, #profilesTable th, #profilesTable td{

                border: 1px solid black;                

            }

            #profilesTable td:nth-child(3){
                
                text-align: center;

            }

        </style>

        <!-- JQUERY -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


    </head>

    <body>
    
        <div class="container">
                              
           <h1>Resume Registry</h1>
        
            <?php 
                
                // Display messages
                flashMessages();

                if(isset($_SESSION['user_id'])){

                    echo('<p><a href="logout.php">Logout</a></p>' ."\n");
                    
                }else{

                    echo('<p><a href="login.php">Please log in</a></p>' ."\n");

                }

            ?>

            <form method="POST">
                <input type="text" name="searchForValue" size="80">&nbsp;
                <input type="submit" name="btnSearch" value="Search">
            </form>                                            
            <br/>

            <h2>Profiles:</h2>

            <br/>               
          
            <?php                                       
              
                $totalNumProfiles = 0;

                $profilesArray = array();                    
                
                $_SESSION['totalProfiles'] = $totalNumProfiles;

                if(isset($_POST['btnSearch'])){

                    if(isset($_POST['searchForValue'])){
                                             
                        $searchForValue = $_POST['searchForValue'];

                    }                 
              
                    if(! empty($searchForValue)){

                        ResetDisplayValues();
                       
                        $profilesArray = LoadFoundProfilesArray($searchForValue);
           
                        $totalNumProfiles = GetTotalProfiles($profilesArray);       

                        unset($_SESSION['totalProfiles']); 
                        $_SESSION['totalProfiles'] = $totalNumProfiles;

                        LoadTable($profilesArray);
                    }

                }else{
                   
                    $profilesArray = LoadProfilesArray();

                    $totalNumProfiles = GetTotalProfiles($profilesArray);       

                    unset($_SESSION['totalProfiles']); 
                    $_SESSION['totalProfiles'] = $totalNumProfiles;

                    LoadTable($profilesArray);

                }        
               
                function LoadTable($profilesArray){

                    $totalNumProfiles = GetTotalProfiles($profilesArray); // REMOVE FOR TESTING 
                    $totalProfilesToShow = 0;

                    $startProfileNum = 0;
                    $endProfileNum = 0;                                                     
               
                   /* TESTING ONLY */

                   /*
                    $totalNumProfiles = 80;

                    unset($_SESSION['totalProfiles']); 
                    $_SESSION['totalProfiles'] = $totalNumProfiles;

                    /* END OF TESTING ONLY */
                 
                    if($totalNumProfiles > 0){

                        echo('<table id="profilesTable">');

                        echo('<tr>');
                            echo('<th>Name</th>');
                            echo('<th>Headline</th>');
                            echo('<th>Action</th>');
                        echo('</tr>');
                           
                      
                        if($totalNumProfiles > 100000){                                     
                                              
                           /*** ADDITIONAL CONTENT - OPTIONAL OBJECTIVES ***/
                           
                           /*
                           if(isset($_POST['prev']) || isset($_POST['next'])){
                               
                                $pageNumber = $_SESSION['pageNumber'];                                    
                              
                                if(isset($_POST['prev'])){                                                   
                                                                                                          
                                    if($pageNumber > 1){

                                        $pageNumber = $pageNumber - 1;
                                                                                                           
                                        $startProfileNum = $_SESSION['startProfileNum'] - 10;

                                        $endProfileNum = $startProfileNum + 9;

                                        $totalProfilesToShow = 10;

                                    }else{

                                        $startProfileNum = 0;

                                        $endProfileNum = $startProfileNum + 9;

                                        $totalProfilesToShow = 10;

                                    }          
                                                                        
                                    unset($_SESSION['pageNumber']);
                                    $_SESSION['pageNumber'] = $pageNumber;
                                
                                    unset($_SESSION['startProfileNum']);
                                    $_SESSION['startProfileNum'] = $startProfileNum;
    
                                    unset($_SESSION['endProfileNum']);                                        
                                    $_SESSION['endProfileNum'] = $endProfileNum;
                    
                                             
                                }

                               if(isset($_POST['next'])){
                                                                   
                                    if($pageNumber < $_SESSION['totalPages']){                               
                                        
                                        $pageNumber = $pageNumber + 1;                                                          

                                        $startProfileNum = $_SESSION['endProfileNum'] + 1;
                                    
                                        $endProfileNum = $startProfileNum + 9;

                                        if($endProfileNum < $totalNumProfiles){

                                            $endProfileNum = $endProfileNum;

                                            $totalProfilesToShow = 10;

                        
                                        }else{
                        
                                            $endProfileNum = $startProfileNum + ($_SESSION['totalProfiles'] - $startProfileNum);

                                            $totalProfilesToShow = $endProfileNum;                                          
                        
                                        }

                                        unset($_SESSION['pageNumber']);
                                        $_SESSION['pageNumber'] = $pageNumber;
                                    
                                        unset($_SESSION['startProfileNum']);
                                        $_SESSION['startProfileNum'] = $startProfileNum;
        
                                        unset($_SESSION['endProfileNum']);                                        
                                        $_SESSION['endProfileNum'] = $endProfileNum;
                        
                                    }                                                                    
    
                                }                                              
  
                              
                               // echo('<br/>');
                                //echo("Page Number:" ." " .$_SESSION['pageNumber'] ." " ."Start Profile:" ." " .$_SESSION['startProfileNum'] ." " ."Last Profile:" ." " .$_SESSION['endProfileNum']);
                                //echo('<br/>');  

                                $show = true;
                                AddTableRows($_SESSION['startProfileNum'], $_SESSION['endProfileNum'], $show, $profilesArray, $totalProfilesToShow);
                                
                            }else{

                                GetStartingValues($totalNumProfiles);

                                $totalProfilesToShow = 10;

                             /*   echo('<br/>');
                                echo("Page Number:" ." " .$_SESSION['pageNumber'] ." " ."Start Profile:" ." " .$_SESSION['startProfileNum'] ." " ."Last Profile:" ." " .$_SESSION['endProfileNum']);
                                echo('<br/>');  */

                                //$show = true;
                                //AddTableRows($_SESSION['startProfileNum'], $_SESSION['endProfileNum'], $show, $profilesArray, $totalProfilesToShow);                                                    

                            //}
                                    
                          
                        }else{
                           
                            unset($_SESSION['pageNumber']);
                            $_SESSION['pageNumber'] = 1;    

                            unset($_SESSION['startProfileNum']);
                            $_SESSION['startProfileNum'] = 0;

                            unset($_SESSION['endProfileNMum']);
                            $_SESSION['endProfileNum'] = 9;
                                        
                            $show = false;

                            $totalProfilesToShow = $totalNumProfiles;
                    
                     //       echo("Page Number: " .$_SESSION['pageNumber'] ." Start Profile: " .$_SESSION['startProfileNum'] ." Last Profile: " .$_SESSION['lastProfileNum']);
                    
                            if($totalNumProfiles > 0){

                                AddTableRows($_SESSION['startProfileNum'], $_SESSION['endProfileNum'], $show, $profilesArray, $totalProfilesToShow);                                      

                            }
  
                        }
  
                    }else{

                        echo('<p><strong>"No records to show"</strong></p>');

                    }

                }
                                               
                function AddTableRows($start, $lastProfileToShow, $showButtons, $profilesArray, $totalProfilesToShow){                                                       
              
                    for($i = $start; $i < $totalProfilesToShow; $i++){

                        echo("<tr><td>");
                        echo('<a href="view.php?profile_id=' .$profilesArray[$i][0] .'">' .htmlentities($profilesArray[$i][2]) .' ' .htmlentities($profilesArray[$i][3]) .'</a>');
                        echo('</td><td>');
                        echo(htmlentities($profilesArray[$i][5]));
                        echo('</td>');                          
                       
                        if(isset($_SESSION['user_id'])){                              
                            
                            echo('<td>');
                            echo('<a href="edit.php?profile_id=' .$profilesArray[$i][0] .'">Edit</a>&nbsp; &nbsp;');
                            echo('<a href="delete.php?profile_id=' .$profilesArray[$i][0] .'">Delete</a>');
                            echo('</td');
                        
                        }         

                        echo('</tr>');

                    }

                    echo('</table><br/>');                                     
             
                    if($showButtons){

                        echo('<form method="POST">');

                            echo('<input type="submit" name="prev" value="PREV">');
                            echo('&nbsp;');
                            echo('<input type="submit" name="next" value="NEXT">');

                        echo('</form>');
                        
                    }                                 
                      
                }                        
                
                if(isset($_SESSION['user_id'])){
                              
                    echo('<br/>');               
                    echo('<p><a href="add.php" style="color:blue;">Add New Entry</a></p>');

                }

            ?>                                               
           
        </div>
    
    </body>

</html>