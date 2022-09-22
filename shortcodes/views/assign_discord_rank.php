<?php
if (!is_user_logged_in()) {
    return "";
}
if (!empty($_GET['elementor-preview'])) {
    return;
}
?>
<div class="col-lg-6">
    <div class="card">
        <div class="card-header">
            <div class="huge"><?php _e('Asignar rango en discord','twitch-ranking'); ?></div>
        </div>
        <div class="card-body">
        <?php
            $discordcode = isset($_GET['code']) ? $_GET['code'] : null;
            if(!empty($discordcode)) {
                $token_request = "https://discord.com/api/oauth2/token";
                $token = curl_init();
                curl_setopt_array($token, array(
                    CURLOPT_URL => $token_request,
                    CURLOPT_POST => 1,
                    CURLOPT_POSTFIELDS => array(
                        "grant_type" => "authorization_code",
                        "client_id" => get_option('tr_discord_client_id', 'Discord Client ID'),
                        "client_secret" => get_option('tr_discord_client_secret', 'Discord Client Secret'),
                        "redirect_uri" => get_permalink(),
                        "code" => $discordcode
                    )
                ));
                curl_setopt($token, CURLOPT_RETURNTRANSFER, true);
                $resp = json_decode(curl_exec($token));
                curl_close($token);
                if (isset($resp->access_token)) {
                    $access_token = $resp->access_token;
                    $info_request = "https://discord.com/api/users/@me";
                    $info = curl_init();
                    curl_setopt_array($info, array(
                        CURLOPT_URL => $info_request,
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: Bearer {$access_token}"
                        ),
                        CURLOPT_RETURNTRANSFER => true
                    ));
                    $user = json_decode(curl_exec($info));
                    curl_close($info);
                    $discordusername=$user->username.'#'.$user->discriminator;
                    echo $discordusername;
                    $discordid=$user->id;
                    
                    //Aniadimos la informacion del discord al usuario

                    $wordpressid = get_current_user_id();
                    update_user_meta($wordpressid, 'discordusername', $discordusername);
                    update_user_meta($wordpressid, 'discordid', $discordid);
                    
                    //Asignamos el rango
                    $addrank = curl_init();
                    $addrank_request = 'https://discord.com/api/guilds/'.get_option('tr_discord_guild_id', 'Discord Guild ID').'/members/'.$discordid.'/roles/'.get_option('tr_discord_role_id', 'Discord Role ID');
                    curl_setopt_array($addrank, array(
                        CURLOPT_URL => $addrank_request,
                        CURLOPT_HTTPHEADER => array(
                            "Authorization: Bot ".get_option('tr_discord_accesss_token', 'Discord Access Token')
                        ),
                        CURLOPT_POSTFIELDS => array(
                            access_token => get_option('tr_discord_accesss_token', 'Discord Access Token'),
                            guild.id => get_option('tr_discord_guild_id', 'Discord Guild ID'),
                            user.id => $discordid,
                            role.id => get_option('tr_discord_role_id', 'Discord Role ID')
                        )
                    ));
                    curl_setopt($addrank, CURLOPT_CUSTOMREQUEST, 'PUT');
                    $informacion = curl_exec($addrank);
                    curl_close($addrank);
                    print($informacion);
                } else {
                    echo json_encode(array("message" => "Authentication Error"));
                }
            } else {
                ?>
                <a href="https://discord.com/api/oauth2/authorize?client_id=<?php echo get_option('tr_discord_client_id', 'Discord Client ID');?>&redirect_uri=<?php echo get_permalink();?>&response_type=code&scope=identify" class="btn btn-outline-danger btn-block"><?php _e('Vincular discord','twitch-ranking'); ?></a>
            <?php
            }
        ?>
        </div> 
    </div>
</div>