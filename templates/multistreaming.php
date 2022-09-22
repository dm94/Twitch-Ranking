<?php

/*
Template Name: MultiStreaming Twitch/Mixer
*/

get_header();

$twitch = addslashes(isset($_GET['twitch']) ? $_GET['twitch'] : '');
?>
	<div class="row">
		<?php
		$streamersTwitch = explode(",", $twitch,3);
		foreach ($streamersTwitch as $streamerTwitch) :
			if (!empty($streamerTwitch)) :
		?>
		<div class="col-lg-6 m-0 p-0">
			<?php
				echo '<iframe id="streamning" src="https://player.twitch.tv/?channel='.$streamerTwitch.'&parent='.$_SERVER['HTTP_HOST'].'" frameborder="0" allowfullscreen="true" scrolling="no" height="540" width="960"></iframe>';
			?>
		</div>
	<?php
			endif;
		endforeach;
		?>
		<div class="col-lg-12">
		</div>
		<?php
		foreach ($streamersTwitch as $streamerTwitch) :
			if (!empty($streamerTwitch)) :
	?>
		<div class="col-lg-6">
			<?php 
				echo '<iframe src="https://www.twitch.tv/embed/'.$streamerTwitch.'/chat?parent='.$_SERVER['HTTP_HOST'].'" id="chat" frameborder="0" scrolling="no" height="378" width="100%"></iframe>';
			?>
		</div>
	<?php
			endif;
		endforeach;
	?>
		<div class="col-lg-12">
		</div>
		<div class="col-lg-3">
			<div class="card">
				<div class="card-header text-left">
					<?php _e('Añadir Streamer Twitch','twitch-ranking'); ?>
				</div>
				<div class="card-body text-center">
					<p class="mx-auto"><?php _e('Separa los streamers que quieras añadir con ",". Max: 3','twitch-ranking'); ?></p>
					<p class="mx-auto"><?php _e('Ejemplo: ','twitch-ranking'); ?>: dm94dani,gzonecomunidad</p>
					<form method="get" action="">
						<input type="text" name="twitch" value=""/></input>
						<button class="btn btn-primary" type="submit"><?php _e('Ver','twitch-ranking');?></button>
					</form>
				</div>
			</div>
		</div>
	</div>
<?php get_footer(); ?>