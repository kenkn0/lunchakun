<?php
// まずは HTTPステータス 200 を返す
http_response_code(200) ;
echo '200 {}';


// 送られて来たJSONデータを取得
$json_string = file_get_contents('php://input');
$json = json_decode($json_string);

// JSONデータから返信先を取得
$replyToken = $json->events[0]->replyToken;
// JSONデータから送られてきたメッセージを取得
$message = $json->events[0]->message->text;

//位置情報取得
$location = $json->events[0]->message;
$lat = $location->latitude;
$lon = $location->longitude;

//Type取得
$type = $json->events[0]->message->type;
$addType = $json->result[0]->content->opType;

//postback
$type2 = $json->events[0]->type;
$replay = $json->events[0]->postback->data;
parse_str($replay);

// HTTPヘッダを設定
$channelToken = 'LINEtoken';
$headers = [
	'Authorization: Bearer ' . $channelToken,
	'Content-Type: application/json; charset=utf-8',
];



if($type == 'text' || $addType == 4){
	$message = "「使い方」\n①左下の「+」マークを選択\n②「location」を選択\n③希望の位置情報を選択してください。\n";
	$post = [
		'replyToken' => $replyToken,
		'messages' => [
			[
					'type'     => 'text',
					'text' => $message,
			],
		],
	];

		$post = json_encode($post);

		// HTTPリクエストを設定
		$ch = curl_init('https://api.line.me/v2/bot/message/reply');
		$options = [
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_BINARYTRANSFER => true,
			CURLOPT_HEADER => true,
			CURLOPT_POSTFIELDS => $post,
		];

		// 実行
		curl_setopt_array($ch, $options);


		// エラーチェック
		$result = curl_exec($ch);
		$errno = curl_errno($ch);
		if ($errno) {
			return;
		}

		// HTTPステータスを取得
		$info = curl_getinfo($ch);
		$httpStatus = $info['http_code'];

		$responseHeaderSize = $info['header_size'];
		$body = substr($result, $responseHeaderSize);

		// 200 だったら OK
		echo $httpStatus . ' ' . $body;
}

if($type == 'location' || $type2 == 'postback'){

		//ぐるナビ検索
			$gnavi = "ぐるなびtoken";
			$offset = rand(1,10);
			$offset_page = rand(1,10);
			$uri = "http://api.gnavi.co.jp/RestSearchAPI/20150630/?format=json&keyid=".$gnavi."&latitude=".$lat."&longitude=".$lon."&range=2&hit_per_page=1&offset=".$offset."&offset_page=".$offset_page;
			//$uri = "http://api.gnavi.co.jp/RestSearchAPI/20150630/?format=json&keyid=".$gnavi."&id=g821200";
			$json = file_get_contents($uri);
			$json = json_decode($json);
			$id = $json->rest->id;
			$name = $json->rest->name;
			$category = $json->rest->category;
			$image = $json->rest->image_url->shop_image1;
			if(is_object($image)){
				$image = "https://owl-knj.com/bot/no_image.jpg";
			}
			$uri2 = "https://api.gnavi.co.jp/PhotoSearchAPI/20150630/?format=json&keyid=".$gnavi."&shop_id=".$id;
			$json2 = file_get_contents($uri2);
			$json2 = json_decode($json2);
			$result = $json->{'rest'}->{'url'};
			if(is_null($json2->response->{'0'}->photo->total_score)){
				$score = "未評価\n";
			}else{
				$score = $json2->response->{'0'}->photo->total_score."\n";
			}
			if(is_object($json->rest->lunch)){
			  $lunch = null;
			}else{
			  $lunch = $json->rest->lunch."円\n";
			}
			if(is_object($json->rest->budget)){
			  $ave = null;
			}else{
			  $ave = $json->rest->budget."円\n";
			}
				if(is_null($lunch)){
					$lunch = "不明\n";
				}
				if(is_null($ave)){
					$ave = "不明\n";
				}

				$template = [
					'type'    => 'buttons',
					'thumbnailImageUrl' => $image,
					'title'   => $name."(".$category.")",
					'text'    => "【評価】".$score."【ランチ予算】".$lunch."【平均予算】".$ave,
					'actions' => [
						[
							'type'  => 'uri',
              'uri'   => $result,
              'label' => 'お店のページを開く'
						],
						[
							'type'=>'postback',
							'label'=>'もう一回',
							'data'=>'lat='.$lat.'&lon='.$lon,
						]
					],
				];

				$post = [
					'replyToken' => $replyToken,
					'messages' => [
						[
								'type'     => 'template',
				        'altText'  => '代替テキスト',
				        'template' => $template
						],
					],
				];

				$post = json_encode($post);

				// HTTPリクエストを設定
				$ch = curl_init('https://api.line.me/v2/bot/message/reply');
				$options = [
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_HTTPHEADER => $headers,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_BINARYTRANSFER => true,
					CURLOPT_HEADER => true,
					CURLOPT_POSTFIELDS => $post,
				];

				// 実行
				curl_setopt_array($ch, $options);


				// エラーチェック
				$result = curl_exec($ch);
				$errno = curl_errno($ch);
				if ($errno) {
					return;
				}

				// HTTPステータスを取得
				$info = curl_getinfo($ch);
				$httpStatus = $info['http_code'];

				$responseHeaderSize = $info['header_size'];
				$body = substr($result, $responseHeaderSize);

				// 200 だったら OK
				echo $httpStatus . ' ' . $body;

}
