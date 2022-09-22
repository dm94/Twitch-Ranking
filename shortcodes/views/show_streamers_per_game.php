<?php
if (!is_user_logged_in()) {
    return "";
}

global $wpdb;
?>
<div class="row">
    <div class="col-lg-12 mb-2">
        <div class="card border border-success">
            <div class="card-header">
                <?php _e('BETA - Streamers por juego','twitch-ranking'); ?>
            </div>
        </div>
    </div>
    <?php
        $twitchID = getTwitchId(get_current_user_id());
        if (empty($twitchID)) {
            return "";
        }
        $postcanales = get_posts(array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
        $accion = isset($_GET['accion']) ? $_GET['accion'] : null;
        if ($accion=='tr_apuntarseJuego') {
            $nombrejuego = isset($_GET['nombrejuego']) ? $_GET['nombrejuego'] : null;
            $streamerjuego = isset($_GET['idstreamer']) ? $_GET['idstreamer'] : null;
            $nickjuego = isset($_GET['nickjuego']) ? $_GET['nickjuego'] : null;
            
            foreach ($postcanales as $p){
                if (strcasecmp(get_post_meta($p->ID,'twitchID',true), $streamerjuego) == 0) {
                    update_post_meta($p->ID, $nombrejuego, $nickjuego);
                }
            }
            
            avisoEnDiscord(__("Nuevo registro en el apartado de streamers por juego",'twitch-ranking'),"ApartadoPrivado");
            wp_redirect($_SERVER['HTTP_REFERER']);
        } else if ($accion=='tr_borrarseJuego') {
            $nombrejuego = isset($_GET['nombrejuego']) ? $_GET['nombrejuego'] : null;
            $streamerjuego = isset($_GET['idstreamer']) ? $_GET['idstreamer'] : null;
            
            foreach ($postcanales as $p){
                if (strcasecmp(get_post_meta($p->ID,'twitchID',true), $streamerjuego) == 0) {
                    delete_post_meta($p->ID, $nombrejuego);
                }
            }
            
            wp_redirect($_SERVER['HTTP_REFERER']);
        } else if ($accion=='tr_crearjuego') {
            $juego = isset($_GET['juego']) ? $_GET['juego'] : null;
            $url = isset($_GET['url']) ? $_GET['url'] : null;
            $urlimagen=isset($_GET['urlimagen']) ? $_GET['urlimagen'] : null;
            $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trjuegos (nombre,url,urlimagen) VALUES (%s,%s,%s)", 
            $juego,
            $url,
            $urlimagen
            ));
            wp_redirect($_SERVER['HTTP_REFERER']);
        }
        $juegoid = 0;
        $nombre = "";
        $url = "";
        $urlimagen = "";
        $juegos = $wpdb->get_results("select idjuego,nombre,url,urlimagen from ".$wpdb->prefix."trjuegos");
        foreach ($juegos as $row) {
            $juegoid=$row->idjuego;
            $nombre=$row->nombre;
            $url=$row->url;
            $urlimagen=$row->urlimagen;
        ?>
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-1 mt-2">
                            <div class="card-header">
                                <?php echo $nombre;?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border border-warning">
                            <div class="card-body">
                                <div class="col-sm-12">
                                    <a href="<?php echo $url;?>" target="_blank"><img class="img-fluid mb-0" src="<?php echo $urlimagen;?>"></img></a>
                                </div>
                                <div class="col-sm-12">
                                    <form id="formApuntarse" method='GET' action="">
                                        <input type="hidden" name="accion" value="tr_apuntarseJuego" />
                                        <input type="hidden" name="nombrejuego" value="<?php echo $nombre;?>"/>
                                        <input type="hidden" name="idstreamer" value="<?php echo $twitchID;?>"/>
                                        <div class="form-group">
                                            <label for="nick"><?php _e('Nick en el juego: ','twitch-ranking'); ?></label>
                                            <input class="form-control" type="text" name="nickjuego" maxlength="20" />
                                        </div>
                                        <input class="btn btn-success btn-lg btn-block" type="submit" value="<?php _e('Apuntarse','twitch-ranking'); ?>">
                                    </form>
                                </div>
                                <div class="col-sm-12">
                                    <form id="formBorrarse" method='GET' action="">
                                        <input type="hidden" name="accion" value="tr_borrarseJuego" />
                                        <input type="hidden" name="nombrejuego" value="<?php echo $nombre;?>"/>
                                        <input type="hidden" name="idstreamer" value="<?php echo $twitchID;?>"/>
                                        <input class="btn btn-warning btn-lg btn-block" type="submit" value="<?php _e('Borrarse','twitch-ranking'); ?>">
                                    </form>
                                </div>
                                <div class="clearfix"></div>
                            </div> 
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border border-info">
                            <div class="card-header">
                                <div class="float-left">
                                    <p class="font-weight-bold"><?php _e('Streamer','twitch-ranking'); ?></strong>
                                </div>
                                <div class="float-right">
                                    <p class="text-right"><?php _e('Nick','twitch-ranking'); ?></p>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="card-body">
                            <?php
                                $streamerjuego = "";
                                $nickjuego = "";
                                
                                foreach ($postcanales as $p) {
                                    $nickjuego = get_post_meta($p->ID,$nombre,true);
                                    $streamerjuego = get_post_meta($p->ID,'usuario',true);
                                    if (!empty($nickjuego)) {
                                        ?>
                                        <div class="float-left">
                                            <p class="font-weight-bold"><?php echo $streamerjuego;?></p>
                                        </div>
                                            <div class="float-right text-wrap">
                                                <p class="text-right"><?php echo $nickjuego;?></p>
                                            </div>
                                            <div class="clearfix"></div>
                                        <?php
                                    }
                                }
                            ?>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="col-lg-12 mt-2">
            <div class="card border border-warning">
                <div class="card-header text-center">
                    <?php _e('Si quieres que se añada un juego contacta con el administrador','twitch-ranking'); ?>
                </div>
            </div>
        </div>
    </div>