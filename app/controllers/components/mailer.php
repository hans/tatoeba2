<?php 
/*
    Tatoeba Project, free collaborativ creation of languages corpuses project
    Copyright (C) 2009  TATOEBA Project(should be changed)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class MailerComponent extends Object{
	var $from		= 'trang.dictionary.project@gmail.com';
	var $fromName	= 'TATOEBA';
	var $to			= null;
	var $toName		= null;
	var $subject	= null;
	var $message	= null;
	
	function send(){
		/* email notification */
		$this->authgMail(
			$this->from, 
			$this->fromName, 
			$this->to, 
			$this->toName, 
			$this->subject, 
			$this->message);
	}

	function authgMail($from, $namefrom, $to, $nameto, $subject, $message){
        if($_SERVER['SERVER_NAME'] != 'tatoeba.org'){
            return;
        }
        
		/*  your configuration here  */

		$smtpServer = "tls://smtp.gmail.com"; //does not accept STARTTLS
		$port = "465"; // try 587 if this fails
		$timeout = "45"; //typical timeout. try 45 for slow servers
		$username = "tatoeba.tx03@gmail.com"; //your gmail account
		$password = "toFGeF89"; //the pass for your gmail
		$localhost = $_SERVER['REMOTE_ADDR']; //requires a real ip
		$newLine = "\r\n"; //var just for newlines
		 
		/*  you shouldn't need to mod anything else */

		//connect to the host and port
		$smtpConnect = fsockopen($smtpServer, $port, $errno, $errstr, $timeout);
		//echo $errstr." - ".$errno;
		$smtpResponse = fgets($smtpConnect, 4096);
		if(empty($smtpConnect))
		{
		   $output = "Failed to connect: $smtpResponse";
		   //echo $output;
		   return $output;
		}
		else
		{
		   $logArray['connection'] = "Connected to: $smtpResponse";
		   //echo "connection accepted<br>".$smtpResponse."<p />Continuing<p />";
		}

		//you have to say HELO again after TLS is started
		   fputs($smtpConnect, "HELO $localhost". $newLine);
		   $smtpResponse = fgets($smtpConnect, 4096);
		   $logArray['heloresponse2'] = "$smtpResponse";
		  
		//request for auth login
		fputs($smtpConnect,"AUTH LOGIN" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authrequest'] = "$smtpResponse";

		//send the username
		fputs($smtpConnect, base64_encode($username) . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authusername'] = "$smtpResponse";

		//send the password
		fputs($smtpConnect, base64_encode($password) . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['authpassword'] = "$smtpResponse";

		//email from
		fputs($smtpConnect, "MAIL FROM: <$from>" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['mailfromresponse'] = "$smtpResponse";

		//email to
		fputs($smtpConnect, "RCPT TO: <$to>" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['mailtoresponse'] = "$smtpResponse";

		//the email
		fputs($smtpConnect, "DATA" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['data1response'] = "$smtpResponse";

		//construct headers
		$headers = "MIME-Version: 1.0" . $newLine;
		$headers .= "Content-type: text/plain; charset=UTF-8" . $newLine;
		$headers .= "To: $nameto <$to>" . $newLine;
		$headers .= "From: $namefrom <$from>" . $newLine;
		$headers .= "Subject: $subject" . $newLine;

		//observe the . after the newline, it signals the end of message
		fputs($smtpConnect, "$headers\r\n\r\n$message\r\n.\r\n");
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['data2response'] = "$smtpResponse";

		// say goodbye
		fputs($smtpConnect,"QUIT" . $newLine);
		$smtpResponse = fgets($smtpConnect, 4096);
		$logArray['quitresponse'] = "$smtpResponse";
		$logArray['quitcode'] = substr($smtpResponse,0,3);
		fclose($smtpConnect);
		//a return value of 221 in $retVal["quitcode"] is a success
		return($logArray);
	}
}
?>
