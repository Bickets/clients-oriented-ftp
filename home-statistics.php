<?php
	$max_stats_days = 15;

	$month = date("m");
	$day = date("d");
	$year = date("Y");
	for($i=0; $i<=$max_stats_days-1; $i++){
		$gen_30_days[] = date(TIMEFORMAT_USE,mktime(0,0,0,$month,($day-$i),$year));
	}
	$last_30_days = array_reverse($gen_30_days);

	/**
	 * The graph will show only this actions
	 */
	$results = $database->query("SELECT action, timestamp, COUNT(*) as total
									FROM tbl_actions_log
									WHERE timestamp >= DATE_SUB( CURDATE(),INTERVAL $max_stats_days DAY)
									AND action IN ('5', '8', '9')
									GROUP BY DATE(timestamp), action
								");

	while($res = mysql_fetch_array($results)) {
		$res['timestamp'] = strtotime($res['timestamp']);
		switch ($res['action']) {
			case 5:
				$actions_to_graph['d5'][$res['timestamp']] = $res['total'];
				break;
			case 8:
				$actions_to_graph['d8'][$res['timestamp']] = $res['total'];
				break;
			case 9:
				$actions_to_graph['d9'][$res['timestamp']] = $res['total'];
				break;
		}
	}
	
	$data_logs = array('d5','d8','d9');
	foreach ($data_logs as $gen_log) {
		echo 'var '.$gen_log.' = [';
		$i = 0;
		foreach ($last_30_days as $day) {
			$wrote = false;
			$day_timestamp = str_replace('/', '-',$day);
			$final_timestamp = strtotime($day_timestamp)*1000;
			echo "[".$final_timestamp.",";
			foreach ($actions_to_graph as $action_number => $when) {
				if ($action_number == $gen_log) {
					foreach ($when as $log_day => $total) {
						if (date(TIMEFORMAT_USE,$log_day) == $day) {
							echo $total;
							$wrote = true;
						}
						else {
						}
					}
				}
			}
			if (!$wrote) {
				echo '0';
				//echo rand(0,180);
			}
			echo ']';
			$i++;
			if ($i < $max_stats_days) {
				echo ',';
			}
		}
		echo "];\n";
	}
?>