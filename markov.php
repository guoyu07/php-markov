<?php

//Database info here
$con = new PDO('mysql:dbname=test; host=localhost', 'root', '');

function readWord($current, $next)
{
	global $con;

	$stmt=$con->prepare("INSERT INTO WORDS (WORD, SUFFIX) VALUES (:word,:suffix)");
	$stmt->bindParam(':word', $current);
	$stmt->bindParam(':suffix', $next);
	$stmt->execute();
	return $next;
}
function getNextWord($current)
{
	global $con;
	if($current!="::new::")
	{
		$stmt=$con->prepare("SELECT SUFFIX FROM WORDS WHERE WORD = :word");
		$stmt->bindParam(':word', $current);
		$stmt->execute();
		$stmt = $stmt->fetchAll(PDO::FETCH_COLUMN);
		if(count($stmt) == 0)
		{
			return getNextWord("::new::");
		}
	} else 
	{
		$stmt=$con->prepare("SELECT SUFFIX FROM WORDS WHERE 1");
		$stmt->execute();
		$stmt = $stmt->fetchAll(PDO::FETCH_COLUMN);
	}
	$choice = mt_rand(0, count($stmt)-1);

	return $stmt[$choice];
}
function getWords($line)
{
	$wordList = array();
	$line = preg_replace("/[\n\r]/",' ', $line);
	$invalidChars = "/[^A-Za-z\d\;,\: \.\']/";
	$captureChars = '/([\w,\;\:\.\']+)| /';
	$line = preg_replace($invalidChars, '', $line);
	preg_match_all($captureChars, $line, $wordList);
	return $wordList[0];
}
function getChars($chars)
{
	$chars = preg_replace("/[\n\r]/",' ', $chars);
	$invalidChars = "/[^A-Za-z\d\;,\: \.\']/";
	$chars = preg_replace($invalidChars, '', $chars);
	return str_split($chars);
}
function feedBatchWork($filename, $order, $splicer)
{
	$work = file_get_contents($filename);
	$formatted = $splicer($work);
	$current = '';

	for($i=0; $i<$order; $i++)
	{
		$current .= $formatted[$i];
		unset($formatted[$i]);
	}
	for($i=$order; $i<count($formatted)-($order-1); $i+=$order)
    {
    	$val = '';
    	for($j=0; $j<$order; $j++)
    	{
    		$val .= $formatted[$i+$j];
    	}
    	readWord($current, $val);
    	$current = $val;
    }
}
function feedWork($fileName)
{
	$handle = fopen($fileName, "r");
	if ($handle) {
		$current = '';
    	while (($line = fgets($handle)) !== false) {
    		$line = getWords($line);
    		if($current == ''){
    			$current = $line[0].' '.$line[1];
    			unset($line[0]);
    			unset($line[1]);
    		}
    		for($i=0; $i<count($line)-1; $i+=2)
    		{
    			$val = $line[$i].' '.$line[$i+1];
    			readWord($current, $val);
    			$current = $val;
    		}
    	}
	} else {
	} 
	fclose($handle);
}

/* Quick Test implementation */
feedBatchWork("feed.txt", 4, 'getWords');
$first = "::new::";
$first = getNextWord($first);

//Specific tweaks
while($first == ',' || $first== ';' || $first== ':' || $first== '.')
{
	$first = getNextWord($first);
} 
$first = ucfirst($first);
/*
for($i=0; $i<mt_rand(150, 750); $i++)
{
	if($first == ',' || $first== ';' || $first== ':')
	{
		echo $first;
		$first = getNextWord($first);
	} else if($first == '.'){
		echo $first.' ';
		$first = getNextWord($first);
		$first = ucfirst($first);
	} else {
		if($first==='i' || $first==="i'm" || $first==="i've")$first = ucfirst($first);
		echo ' '.$first;
		$first = getNextWord($first);
	}
}
*/

//Create a paragraph between 150 and 750 word chunks.
for($i=0; $i<mt_rand(150, 750); $i++)
{
		$first = getNextWord($first);
		echo $first;
}
?>