<?php

if (!is_user_logged_in()) {
    return "";
}

$twitchID = getTwitchId(get_current_user_id());
$accion = isset($_GET['accion']) ? $_GET['accion'] : null;
$idsoli = isset($_GET['idsoli']) ? $_GET['idsoli'] : null;
$streamervotante = isset($_GET['streamervotante']) ? $_GET['streamervotante'] : null;

if (empty($twitchID)) {
    return "";
}
global $wpdb;

if ($accion=='tr_megusta') {
    echo "Gracias por votar";
    $tipovoto = 1;
    $yahavotado = false;
    $votos = $wpdb->get_results("select idsolicitud from ".$wpdb->prefix."trvotosstwitchsoli where streamervotante='$twitchID' and idsolicitud=$idsolicitud");
    foreach ($votos as $row){
        $yahavotado = true;
    }
    if (!$yahavotado) {
        $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trvotosstwitchsoli (idsolicitud,streamervotante,tipovoto) VALUES (%d,%s,%d)", 
        $idsoli,
        $streamervotante,
        $tipovoto
        ));
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
} else if($accion=='tr_nogusta') {
    echo "Gracias por votar";
    $tipovoto = 2;
    $yahavotado = false;
    $votos = $wpdb->get_results("select idsolicitud from ".$wpdb->prefix."trvotosstwitchsoli where streamervotante='$twitchID' and idsolicitud=$idsolicitud");
    foreach ($votos as $row){
        $yahavotado = true;
    }
    if(!$yahavotado){
        $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."trvotosstwitchsoli (idsolicitud,streamervotante,tipovoto) VALUES (%d,%s,%d)", 
        $idsoli,
        $streamervotante,
        $tipovoto
        ));
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
}
?>
<div class="col-lg-12 mb-2">
    <div class="card">
        <div class="card-header">
            <div class="huge"><?php _e('Solicitudes de los streamers','twitch-ranking'); ?></div>
        </div>
        <div class="card-body text-center">
            <?php _e('Desde aquí puedes ver las solicitudes para entrar como streamer que todavía no has votado','twitch-ranking'); ?>
        </div> 
    </div>
</div>
<table class="table text-center">
    <thead>
        <tr>
            <th colspan="4"><?php _e('Solicitudes pendientes','twitch-ranking'); ?></td>
        </tr>
    </thead>
    <tbody>
<?php
$solicitudes = $wpdb->get_results("select idsolicitud,nickstreamer,Fecha,mensaje,imagen,estado from ".$wpdb->prefix."trsolicitutwitch where estado=2 order by fecha DESC");
$haysolicitudes=false;
foreach ($solicitudes as $row) :
    $streameravotar=$row->nickstreamer;
    $mensaje=$row->mensaje;
    $idsolicitud=$row->idsolicitud;
    $logo=$row->imagen;
    $estado=$row->estado;
    
    $yahavotado = false;
    $votos = $wpdb->get_results("select idsolicitud from ".$wpdb->prefix."trvotosstwitchsoli where streamervotante='$twitchID' and idsolicitud = $idsolicitud");
    foreach ($votos as $row){
        $yahavotado = true;
    }
    
    if (!$yahavotado):
        $haysolicitudes=true;
        ?>
        <tr>
            <td class="text-center"><a href='https://www.twitch.tv/<?php echo $streameravotar;?>'><img src='<?php echo $logo;?>' width='50px'></a></td>
            <td class="text-center border-bottom border-dark" rowspan='2'><b><?php _e('¿Por qué crees que encajas en nuestra comunidad?','twitch-ranking'); ?></b> </br><?php echo $mensaje;?></td>
            <td class="text-center border-bottom border-dark" rowspan='2'>
                <form method="GET" action="">
                    <input type="hidden" name="idsoli" value="<?php echo $idsolicitud;?>"/>
                    <input type="hidden" name="streamervotante" value="<?php echo $twitchID; ?>"/>
                    <input type="hidden" name="accion" value="tr_megusta"/>
                    <input type="image" src="https://comunidadgzone.es/wp-content/uploads/2018/04/manoArriba.png" alt="Enviar" />
                </form>
            </td>
            <td class="text-center border-bottom border-dark" rowspan='2'>
                <form method="GET" action="">
                    <input type="hidden" name="idsoli" value="<?php echo $idsolicitud; ?>"/>
                    <input type="hidden" name="streamervotante" value="<?php echo $twitchID;?>"/>
                    <input type="hidden" name="accion" value="tr_nogusta"/>
                    <input type="image" src="https://comunidadgzone.es/wp-content/uploads/2018/04/manoAbajo.png" alt="Enviar" />
                </form>
            </td>
        </tr>
        <tr>
            <td class="text-center border-bottom border-dark"><a href='https://www.twitch.tv/<?php echo $streameravotar;?>'><?php _e('Su canal','twitch-ranking'); ?></a></td>
        </tr>
        <?php
    endif;
endforeach;
if (!$haysolicitudes) : ?>
    <tr>
        <td colspan=4 class="text-center"><?php _e('No hay solicitudes pendientes de votar','twitch-ranking'); ?></td>
    </tr>
<?php endif; ?>
    </tbody>
</table>