<?php

include_once("include/config.php");

session_start();

function prepare_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if(isset($_POST['create_job']) && isset($_SESSION['user_id'])) {
    $title = prepare_input($_POST['job_title']);
    $salary = (double)$_POST['salary'];
    $content = prepare_input($_POST['content']);
    $company = prepare_input($_POST['company']);
    $location = prepare_input($_POST['location']);
    $user_id = $_SESSION['user_id'];
    $timestamp = date("Y-m-d H:i:s", time());

    /* echo $title.'<br>'.gettype($title).'<br>';
    echo $salary.'<br>'.gettype($salary).'<br>';
    echo $content.'<br>'.gettype($content).'<br>';
    echo $company.'<br>'.gettype($company).'<br>';
    echo $location.'<br>'.gettype($location).'<br>';
    echo $user_id; */

    try {
        $query = "SELECT * FROM companies WHERE name LIKE '".$company."' AND location LIKE '".$location."'";

        $stmt = $conn->query($query);
        
        if($stmt->rowCount() > 0){
            while ($row = $stmt->fetch()) {
                $com_id = $row['id'];
            }
            try {

                $conn->beginTransaction();

                $query = "INSERT INTO job_ads (title,salary,content,com_id,user_id) VALUES('$title', '$salary', '$content', '$com_id', '$user_id')";
                $conn->query($query);

                $conn->commit();
                
                header('Location: add_jobs.php?action=success');
            } catch (PDOException $e) {
                $conn->rollback();
                echo "Error: ".$e->getMessage();
            }
        } else {

            try {
                $conn->beginTransaction();

                $query = "INSERT INTO companies (name,location) VALUES('$company', '$location')";
                $conn->query($query);

                $conn->commit();
                $com_id = $conn->lastInsertId();

                try {
                    $conn->beginTransaction();
                    $query = "INSERT INTO job_ads (title,salary,content,com_id,user_id) VALUES('$title', '$salary', '$content', '$com_id', '$user_id')";
                    $conn->query($query);

                    $conn->commit();
                    
                    header('Location: add_jobs.php?action=success');
                } catch (PDOException $e) {
                    $conn->rollback();
                    echo "Error: ".$e->getMessage();
                }
            } catch (PDOException $e) {
                $conn->rollback();
                echo "Error: ".$e->getMessage();
            }
        }
    } catch (PDOException $e) {
        echo "Error: ".$e->getMessage();
    }
} else {
    header('Location: add_jobs.php');
}

?>