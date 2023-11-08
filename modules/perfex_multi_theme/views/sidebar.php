<?php
defined('BASEPATH') or exit('No direct script access allowed');
$staff_id = get_staff_user_id();
include 'style.php';
?>
<button type="button" class="show color_confi_btn"><i class="fa fa-cog" aria-hidden="true"></i></button>
<div class="slideout_color_Confi multi_theme_settings">
	<button type="button" class="hide_close_btn con_close_btn">&times;</button>
	<h1>Multi Theme</h1>
	<!--	<p>THEME BASE</p>-->
	<ul class="list-unstyled">
		<?php
		$current_theme = current_theme_applied();
		$CI = &get_instance();
		$css_dir = 'old';
		$current_version = $CI->app->get_current_db_version();
		if ($current_version > 294) {
			$css_dir = 'new';
		}
		?>
		<li>
			<h3 style="background: linear-gradient(to bottom right, #4f6f91 0%, #5072a7 100%);" id="default_mode"><span><?= !$current_theme ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Default Color
			</h3>
		</li>

		<li>
			<h3 style="background-color: #131c28" id="dark_mode"><span><?= $current_theme && $current_theme == 'dark' ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Dark mode</h3>
		</li>

		<li>
			<h3 style="background-color: #ffffff; color: black" id="light_mode"><span><?= $current_theme && $current_theme == 'light' ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Light Mode </h3>
		</li>

		<li>
			<h3 style="background-color: #f5561e" id="orange_mode"><span><?= $current_theme && $current_theme == 'orange' ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Orange</h3>
		</li>
		<li>
			<h3 style="background: linear-gradient(#392ab7, #2861b7)" id="purple_mode"><span><?= $current_theme && $current_theme == 'purple' ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Purple</h3>
		</li>
		<li>
			<h3 style="background: linear-gradient(to right, #30bc22, #28b01d, #20a418, #189813, #0e8c0e);" id="green_mode"><span><?= $current_theme && $current_theme == 'green' ? '<i class="fa fa-check-square-o" aria-hidden="true"></i>' : '' ?> </span>Green</h3>
		</li>

	</ul>
</div>

<script>
	var current_theme = "<?= $current_theme; ?>";
	var css_dir = "<?= $css_dir; ?>";

	$(document).ready(function() {
		if (current_theme != 'dark') {
			$("body").addClass("active_theme");
		}
	})

	$('.show').click(function() {
		$('.slideout_color_Confi').addClass('on');
		$('body').css('overflow', 'hidden');
	});
	$('.hide_close_btn').click(function() {
		$('.slideout_color_Confi').removeClass('on');
		$('body').css('overflow', '');
	});
	$('#dark_mode').click(function() {
		$("body").removeClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: "dark",
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#theme_styles_color').remove();
			$('#light_styles_color').remove();
			$('#purple_styles_color').remove();
			$('#orange_styles_color').remove();
			$('#green_styles_color').remove();
			$('head').append('<link id="dark_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/dark-css/dark_styles.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		});
	});
	$('#light_mode').click(function() {
		$("body").addClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: "light",
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#dark_styles_color').remove();
			$('#theme_styles_color').remove();
			$('#purple_styles_color').remove();
			$('#orange_styles_color').remove();
			$('#green_styles_color').remove();
			$('head').append('<link id="light_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/light-css/light_styles.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		});
	});

	$('#default_mode').click(function() {
		$("body").addClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: null,
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#dark_styles_color').remove();
			$('#purple_styles_color').remove();
			$('#orange_styles_color').remove();
			$('#light_styles_color').remove();
			$('#green_styles_color').remove();
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		})
	});

	$('#orange_mode').click(function() {
		$("body").addClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: "orange",
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#theme_styles_color').remove();
			$('#dark_styles_color').remove();
			$('#light_styles_color').remove();
			$('#green_styles_color').remove();
			$('head').append('<link id="orange_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/orange-css/orange_styles.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		})
	});

	$('#purple_mode').click(function() {
		$("body").addClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: "purple",
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#theme_styles_color').remove();
			$('#dark_styles_color').remove();
			$('#orange_styles_color').remove();
			$('#light_styles_color').remove();
			$('#green_styles_color').remove();
			$('head').append('<link id="purple_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/purple-css/purple_styles.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		})

	});
	$('#green_mode').click(function() {
		$("body").addClass("active_theme");
		$('.multi_theme_settings .fa-check-square-o').remove();
		$(this).find('span').html('<i class="fa fa-check-square-o" aria-hidden="true"></i> ');
		$.get(admin_url + 'perfex_multi_theme/main/update_color', {
			theme_css: "green",
			staff_id: <?php echo "$staff_id" ?>
		}, function(resp) {
			$('#theme_styles_color').remove();
			$('#dark_styles_color').remove();
			$('#orange_styles_color').remove();
			$('#light_styles_color').remove();
			$('#purple_styles_color').remove();
			$('head').append('<link id="green_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/green-css/green_styles.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
			$('head').append('<link id="sidebar_styles_color" href="<?= base_url('modules/perfex_multi_theme/assets/'.$css_dir.'/sidebar.css?v=' . time()); ?>" rel="stylesheet" type="text/css">');
		})
	});
</script>