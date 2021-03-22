<?php
session_start();
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
require_once("constants.php");
require_once("geoip2.phar");
use GeoIp2\Database\Reader;


$servername = SERVER_NAME;
$username = DB_USER_NAME;
$password = DB_PASSWORD;
$dbname = DATABASE_NAME; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$whisky_knowledge_list = [
            'Newbie'=>1,
            'Astute'=>2,
            'Dilettante'=>3,
            'Connoisseur'=>4
            ];

if (isSet($_SESSION['started']))
{ 
	if((time() - $_SESSION['started'] - 60*30) > 0){ 
		unset($_SESSION['started']); 
		unset($_SESSION['user_id']); 
	} 
} 


$postRequest = $_POST;
if(isset($postRequest['sign_up'])){
	$firstName = (isset($postRequest['first_name'])) ? $postRequest['first_name'] :'';
	$lastName = (isset($postRequest['last_name'])) ? $postRequest['last_name'] :'';
	$email = (isset($postRequest['email'])) ? $postRequest['email']  :'';
	$gender = (isset($postRequest['gender'])) ? $postRequest['gender'] :'';;
	$ageGroup = (isset($postRequest['age_group'])) ? $postRequest['age_group'] :'';
	$country = (isset($postRequest['country'])) ? $postRequest['country'] :'';

	$date = new DateTime();
	$createdAt = $date->format('Y-m-d H:i:s') . "\n";

	 // Attempt insert query execution
	$sql = "INSERT INTO users (first_name, last_name, email, gender, age_group, country, created_date) VALUES
	            ('".$firstName."', '".$lastName."', '".$email."', '".$gender."', '".$ageGroup."', '".$country."', '".$createdAt."')";

	if ($conn->query($sql) === TRUE) {
	  	$last_id = $conn->insert_id;
	  	$_SESSION['user_id'] = $last_id;
	  	$_SESSION['started'] = time(); 
	  	$response = ['success'=>true,'code'=> 'user_signup','message'=>'User successfully signed up'];
	  	echo json_encode($response);
	  	exit;
	} else {
		$response = ['success'=>false,'code'=> 'user_signup','message'=>'Failed to create user'];
	  	echo json_encode($response);
	  	exit;
	}


}

if(isset($postRequest['step_1'])){
	$whiskyKnowledge = (isset($postRequest['whisky_knowledge'])) ? $postRequest['whisky_knowledge'] :'';
	$userId = $_SESSION['user_id'];


	//check if survey is created
	$sql = "SELECT * FROM survey where user_id = '".$userId."'";
	$result = $conn->query($sql);
	if ($result->num_rows == 0) {
		$sql = "INSERT INTO survey (user_id, whisky_industry_knowledge) VALUES
	            ('".$userId."', '".$whiskyKnowledge."')";
	}
	else{
		$sql = "UPDATE survey SET whisky_industry_knowledge='".$whiskyKnowledge."' WHERE user_id= '".$userId."'";
	}

	if ($conn->query($sql) === TRUE) {;
	  	$response = ['success'=>true,'code'=> 'step_1','message'=>'Answer successfully saved.'];
	  	echo json_encode($response);
	  	exit;
	} else {
		$response = ['success'=>false,'code'=> 'step_1','message'=>'Failed to save Answer'];
	  	echo json_encode($response);
	  	exit;
	}


}

if(isset($postRequest['step_2'])){
	$bottlebitsHelpMe = (isset($postRequest['bottlebits_help_me'])) ? $postRequest['bottlebits_help_me'] :'';
	$userId = $_SESSION['user_id'];
	if(is_array($bottlebitsHelpMe)) 
		$bottlebitsHelpMe = implode(", ", $bottlebitsHelpMe);


	//check if survey is created
	$sql = "SELECT * FROM survey where user_id = '".$userId."'";
	$result = $conn->query($sql);
	if ($result->num_rows == 0) {
		$sql = "INSERT INTO survey (user_id, bottlebits_help_me) VALUES
	            ('".$userId."', '".$bottlebitsHelpMe."')";
	}
	else{
		$sql = "UPDATE survey SET bottlebits_help_me='".$bottlebitsHelpMe."' WHERE user_id= '".$userId."'";
	}

	if ($conn->query($sql) === TRUE) {;
	  	$response = ['success'=>true,'code'=> 'step_2','message'=>'Answer successfully saved.'];
	  	echo json_encode($response);
	  	exit;
	} else {
		$response = ['success'=>false,'code'=> 'step_2','message'=>'Failed to save Answer'];
	  	echo json_encode($response);
	  	exit;
	}


}

