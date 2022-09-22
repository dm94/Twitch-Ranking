<?php
if (!is_user_logged_in()) {
    return "";
}

$twitchID = getTwitchId(get_current_user_id());
if (empty($twitchID)) {
    return "";
}

$posteventos = get_posts( array('post_type' => 'eventos','numberposts' => -1, 'no_found_rows' => true));
$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
$accion = isset($_POST['accion']) ? $_POST['accion'] : null;

if ($accion=='tr_apuntarseevento') {
    $idsoli = isset($_POST['idsoli']) ? $_POST['idsoli'] : null;
    
    foreach ($posteventos as $pe){
        if (strcasecmp($pe->ID, $idsoli) == 0) {
            $tr_inscribed_list = get_post_meta($pe->ID,'tr_inscribed_list',true);
            if (!get_post_meta($pe->ID,'tr_inscribed_list',true)) {
                update_post_meta($pe->ID, 'tr_inscribed_list',  array($twitchID));
            } else {
                if (!in_array($twitchID, $tr_inscribed_list)) {
                    array_push($tr_inscribed_list, $twitchID);
                    update_post_meta($pe->ID, 'tr_inscribed_list', $tr_inscribed_list);
                }
            }
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
} else if ($accion=='tr_desapuntarseevento') {
    $idsoli = isset($_POST['idsoli']) ? $_POST['idsoli'] : null;
    
    foreach ($posteventos as $pe){
        if (strcasecmp($pe->ID, $idsoli) == 0) {
            $tr_inscribed_list = get_post_meta($pe->ID,'tr_inscribed_list',true);
            if (($key = array_search($twitchID, $tr_inscribed_list)) !== false) {
                unset($tr_inscribed_list[$key]);
                sort($tr_inscribed_list);
                update_post_meta($pe->ID, 'tr_inscribed_list', $tr_inscribed_list);
            }
        }
    }
    wp_redirect($_SERVER['HTTP_REFERER']);
} else if ($accion=='tr_crearevento') {
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
    $fechaini = isset($_POST['fechaini']) ? $_POST['fechaini'] : null;
    $fechafin = isset($_POST['fechafin']) ? $_POST['fechafin'] : null;
    $idevento = isset($_POST['idevento']) ? $_POST['idevento'] : null;
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : null;
    require 'templates/libraries/Parsedown.php';
    $Parsedown = new Parsedown();
    $descripcion = $Parsedown->text($descripcion);
    
    $new_post = array(
        'post_title' => $nombre,
        'post_status' => 'publish',
        'post_type' => 'eventos',
        'post_content' => $descripcion,
        'post_category' => array(0)
    );
    $post_id = wp_insert_post($new_post);
    
    $arg = array(
        'ID' => $post_id,
        'post_author' => get_current_user_id(),
    );
    wp_update_post($arg);
    update_post_meta($post_id, 'tr_creator_twitch_id', $twitchID);
    update_post_meta($post_id, 'tr_date_start', $fechaini);
    update_post_meta($post_id, 'tr_date_end', $fechafin);

    $new_post_permalink = get_permalink($post_id);
    
    avisoEnDiscord(sprintf(__('Nuevo evento "%s" puedes verlo y apuntarte en '.$new_post_permalink, 'twitch-ranking'),$nombre), 'Twitch Ranking');
    
    wp_redirect($new_post_permalink);
}
?>
<script>
    function div_show() {
        var elements = document.getElementsByClassName('formcrearevento');
        for(var i = 0, length = elements.length; i < length; i++) {
            elements[i].style.display = 'table-cell';
        }
    }
    function div_hide(){
        var elements = document.getElementsByClassName('formcrearevento');
        for(var i = 0, length = elements.length; i < length; i++) {
            elements[i].style.display = 'none';
        }
    }
</script>
<div class="col-lg-12 mb-2">
    <div class="card">
        <div class="card-header">
            <?php _e('Eventos - Apúntate a los eventos o crea nuevos','twitch-ranking'); ?>
        </div>
        <div class="card-body text-center">
            <?php _e('Nosotros no te podemos obligar a participar pero lo suyo es que te apuntes a los eventos que crean otros y si no te interesa crea el tuyo para que otros participen y exista una mayor colaboración','twitch-ranking'); ?>
            <p><?php _e('Recuerda que los eventos los pueden ver todos los visitantes de la web','twitch-ranking'); ?></p>
        </div> 
    </div>
</div>
<table class="table text-center mt-2">
    <form action="" id="formevento" method="POST">
    <tr>
        <th colspan="2" class="formcrearevento text-center font-weight-bold" style="display:none;">
            <?php _e('Crear Evento','twitch-ranking'); ?>
        </th>
    </tr>
    <tr>
        <td colspan="2" class="formcrearevento" style="display:none;">
            <input type="hidden" name="accion" value="tr_crearevento"/>
            <label for="nombre"><?php _e('Nombre del evento','twitch-ranking'); ?></label><input class="form-control" type="text" name="nombre"/>
        </td>
    </tr>
    <tr>
        <td class="formcrearevento" style="display:none;">
            <label for="fechaini"><?php _e('Fecha Inicio: ','twitch-ranking'); ?></label>
            <p><input class="form-control" type="datetime-local" name="fechaini" required/></p>
        </td>
        <td class="formcrearevento" style="display:none;">
            <label for="fechafin"><?php _e('Fecha Fin: ','twitch-ranking'); ?></label>
            <p><input class="form-control" type="datetime-local" name="fechafin" required/></p>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="formcrearevento" style="display:none;">
            <label for="descripcion"><?php _e('Descripción','twitch-ranking'); ?> (<a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet" target="_blank"><?php _e('Permite Markdown','twitch-ranking'); ?></a>)</label><p><textarea name="descripcion" class="form-control" maxlength="255" form="formevento" required></textarea></p>
            <button class="btn btn-success" type="submit"><?php _e('Crear','twitch-ranking'); ?></button>
            <button class="btn btn-danger" type="button" onclick="div_hide()"><?php _e('Cancelar','twitch-ranking'); ?></button>
        </td>
    </tr>
    </form>
    <tr>
        <td class="tabla-centrada text-center"><b><?php _e('Próximos eventos','twitch-ranking'); ?></b></td>
        <td class="tabla-centrada text-center"><button type="button" class="btn btn-info" id="popup" onclick="div_show()"><?php _e('Crear Evento','twitch-ranking'); ?></button></td>
    </tr>
<?php
    $time = time();
    $existeeventos = false;
    
    foreach ($posteventos as $pe){
        $idevento = $pe->ID;
        $nombre = $pe->post_title;
        $descripcion = $pe->post_content;
        $fechainicio = get_post_meta($pe->ID,'tr_date_start',true);
        $fechafin = get_post_meta($pe->ID,'tr_date_end',true);
        $tr_inscribed_list = get_post_meta($pe->ID,'tr_inscribed_list',true);
        $creator_twitch_id = get_post_meta($pe->ID,'tr_creator_twitch_id',true);
        
        $dateini = new DateTime($fechainicio);
        $dateini=$dateini->format('Y-m-d H:i');
        
        $datefin = new DateTime($fechafin);
        $datefin=$datefin->format('Y-m-d H:i');
        
        $timefin = strtotime($datefin);
        
        $streamer_channel = "";
        if ($time < $timefin) :
            foreach ($postcanales as $p ){
                if (strcasecmp(get_post_meta($p->ID,'twitchID',true), $creator_twitch_id) == 0) {
                    $streamer_channel = get_post_meta($p->ID,'usuario',true);
                }
            }
            $existeeventos = true;
        ?>
            <tr>
                <td class="mx-auto border-bottom border-dark"><a href="<?php echo get_permalink($pe->ID); ?>"><b><?php echo $nombre;?></b></a></td>
                <td class="mx-auto border-bottom border-dark" rowspan="3" ><b><?php _e('Descripción:','twitch-ranking'); ?></b><br/><?php echo $descripcion;?></td>
            </tr>
            <tr>
                <td class="mx-auto"><b><?php _e('Fecha Inicio','twitch-ranking'); ?></b></td>
            </tr>
            <tr>
                <td class="mx-auto border-bottom border-dark"><?php echo $dateini;?></td>
            </tr>
            <tr>
                <td class="mx-auto"><b><?php _e('Fecha Fin','twitch-ranking'); ?></b></td>
                <td class="mx-auto border-bottom border-dark"></b><?php _e('Creador del evento: ','twitch-ranking'); ?></b><a target='_blank' href='https://www.twitch.tv/<?php echo $streamer_channel;?>'><?php echo $streamer_channel;?></a></td>
            </tr>
            <tr>
                <td class="mx-auto border-bottom border-danger"><?php echo $datefin;?></td>
                <td class="mx-auto border-bottom border-danger"><?php _e('Listado de apuntados: ','twitch-ranking');?>
                <?php
                    foreach ($postcanales as $p ):
                        if (in_array(get_post_meta($p->ID,'twitchID',true), $tr_inscribed_list)) :
                    ?>
                            <a target="_blank" href="https://www.twitch.tv/<?php echo get_post_meta($p->ID,'usuario',true);?>"><?php echo get_post_meta($p->ID,'usuario',true); ?></a> -
                <?php
                        endif;
                    endforeach;
                ?>
                </td>
            </tr>
            <tr class="mb-3">
                <td class="mx-auto border-bottom border-danger mb-1" colspan="2">
                <?php
                $estaapuntado = in_array($twitchID, $tr_inscribed_list);
                if (!$estaapuntado) :
                    ?>
                    <form method="POST" action="">
                        <input type="hidden" name="idsoli" value="<?php echo $idevento;?>"/>
                        <input type="hidden" name="streamervotante" value="<?php echo $twitchID; ?>"/>
                        <input type="hidden" name="accion" value="tr_apuntarseevento"/>
                        <button class="btn btn-primary" type="submit"><?php _e('Apuntarse','twitch-ranking'); ?></button>
                    </form>
                <?php else: ?>
                    <b><?php _e('Ya estas apuntad@','twitch-ranking'); ?></b>
                    <form method="POST" action="">
                        <input type="hidden" name="idsoli" value="<?php echo $idevento;?>"/>
                        <input type="hidden" name="streamervotante" value="<?php echo $twitchID; ?>"/>
                        <input type="hidden" name="accion" value="tr_desapuntarseevento"/>
                        <button class="btn btn-primary" type="submit"><?php _e('Desapuntarse','twitch-ranking'); ?></button>
                    </form>
                <?php endif; ?>
                </td>
            </tr>
        <?php endif;
    }
    if (!$existeeventos) :
        ?>
        <tr>
            <td class="mx-auto" colspan="2"><?php _e('No hay eventos próximos','twitch-ranking'); ?></td>
        </tr>
        <?php 
    endif;
?>
</table>