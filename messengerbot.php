<?php
// parameters
$hubVerifyToken = '**';
$accessToken = "***";
$key = "***";
$cx = "***";
// check token at setup
if ($_REQUEST['hub_verify_token'] === $hubVerifyToken) {
  echo $_REQUEST['hub_challenge'];
  exit;
}
// handle bot's anwser
$input = json_decode(file_get_contents('php://input'), true);
$senderId = $input['entry'][0]['messaging'][0]['sender']['id'];
$messageText = $input['entry'][0]['messaging'][0]['message']['text'];
$messageText = strtolower($messageText);
$response;
if(!empty($messageText)){
	$response = file_get_contents("https://graph.facebook.com/v2.6/".$senderId."?access_token=".$accessToken);  
	$user = json_decode($response, true); 
	$name = $user['first_name'];
	$lname = $user['last_name'];
	$sendee = file_get_contents("sender.json");
	$sender = json_decode($sendee, true);
	$inp = file_get_contents("answers.json");
	$tempArray = json_decode($inp, true);
	$q = preg_replace('/(!|\?|\.|\s)+$/', '', $messageText);
	$question = preg_replace('/(\s)+/', ' ', $q);
	if((!array_key_exists($lname , $sender) && $sender[$lname] != $name)|| $messageText == "instructions") {
		$data = array($lname =>  $name);
		$data = array_merge($sender, $data);
		$jsonData = json_encode($data);
		file_put_contents("sender.json", $jsonData);
		$answer = "Hello ". $name. ". I am VikBot. \nIf you want to ask me a question say \"Hey VikBot, (your query)\". \nIf you want an image search say \"Hey VikBot, (your query)\" with the key word \"image\" within your query. \nIf you want to add to my knowledge of answers to questions, say \"add: (question you know the answer to) answer: (the answer to the question you added). \n Once you do this you can just say: \"(that question (or any question) that you or anybody has added previously to my knowledge)\" at any time to get that specific answer from me. \nRemember: spelling, spacing and punctuation all matter if you want to talk to me.";
		$response = [
			'recipient' => [ 'id' => $senderId ],
			'message' => [ 'text' => $answer ],
		];
		$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_exec($ch);
		curl_close($ch);
	}elseif(preg_match("/^(generate meme: )/", $messageText)){
		$messageText = str_replace("generate meme: ", "", $messageText);
		$meme = explode("|", $messageText);
		$imagefile = file_get_contents("http://version1.api.memegenerator.net/Generators_Search?q=".rawurlencode($meme[0]));
		$images = json_decode($imagefile, true);
		$genID = $images['result'][0]['generatorID'];
		$imageID = $images['result'][0]['imageID'];
		$memefile = file_get_contents("http://version1.api.memegenerator.net/Instance_Create?username=test8&password=test8&languageCode=en&generatorID=".rawurlencode($genID)."&imageID=".rawurlencode($imageID)."&text0=".rawurlencode($meme[1])."&text1=".rawurlencode($meme[2]));
		$memegen = json_decode($memefile, true);
		$memeimage = $memegen['result']['instanceImageUrl'];
			$answer = ["attachment"=>[
			  "type"=>"image",
			  "payload"=>[
				"url"=>$memeimage
			  ]
			]];
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => $answer 
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		
	}elseif(preg_match("/^(artist: )/", $messageText) && preg_match("/( song: )/", $messageText)){
		$messageText = str_replace("artist: ", "", $messageText);
		$songInfo = explode(" song: ", $messageText);
		$songSearch = file_get_contents("https://api.spotify.com/v1/search?q=track:".rawurlencode($songInfo[1])."%20artist:".rawurlencode($songInfo[0])."&type=track");
		$songs = json_decode($songSearch, true);
		$url = $songs['tracks']['items'][0]['external_urls']['spotify'];
		$title = $songs['tracks']['items'][0]['name'];
		$image = $songs['tracks']['items'][0]['album']['images'][1]['url'];
		$answer = ["attachment"=>[
			  "type"=>"template",
			  "payload"=>[
				"template_type"=>"generic",
				"elements"=>[
				  [
					"title"=>$title,
					"image_url"=>$image,
					"buttons"=>[
					  [
						"type"=>"web_url",
						"url"=>$url,
						"title"=>"Play Song on Spotify"
					  ],             
					]
				  ]
				]
			  ]
			]];
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => $answer
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		
	}elseif(preg_match("/^(add: )/", $messageText) && preg_match("/( answer: )/", $messageText)){
		$messageText = str_replace("add: ", "", $messageText);
		$QnA = explode(" answer: ", $messageText);
		$q2 = preg_replace('/(!|\?|\.|\s)+$/', '', $QnA[0]);
		$question2 = preg_replace('/(\s)+/', ' ', $q2);
		if(array_key_exists($question2 , $tempArray )){
			$answer = "The answer to that question has alreay been taught to me";
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => [ 'text' => $answer ],
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		}
		else{
			$data = array($question2 =>  $QnA[1]);
			$data = array_merge($tempArray, $data);
			$jsonData = json_encode($data);
			file_put_contents("answers.json", $jsonData);
			$answer = "Ok thanks for teaching me that";
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => [ 'text' => $answer ],

			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		}
	}elseif(array_key_exists ($question, $tempArray)) {
		$answer = $tempArray[$question];
		$response = [
			'recipient' => [ 'id' => $senderId ],
			'message' => [ 'text' => $answer ],

		];
		$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_exec($ch);
		curl_close($ch);
	}elseif (preg_match("/^(hey vikbot,)/", $messageText)){
		$messageText = str_replace("hey vikbot,", "", $messageText);
		$search = file_get_contents("https://www.googleapis.com/customsearch/v1?key=".$key."&cx=".$cx."&q=".rawurlencode($messageText));
		$imageSearch = file_get_contents("https://www.googleapis.com/customsearch/v1?key=".$key."&cx=".$cx."&searchType=image&q=".rawurlencode($messageText));
		$urls = json_decode($search, true);
		$images = json_decode($imageSearch, true);
		$url = $urls['items'][0]['link'];
		$webtitle = $urls['items'][0]['title'];
		$snippet = $urls['items'][0]['snippet'];
		$image = $images['items'][0]['link'];
		$image2 = $images['items'][1]['link'];
		if(preg_match("/( image)/", $messageText)){
			$answer = ["attachment"=>[
			  "type"=>"image",
			  "payload"=>[
				"url"=>$image
			  ]
			]];
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => $answer 
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
			
		}elseif($messageText == "" || $messageText == "\s" ){
			$answer = "You did not give me any questions to query";
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => [ 'text' => $answer ],
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		
		}else{
			$answer = ["attachment"=>[
			  "type"=>"template",
			  "payload"=>[
				"template_type"=>"generic",
				"elements"=>[
				  [
					"title"=>$webtitle,
					"image_url"=>$image,
					"subtitle"=>$snippet,
					"buttons"=>[
					  [
						"type"=>"web_url",
						"url"=>$url,
						"title"=>"Here is What I found"
					  ],             
					]
				  ]
				]
			  ]
			]];
			$response = [
				'recipient' => [ 'id' => $senderId ],
				'message' => $answer
			];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		}
	}else{
		$answer = "I did not understand, say the word \"instructions\" so I can repeat my instructions so you can learn how to talk with me";
		$response = [
			'recipient' => [ 'id' => $senderId ],
			'message' => [ 'text' => $answer ],
		];
			$ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$accessToken);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
			curl_exec($ch);
			curl_close($ch);
		
	}
}



