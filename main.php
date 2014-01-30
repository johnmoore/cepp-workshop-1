<?PHP
require "HTTPrequest.php";
require "config.php";

$req = new HTTPrequest();

function logIntoLMS($user, $pass, $req) {
	$req->sendGET("http://learn.bu.edu/");
	$req->follow();
	$req->sendPOST("https://weblogin.bu.edu//web@login3?jsv=1.5p&br=un&fl=0", "p=&act=up&js=yes&jserror=&c2f=&r2f=&user=".urlencode($user)."&pw2=".urlencode($pass)."&pw=".urlencode($pass), $req->cookie);
	$loc = reset(explode("\"", end(explode("; URL=", $req->response['data']))));
	$req->sendGET($loc, $req->cookie);
	$req->follow();
	$attempt = 0;
	do {
		$loc = html_entity_decode(reset(explode("\"", end(explode("<form action=\"", $req->response['data'])))));
		$relaystate = html_entity_decode(reset(explode("\"", end(explode("<input type=\"hidden\" name=\"RelayState\" value=\"", $req->response['data'])))));
		$samlresponse = reset(explode("\"", end(explode("<input type=\"hidden\" name=\"SAMLResponse\" value=\"", $req->response['data']))));
		$postData = "RelayState=".urlencode($relaystate)."&SAMLResponse=".urlencode($samlresponse);
		$req->sendPOST($loc, $postData, $req->cookie);
		$req->follow();
		$attempt += 1;
	} while (strpos($req->response['data'], "Blackboard Learn") === false && $attempt < 5);
	if (strpos($req->response['data'], "Blackboard Learn") === false) {
		return false;
	}
	return true;
}

function getGradeHTML($courseid, $req) {
	$req->sendGET("https://learn.bu.edu/webapps/bb-mygrades-BBLEARN/myGrades?course_id=".urlencode($courseid)."&stream_name=mygrades", $req->cookie);
	return $req->response['data'];
}

function hashGradeHTML($html) {
	$static = reset(explode("<script type=\"text/javascript\">", end(explode("<div class=\"detail-contents grades-details\">", $html, 2))));
	print $static;
	return md5($static);
}

function 

logIntoLMS(LMS_USER, LMS_PASS, $req);
$html = getGradeHTML("_12524_1", $req);
$hash = hashGradeHTML($html);

$db = new mysqli(SQL_HOST, SQL_USER, SQL_PASS, SQL_DB);

?>