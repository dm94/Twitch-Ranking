<?php
if (!is_user_logged_in()) {
    return "";
}

global $wpdb;
$twitchID = getTwitchId(get_current_user_id());

if (empty($twitchID)) {
    return "";
}

$strikes = 0;
$participacion = 0;

$time = strtotime(date("d-m-Y H:i:00",time()));
$participaciones = $wpdb->get_results("select * from ".$wpdb->prefix."trkarma where IDStreamer='$twitchID' and tipo=2 order by fechaCreacion");
foreach ($participaciones as $row2) {
    $fechaCreacion1=$row2->fechaCreacion;
    $dateini1 = date($fechaCreacion1);
    $mod_date1 = strtotime($dateini1."+ 60 days");
    if ($time < $mod_date1) {
        $participacion++;
    }
}
$strikeList = $wpdb->get_results("select * from ".$wpdb->prefix."trkarma where IDStreamer='$twitchID' and tipo=1 order by fechaCreacion");
foreach ($strikeList as $row3) {
    $fechaCreacion=$row3->fechaCreacion;
    $dateini = date($fechaCreacion);
    $mod_date = strtotime($dateini."+ 120 days");
    if ($time < $mod_date) {
        $strikes++;
    }
}

$karma = ($participacion)-($strikes*5);
?>

<div class="col-lg-12">
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-xs-6 text-left">
                            <div class="huge"><?php _e('Tu Karma','twitch-ranking'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="float-left">
                        <p><strong><?php _e('Strikes','twitch-ranking'); ?></strong></p>
                        <p><strong><?php _e('Colaboraciones','twitch-ranking'); ?></strong></p>
                        <p><strong><?php _e('Karma Actual','twitch-ranking'); ?></strong></p>
                    </div>
                    <div class="float-right">
                        <p class="text-right"><?php echo $strikes; ?>*5
                        </p>
                        <p class="text-right"><?php echo $participacion; ?>*1
                        </p>
                        <p class="text-right"><?php echo $karma; ?>
                        </p>
                    </div>
                    <div class="clearfix"></div>
                </div> 
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-xs-6 text-left">
                            <div class="huge"><?php _e('Log Karma','twitch-ranking'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <?php
                        $time = strtotime(date("d-m-Y H:i:00",time()));
                        $todo = $wpdb->get_results("select * from ".$wpdb->prefix."trkarma where IDStreamer='$twitchID' order by fechaCreacion");
                        foreach ($todo as $row4) {
                            $fechaCreacion2 = $row4->fechaCreacion;
                            $motivo = $row4->motivo;
                            $traducido = "....................................";
                            $dateini1 = date($fechaCreacion2);
                            $mod_date1 = strtotime($dateini1."+ 60 days");
                            if ($time < $mod_date1) {
                                $traducido = "....................................";
                                if ($motivo == "1") {
                                    $traducido = __('Strike - No llegar al minimo de requisitos mensuales','twitch-ranking');
                                } else if ($motivo == "2") {
                                    $traducido = __('Strike - No tener el logo o link en los cardes de twitch','twitch-ranking');
                                } else if ($motivo == "3") {
                                    $traducido = __('Strike - No participar en un evento obligatorio','twitch-ranking');
                                } else if ($motivo == "4") {
                                    $traducido = __('Strike - No respetar las normas de la comunidad','twitch-ranking');
                                } else if ($motivo == "11") {
                                    $traducido = __('Colaboración - Participar en un evento','twitch-ranking');
                                } else if ($motivo == "12") {
                                    $traducido = __('Colaboración - Participar en la comunidad','twitch-ranking');
                                } else if ($motivo == "13") {
                                    $traducido = __('Colaboración - Crear un evento','twitch-ranking');
                                }
                                echo "<p>".$dateini1." - ".$traducido."</p>";
                            }
                        }
                    ?>
                </div> 
            </div>
        </div>
    </div>
</div>