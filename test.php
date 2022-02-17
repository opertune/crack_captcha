<?php
require __DIR__."./vendor/autoload.php";
use thiagoalessio\TesseractOCR\TesseractOCR;

$i = 0;
$resp = '';
// Loop while captcha is wrong 
do{
	try{
		// Get HTML Page
		$curlSession = curl_init();
		curl_setopt($curlSession, CURLOPT_URL, 'http://challenge01.root-me.org/programmation/ch8/');
		curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curlSession, CURLOPT_COOKIEJAR, 'cookie.txt');
		$html = curl_exec($curlSession);
		curl_close($curlSession);

		// Extract and format base64 image (captcha)
		$start = strpos($html, 'base64,')+7;
		$len = abs($start - strpos($html, '=" />'));
		$base64 = substr($html, $start, $len);
		$base64 = str_replace(' ', '+', $base64);

		// Decode and save image (base64) to png
		$img = base64_decode($base64);
		$file = "./img/captcha.png";
		file_put_contents($file, $img);

		// Decode captcha
		$captcha = (new TesseractOCR('./img/captcha.png'))
		    ->executable("C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe")
		    ->allowlist(range('a', 'z'), range(0, 9))
		    ->run();
		$captcha = str_replace(' ','',$captcha);


		// Curl submit form
		$url = "http://challenge01.root-me.org/programmation/ch8/";
		$headers = array(
		   "Content-Type: application/x-www-form-urlencoded",
		);
		// Extract PHPSESSID cookie
		$cookieFile = file_get_contents('cookie.txt');
		$start = strpos($cookieFile, 'PHPSESSID	');
		$phpsessid = substr($cookieFile, $start+10, 32);


		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'cametu='.$captcha);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_COOKIE, 'PHPSESSID='.$phpsessid.';spip_session=361961_0a4086b2489a80d1d89a91c83958e346;msg_history=explication_site_multilingue%3B');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);

		$resp = curl_exec($curl);
		curl_close($curl);

		$i++;
		echo 'Try: '.$i;
	}catch(Exception $e){}
}while(strpos($resp, 'Congratz, le flag est') == false);

// Display result
var_dump($resp);