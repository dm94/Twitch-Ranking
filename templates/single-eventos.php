<?php

/*
Template Name: Evento
Template Post Type: eventos
*/

get_header();

?>
<?php
	$creator_twitch_id = get_post_meta(get_the_ID(),'tr_creator_twitch_id',true);
	$tr_date_start = get_post_meta(get_the_ID(),'tr_date_start',true);
	$tr_date_end = get_post_meta(get_the_ID(),'tr_date_end',true);
	$tr_inscribed_list = get_post_meta(get_the_ID(),'tr_inscribed_list',true);
	$postcanales = get_posts( array('post_type' => 'canales','numberposts' => -1, 'no_found_rows' => true));
	$dateini = new DateTime($tr_date_start);
	$dateini=$dateini->format('Y-m-d H:i');
	$datefin = new DateTime($tr_date_end);
	$datefin = $datefin->format('Y-m-d H:i');
	
	$streamer_channel = '';
	
	foreach ($postcanales as $p ){
		if (strcasecmp(get_post_meta($p->ID,'twitchID',true), $creator_twitch_id) == 0) {
			$streamer_channel = get_post_meta($p->ID,'usuario',true);
		}
	}
?>
<div id="main-content" class="page-card">

			<?php while ( have_posts()) : the_post(); ?>
			
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-content row">
					<div class="col-lg-12 mb-2">
						<div class="row">
							<div class="col-lg-8">
								<div class="card border border-info">
									<div class="card-body">
										<?php the_content(); ?>
									</div> 
								</div>
							</div>
							<div class="col-lg-4">
								<div class="card">
									<div class="card-header">
										<div class="huge"><?php _e('Datos del evento','twitch-ranking'); ?></div>
									</div>
									<div class="card-body">
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
										<a class="btn btn-block btn-danger mx-auto text-center" target='_blank' href='https://www.twitch.tv/<?php echo $streamer_channel;?>'><?php _e('Ir a su canal','twitch-ranking'); ?></a>
									</div> 
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-12">
						<div class="row">
							<div class="col-lg-8">
								<iframe id="streamning" src="https://player.twitch.tv/?channel=<?php echo $streamer_channel;?>&parent=<?php echo $_SERVER['HTTP_HOST'];?>" frameborder="0" allowfullscreen="true" scrolling="no" height="378" width="100%"></iframe>
							</div>
							<div class="col-lg-4">
								<iframe src="https://www.twitch.tv/embed/<?php echo $streamer_channel;?>/chat?parent=<?php echo $_SERVER['HTTP_HOST'];?>" id="chat" frameborder="0" scrolling="no" height="378" width="100%"></iframe>
							</div>
						</div>
					</div>
					<div class="col-lg-12">
						<div class="card">
							<div class="card-header">
								<div class="huge"><?php _e('Streamers apuntados','twitch-ranking'); ?></div>
							</div>
							<div class="card-body">
								<?php
									if (!empty($tr_inscribed_list)) :
										foreach ($postcanales as $p ){
											if (in_array(get_post_meta($p->ID,'twitchID',true), $tr_inscribed_list)) {
											?>
												<a target="_blank" href="https://www.twitch.tv/<?php echo get_post_meta($p->ID,'usuario',true);?>"><?php echo get_post_meta($p->ID,'usuario',true);?></a> 
											<?php
											}
										}
									endif;
								?>
							</div> 
						</div>
					</div>
				</div> <!-- .entry-content -->
				</article> <!-- .et_pb_post -->
			<?php endwhile; ?>

</div> <!-- #main-content -->
<?php 
	if ( comments_open() || get_comments_number()) {
		comments_template();
	}
?>
<?php get_footer(); ?>