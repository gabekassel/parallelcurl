<?php
error_reporting(E_ALL);
ini_set('display_errors', 'on');
require_once('parallelcurl.php');
define ('SEARCH_URL_PREFIX', 'Add Curl prefix url here');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
</head>

<body>
<?php if(!isset($_POST['submit'])){
	 ?>
<br/><br/>

<h1>Enter beginning number...</h1><br/>
<form method="post" action="parallel_index.php">
Starting: <input type="text" name="number" size="14" /></br>
<input type="submit" name="submit" value=" Submit " />
</form>

<?php 
}else{

    $link = mysql_connect('localhost:8889', 'root', 'root');
    mysql_select_db("gc", $link);
    $gc = $_POST['number'];
    $gcint = intval($gc) - 1;
    
    function on_request_done($content, $url, $ch, $gcint) {
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
	if ($httpcode !== 200)
	{
	    print "Fetch error $httpcode for '$url'\n";
	    return;
	}
	$result = json_decode($content, true);
	if(isset($result['giftCard']))
	{
	    $balance = $result['object']['balance'];
	    $number = $result['object']['number'];
	    $gcint2 = intval($number);
			
	    if((strpos($balance,'$') !== false))
	    {
		$existing = mysql_query("SELECT * FROM gc WHERE number_int = '$gcint2'");
		$num_rows = mysql_num_rows($existing);
		if($num_rows<1)
		{
		}
		
	    }
	    
	}
    }
	
    





    $curl_options = array(
	CURLOPT_RETURNTRANSFER => 1,
    );

    $max_requests = 7;
    $parallel_curl = new ParallelCurl($max_requests, $curl_options);

    //Add each number into curl_mult request
    for($i=1; $i<=30000; ++$i)
    {
    
	$gcint = $gcint - 1;
	$search_url = SEARCH_URL_PREFIX.$gcint;
	$parallel_curl->startRequest($search_url, 'on_request_done', $gcint);
    }

    $parallel_curl->finishAllRequests();
    mysql_close($link);

}

?>

</body>
</html>