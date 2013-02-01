<?php
/**
 * Home page for logged in system users.
 *
 * @package		ProjectSend
 *
 */
$allowed_levels = array(9,8,7);
require_once('sys.includes.php');
$page_title = __('Welcome to ProjectSend', 'cftp_admin');

$flot = 1;
include('header.php');
$database->MySQLDB();
?>

<div id="main">
	<h2><?php echo $page_title; ?></h2>

	<div class="home">
		<div class="container-fluid">
			<div class="row-fluid">
			<?php
				$log_allowed = array(9);
				if (in_session_or_cookies($log_allowed)) {
					$show_log = true;
				}
			?>
					<div class="span8 <?php if ($show_log != true) { echo 'offset2'; } ?>">
						<div class="row-fluid">
							<div class="span12">
								<div class="widget">
									<h4><?php _e('Statistics for the last 15 days','cftp_admin'); ?></h4>
									<div class="widget_int">
										<div id="statistics" style="height:320px;width:100%;"></div>
										<ul class="graph_legend">
											<li><div class="legend_color legend_color1"></div><?php _e('Uploads','cftp_admin'); ?></li><li>
											<div class="legend_color legend_color2"></div><?php _e('Downloads','cftp_admin'); ?></li><li>
											<div class="legend_color legend_color3"></div><?php _e('Zip Downloads','cftp_admin'); ?></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="span6">
								<?php include(ROOT_DIR.'/home-news-widget.php'); ?>
							</div>
							<div class="span6">
								<div class="widget">
									<h4><?php _e('System data (relative view)','cftp_admin'); ?></h4>
									<div class="widget_int">
										<div id="sys_info" style="height:335px;width:100%; "></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<?php if (isset($show_log) && $show_log == true) { ?>
						<div class="span4">
							<div class="widget">
								<h4><?php _e('Recent activites','cftp_admin'); ?></h4>
								<div class="widget_int">
									<ul class="activities_log">
										<?php
											$sql_log = $database->query("SELECT * FROM tbl_actions_log ORDER BY id DESC LIMIT 13");
											$log_count = mysql_num_rows($sql_log);
											if ($log_count > 0) {
												while($log = mysql_fetch_array($sql_log)) {
													$rendered = render_log_action(
																		array(
																			'action' => $log['action'],
																			'timestamp' => $log['timestamp'],
																			'owner_id' => $log['owner_id'],
																			'owner_user' => $log['owner_user'],
																			'affected_file' => $log['affected_file'],
																			'affected_file_name' => $log['affected_file_name'],
																			'affected_account' => $log['affected_account'],
																			'affected_account_name' => $log['affected_account_name']
																		)
													);
												?>
													<li>
														<div class="log_ico">
															<img src="img/log_icons/<?php echo $rendered['icon']; ?>.png" alt="Action icon">
														</div>
														<div class="home_log_text">
															<div class="date"><?php echo $rendered['timestamp']; ?></div>
															<div class="action">
																<?php
																	if (!empty($rendered['1'])) { echo '<span>'.$rendered['1'].'</span> '; }
																	echo $rendered['text'].' ';
																	if (!empty($rendered['2'])) { echo '<span>'.$rendered['2'].'</span> '; }
																	if (!empty($rendered['3'])) { echo ' '.$rendered['3'].' '; }
																	if (!empty($rendered['4'])) { echo '<span>'.$rendered['4'].'</span> '; }
																?>
															</div>
														</div>
													</li>
												<?php
												}
											}
										?>
									</ul>
									<div class="view_full_log">
										<a href="actions-log.php" class="button button_blue"><?php _e('View all','cftp_admin'); ?></a>
									</div>
								</div>
							</div>
						</div>
				<?php
					}
				?>
			</div>
		</div>
	</div>
	
</div>

<?php
	/** Get the data to show on the bars graphic */
	$sql = $database->query("SELECT distinct id FROM tbl_files");
	$total_files = mysql_num_rows($sql);

	$sql = $database->query("SELECT distinct user FROM tbl_users WHERE level='0'");
	$total_clients = mysql_num_rows($sql);

	$sql = $database->query("SELECT distinct id FROM tbl_groups");
	$total_groups = mysql_num_rows($sql);

	$sql = $database->query("SELECT distinct user FROM tbl_users WHERE level != '0'");
	$total_users = mysql_num_rows($sql);
?>
<script type="text/javascript">
	$(document).ready(function(){
		$.plot(
			$("#sys_info"), [{
				data: [
					[1, <?php echo $total_files; ?>],
					[2, <?php echo $total_clients; ?>],
					[3, <?php echo $total_groups; ?>],
					[4, <?php echo $total_users; ?>]
				]
			}
			], {
				series:{
					bars:{show: true},
				},
				bars:{
					  barWidth:.5,
					  align: 'center',
				},
				legend: {
					show: true
				},
				grid:{
					hoverable: true,
					borderWidth: 0,
					backgroundColor: {
						colors: ["#fff", "#f9f9f9"]
					}
				},
				xaxis: {
					ticks: [
						[1, '<?php _e('Files','cftp_admin'); ?>: <?php echo $total_files; ?>'],
						[2, '<?php _e('Clients','cftp_admin'); ?>: <?php echo $total_clients; ?>'],
						[3, '<?php _e('Groups','cftp_admin'); ?>: <?php echo $total_groups; ?>'],
						[4, '<?php _e('Users','cftp_admin'); ?>: <?php echo $total_users; ?>']
					]
				},
				yaxis: {
					min: 0,
					tickDecimals:0
				}
			}
		);

		// statistics
		<?php include(ROOT_DIR.'/home-statistics.php'); ?>

		function showTooltip(x, y, contents) {
			$('<div id="stats_tooltip">' + contents + '</div>').css( {
				top: y + 5,
				left: x + 5,
			}).appendTo("body").fadeIn(200);
		}
		
		var previousPoint = null;
		$("#statistics").bind("plothover", function (event, pos, item) {
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));
		
				if (item) {
					if (previousPoint != item.dataIndex) {
						previousPoint = item.dataIndex;
						
						$("#stats_tooltip").remove();
						var x = item.datapoint[0].toFixed(2),
							y = item.datapoint[1].toFixed(2);

						showTooltip(item.pageX, item.pageY,
									item.series.label + ": " + y);
					}
				}
				else {
					$("#stats_tooltip").remove();
					previousPoint = null;            
				}
		});	

		var options = {
			grid: {
				hoverable: true,
				borderWidth: 0,
				color: "#666",
				labelMargin: 10,
				axisMargin: 0,
				mouseActiveRadius: 10
			},
			series: {
				lines: {
					show: true,
					lineWidth: 2
				},
				points: {
					show: true,
					radius: 3,
					symbol: "circle",
					fill: true
				}
			},
			xaxis: {
				mode: "time",
				minTickSize: [1, "day"],
				timeformat: "%d/%m",
				labelWidth: "30"
			},
			yaxis: {
				min: 0,
				tickDecimals:0
			},
			legend: {
				margin: 10,
				sorted: true,
				show: false
			},
			colors: ["#0094bb","#86ae00","#f2b705"]
		};

		$.plot(
			$("#statistics"), [
				{
					data: d5,
					label: '<?php _e('Uploads','cftp_admin'); ?>'
				},
				{
					data: d8,
					label: '<?php _e('Downloads','cftp_admin'); ?>'
				},
				{
					data: d9,
					label: '<?php _e('Zip Downloads','cftp_admin'); ?>'
				}
			], options
		);

	});
</script>

<?php
$database->Close();
include('footer.php');
?>