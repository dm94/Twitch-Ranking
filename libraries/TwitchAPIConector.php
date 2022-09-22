<?php

class TwitchAPIConector {

    private $base_url = 'https://api.twitch.tv/helix/';
    private $auth_url = 'https://id.twitch.tv/oauth2/token';
    private $client_id;
	private $client_secret;
	private $redirect_url;
    private $token_name = 'twitch_token';
    private $scope_array = array('user_read',);

    public function __construct($client_id,$client_secret,$redirect_url) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->redirect_url = $redirect_url;
	}

    private function get_token() {
        $token = get_transient($this->token_name);
        if (!empty($token)) {
            return $token;
        } else {
            $response = wp_remote_post($this->auth_url.'?client_id='.$this->client_id.'&client_secret='.$this->client_secret.'&grant_type=client_credentials');
            if (is_array($response) && !is_wp_error($response)) {
                $return_code = wp_remote_retrieve_response_code($response);
                if ($return_code == '200') {
                    $body = $response['body'];
                    try {
                        $json = json_decode($response['body']);
                        $token = $json->access_token;
                        set_transient($this->token_name, $token, '3600');
                        return $token;
                    } catch (Exception $ex){}
                }
            }
        }
    }

    public function get_from_endpoint($endpoint) {
        $token = $this->get_token();
        if ($token != null && $endpoint != null) {
            $response = wp_remote_get($this->base_url.$endpoint, array(
                'headers' => array(
                    'Client-ID' => $this->client_id,
                    'Authorization' => 'Bearer '.$token
                )
            ));
            if (is_array($response) && !is_wp_error($response)) {
                $return_code = wp_remote_retrieve_response_code($response);
                if ($return_code == '401') {
                    delete_transient($this->token_name);
                } else {
                    try {
                        $json = json_decode($response['body']);
                        return $json;
                    } catch (Exception $ex){}
                }
            }
        }
        delete_transient($this->token_name);
        return null;
    }

    public function authenticate() {
		$i		= 0;
		$return = '';
		$len	= count($this->scope_array);
		foreach ($this->scope_array as $scope) {
			if ($i == $len - 1) {
				$scope .= "";
				$return .= $scope;
			} else {
				$scope .= "+";
				$return .= $scope;
			}

			$i++;
		}
		$scope = $return;
		$authenticate_url = 'https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . $this->redirect_url . '&scope=' . $scope.'&force_verify=true';
		return $authenticate_url;
	}
	
	public function get_access_token($code) {
		$ch = curl_init("https://id.twitch.tv/oauth2/token");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$fields = array(
			'client_id' => $this->client_id,
			'client_secret' => $this->client_secret,
			'code' => $code,
			'grant_type' => 'authorization_code',
			'redirect_uri' => $this->redirect_url
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
		$data = curl_exec($ch);
		$json = json_decode($data, true);
		curl_close($ch);
		return $json['access_token'];
	}

    public function get_user($access_token) {
		$response = wp_remote_get($this->base_url.'users', array(
            'headers' => array(
                'Client-ID' => $this->client_id,
                'Authorization' => 'Bearer '.$access_token
            )
        ));
        if (is_array($response) && !is_wp_error($response)) {
            $return_code = wp_remote_retrieve_response_code($response);
            if ($return_code != '401') {
                try {
                    $json = json_decode($response['body']);
                    return $json;
                } catch (Exception $ex){}
            }
        }
		return null;
	}

    public function get_users($ids) {
        if (!empty($ids)) {
            $response = $this->get_from_endpoint('users?id='.$ids);
        
            if ($response != null && $response->data != null) {
                return $response->data;
            }
        }

		return null;
    }

    public function get_user_by_username($username) {
        if (!empty($username)) {
            $response = $this->get_from_endpoint('users?login='.$username);
        
            if ($response != null && $response->data != null  && $response->data[0] != null) {
                return $response->data[0];
            }
        }

		return null;
    }

    public function get_streams($ids) {
        if (!empty($ids)) {
            $response = $this->get_from_endpoint('streams?user_id='.$ids);
        
            if ($response != null && $response->data != null) {
                return $response->data;
            }
        }

		return null;
    }

    public function get_teams($id) {
        if (!empty($id)) {
            $response = $this->get_from_endpoint('teams/channel?broadcaster_id='.$id);
        
            if ($response != null && $response->data != null) {
                return $response->data;
            }
        }

		return null;
    }

    public function get_followers($id) {
        if (!empty($id)) {
            $response = $this->get_from_endpoint('users/follows?to_id='.$id);
        
            if ($response != null && $response->total != null) {
                return $response->total;
            }
        }

		return 0;
    }

    public function get_members_ids($teamid) {
        $members = array();
        if (!empty($teamid)) {
            $response = $this->get_from_endpoint('teams?id='.$teamid);
            if ($response != null && $response->data != null && $response->data[0] != null && $response->data[0]->users != null) {
                $all_members_data = $response->data[0]->users;
                if (is_array($all_members_data)) {
                    foreach($all_members_data as $streamer) {
                        array_push($members, $streamer->user_id);
                    }
                }
            }
        }

		return $members;
    }
}

?>