if(isset($postRequest['step_3'])){
	$initialInvestmentIntention = (isset($postRequest['initial_investment_intention'])) ? $postRequest['initial_investment_intention'] :'';
	$userId = $_SESSION['user_id'];


	//check if survey is created
	$sql = "SELECT * FROM survey where user_id = '".$userId."'";
	$result = $conn->query($sql);
	if ($result->num_rows == 0) {
		$sql = "INSERT INTO survey (user_id, initial_investment_intention) VALUES
	            ('".$userId."', '".$initialInvestmentIntention."')";
	}
	else{
		$sql = "UPDATE survey SET initial_investment_intention='".$initialInvestmentIntention."' WHERE user_id= '".$userId."'";
	}

	if ($conn->query($sql) === TRUE) {;
	  	$response = ['success'=>true,'code'=> 'step_3','message'=>'Answer successfully saved.'];
	  	echo json_encode($response);
	  	exit;
	} else {
		$response = ['success'=>false,'code'=> 'step_3','message'=>'Failed to save Answer'];
	  	echo json_encode($response);
	  	exit;
	}


}


if(isset($postRequest['session_exits'])){
	
	if (isset($_SESSION['user_id']) && $_SESSION['user_id']!="") {
		$userId = $_SESSION['user_id'];
		$start_survey_step = 'survey-step-welcome';
		$answers = [];

		$sql = "SELECT * FROM survey where user_id = '".$userId."'";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc(); 
			if($row['whisky_industry_knowledge'] == null || $row['whisky_industry_knowledge'] == ''){
				$start_survey_step = 'survey-step-1';
			}
			elseif($row['bottlebits_help_me'] == null || $row['bottlebits_help_me'] == ''){
				$start_survey_step = 'survey-step-2';
			}
			elseif($row['initial_investment_intention'] == null || $row['initial_investment_intention'] == ''){
				$start_survey_step = 'survey-step-3';
			}
			else{
				$start_survey_step = 'survey-step-thankyou';
			}

			if($row['whisky_industry_knowledge'] != null && $row['whisky_industry_knowledge'] != ''){
				$answers['whisky_industry_knowledge'] = (isset($whisky_knowledge_list[$row['whisky_industry_knowledge']])) ? $whisky_knowledge_list[$row['whisky_industry_knowledge']]: '';
			}

			if($row['bottlebits_help_me'] != null && $row['bottlebits_help_me'] != ''){
				$answers['bottlebits_help_me'] = explode(',', $row['bottlebits_help_me']);
			}

			if($row['initial_investment_intention'] != null && $row['initial_investment_intention'] != ''){
				$answers['initial_investment_intention'] = $row['initial_investment_intention'];
			}
		}

	  	$response = ['success'=>true,'code'=> 'session_exits','start_survey_step'=>$start_survey_step,'answers'=>$answers];
	  	echo json_encode($response);
	  	exit;
	} else {
		$reader = new Reader('GeoLite2-Country.mmdb');
		
		$country =  '';
		if($_SERVER['REMOTE_ADDR']!='::1'){
			$record = $reader->country($_SERVER['REMOTE_ADDR']);
			$country = $record->country->name;
		}
		
		$response = ['success'=>false,'code'=> 'session_exits','country'=>$country];
	  	echo json_encode($response);
	  	exit;
	}


}
 
// // Close connection
$conn->close();
?>


