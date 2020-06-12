<?php
use MZ_MBO_Access\Core as Core;
use MZ_Mindbody as MZ;
?>
<div id="mzAccessContainer">

<?php 
if ( false == $data->logged_in ):
	
	include 'login_form.php'; 
	
else:
	?>
	<p class="mbo-user">Hi, <?php echo $data->client_name; ?>.</p>
	<?php
	if ((!empty($data->atts['level_1_redirect']) || !empty($data->atts['level_2_redirect']) )) {
		// this is being used as a redirect login form so just echo content if it exists
		echo $data->content; 
		
		?>
		<div class="row" style="margin:.5em;">
			<span class="btn btn-primary btn-xs" id="MBOLogout" target="_blank"><?php echo $data->logout; ?></span>
		</div>
		<?php
	} else {
	
		if ( $data->access ) { ?>
			<div class="alert alert-warning">
				<?php echo '<strong>' . $data->atts['denied_message'] .  '</strong>:'; ?>
				<ul>
					<?php foreach ($data->access_levels as $level){
						foreach ($data->required_services[$level] as $service) {
							echo '<li>' . $service . '</li>';
						}
					}
					?>
				</ul>
			</div>
		<?php
		} else {
			echo $data->content; 
		}
	?>

			<div class="row" style="margin:.5em;">

				<div class="col-12">
				
					<?php if (!empty($data->manage_on_mbo)): ?>
					<?php var_dump($data->manage_on_mbo); ?>

					<a style="text-decoration:none;" href="https://clients.mindbodyonline.com/ws.asp?&amp;sLoc=1&studioid=<?php echo $data->siteID; ?>" class="btn btn-primary btn-xs" id="MBOSite" target="_blank"><?php echo $data->manage_on_mbo; ?></a>
					
					<?php endif; ?>
					
					<span class="btn btn-primary btn-xs" id="MBOLogout" target="_blank"><?php echo $data->logout; ?></span>

				</div>
			
			</div>
	<?php } // End not a redirect login form ?>
<?php endif; ?>

</div>
<div style="display:none">
<?php include 'login_form.php'; // for use in logout routine ?>
</div>