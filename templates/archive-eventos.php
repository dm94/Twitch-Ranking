<?php

/*
Template Name: Archive eventos
*/
?>
<?php
get_header();
?>
<div class="row">
	<div class="col-lg-12 mb-2">
		<div class="card border-success">
			<div class="card-header">
				<div class="huge"><?php _e('Próximos eventos','twitch-ranking'); ?></div>
			</div>
		</div>
	</div>
	<?php
		$posteventos = get_posts( array('post_type' => 'eventos','numberposts' => -1, 'no_found_rows' => true));
		$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
		$existeeventos = false;
		$time = strtotime(date('Y-m-d H:i'));
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
			$streamer_channel = 'dm94dani';
			if($time < $timefin){
				$streamer_channel = getTwitchUser($creator_twitch_id);
				$existeeventos = true;
		?>
				<div class="col-lg-6 mb-1">
					<div class="card">
						<div class="card-header text-left">
							<div class="huge"><?php echo $nombre; ?></div>
						</div>
						<div class="card-footer">
							<div class="float-left">
									<p><strong><?php _e('Creador del evento','twitch-ranking'); ?></strong></p>
									<p><strong><?php _e('Fecha de inicio','twitch-ranking'); ?></strong></p>
									<p><strong><?php _e('Fecha fin del evento','twitch-ranking'); ?></strong></p>
							</div>
							<div class="float-right">
									<p class="text-right"><a target='_blank' href='https://www.twitch.tv/<?php echo $streamer_channel;?>'><?php echo $streamer_channel; ?></a></p>
									<p class="text-right"><?PHP echo $dateini; ?></p>
									<p class="text-right"><?PHP echo $datefin; ?></p>
							</div>
							<div class="clearfix"></div>
								<a class="btn btn-danger btn-block" href='<?php echo get_permalink($idevento);?>'><?php _e('Ver el evento','twitch-ranking'); ?></a>
						</div> 
					</div>
				</div>
		<?php
			}
		}
		if (!$existeeventos) {
		?>
			<div class="col-lg-12">
				<div class="card border-warning">
					<div class="card-header">
						<div class="huge"><?php _e('No hay próximos eventos todavía','twitch-ranking'); ?></div>
					</div> 
				</div>
			</div>
		<?php
		}
	?>
	</div>
<?php
get_footer();
?>