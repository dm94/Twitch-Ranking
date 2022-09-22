<?php
	require 'lib/TwitterAPIExchange.php';
	function tr_do_rt($id_tweet) {
		$settings = array(
			'oauth_access_token' => get_option('tr_twitter_oauth_access_token', 'Twitter oauth access token'),
			'oauth_access_token_secret' => get_option('tr_twitter_oauth_access_secret', 'Twitter Oauth Access Token Secret'),
			'consumer_key' => get_option('tr_twitter_consumer_key', 'Twitter Consumer Key'),
			'consumer_secret' => get_option('tr_twitter_consumer_secret', 'Twitter Consumer Secret')
		);
		$url = 'https://api.twitter.com/1.1/statuses/retweet/'.$id_tweet.'.json';
		$requestMethod = 'POST';
		$postfields=array();
		$wp = array(CURLOPT_SSL_VERIFYPEER => false);
		$twitter = new TwitterAPIExchange($settings);
		$string = json_decode($twitter->setPostfields($postfields)
		->buildOauth($url, $requestMethod)
		->performRequest(true,$wp),$assoc = TRUE);
}

	function tr_get_mentions() {
		$settings = array(
			'oauth_access_token' => get_option('tr_twitter_oauth_access_token', 'Twitter oauth access token'),
			'oauth_access_token_secret' => get_option('tr_twitter_oauth_access_secret', 'Twitter Oauth Access Token Secret'),
			'consumer_key' => get_option('tr_twitter_consumer_key', 'Twitter Consumer Key'),
			'consumer_secret' => get_option('tr_twitter_consumer_secret', 'Twitter Consumer Secret')
		);
		$url = 'https://api.twitter.com/1.1/statuses/mentions_timeline.json';
		$requestMethod = 'GET';
		$getfield="?count=10";
		$twitter = new TwitterAPIExchange($settings);
		$json =	 $twitter
		->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();
		return $json;
	}
	
	function tr_get_hashtags() {
		$settings = array(
			'oauth_access_token' => get_option('tr_twitter_oauth_access_token', 'Twitter oauth access token'),
			'oauth_access_token_secret' => get_option('tr_twitter_oauth_access_secret', 'Twitter Oauth Access Token Secret'),
			'consumer_key' => get_option('tr_twitter_consumer_key', 'Twitter Consumer Key'),
			'consumer_secret' => get_option('tr_twitter_consumer_secret', 'Twitter Consumer Secret')
		);
		$url = $url = 'https://api.twitter.com/1.1/search/tweets.json';
		$requestMethod = 'GET';
		$getfield = '?q=#gzone&result_type=recent';
		$twitter = new TwitterAPIExchange($settings);
		$json =	 $twitter
		->setGetfield($getfield)
		->buildOauth($url, $requestMethod)
		->performRequest();
		return $json;
	}
	
	function tr_is_online($url) {
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		$last_rt = get_transient('tr_last_streamer_rt');
		foreach ($postcanales as $p){
			$twitch_user = get_post_meta($p->ID,'usuario',true);
			if(strripos($url, $twitch_user) !== false && strcasecmp(get_post_meta($p->ID,'online',true),'1') == 0) {
				if (strcmp($last_rt, $twitch_user) !== 0) {
					if (hasVotes($twitch_user)) {
						set_transient('tr_last_streamer_rt', $twitch_user, 172800);
						return true;
					}
				}
				return false;
			}
		}
		return false;
	}
	
	function tr_get_array_tweets($jsonraw) {
		$json = json_decode($jsonraw);
		for ($i=0; $i<5; $i++){
			$user = $json[$i];
			$id = $user->id_str;
			$screen_name = $user->user->screen_name;
			$tweet = $user->text;
			$urls = $user->entities->urls;
			$sehadadort = $user->retweeted;
			if(!$sehadadort){
				foreach($urls as $datos){
					$url=$datos->expanded_url;
					$url=strtolower($url);
					if (stripos($url, 'twitch')){
						if(tr_is_online($url)){
							tr_do_rt($id);
						}
					}
				}
			}
		}
	}
	
	function tr_search_on_twitter() {
		if (empty(get_option('tr_twitter_oauth_access_token', '')) || empty(get_option('tr_twitter_oauth_access_secret', '')) || empty(get_option('tr_twitter_consumer_key', '')) || empty(get_option('tr_twitter_consumer_secret', ''))) {
			return;
		}
		
		$jsonraw =  tr_get_mentions();
		$rawdata =  tr_get_array_tweets($jsonraw);
	}

	function tr_send_tweet($message) {
		$settings = array(
			'oauth_access_token' => get_option('tr_twitter_oauth_access_token', 'Twitter oauth access token'),
			'oauth_access_token_secret' => get_option('tr_twitter_oauth_access_secret', 'Twitter Oauth Access Token Secret'),
			'consumer_key' => get_option('tr_twitter_consumer_key', 'Twitter Consumer Key'),
			'consumer_secret' => get_option('tr_twitter_consumer_secret', 'Twitter Consumer Secret')
		);

        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $requestMethod = 'POST';
        $postfields = array('status' => $message,);
        $twitter = new TwitterAPIExchange($settings);
        return $twitter->buildOauth($url, $requestMethod)->setPostfields($postfields)->performRequest();
	}

?>