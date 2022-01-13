<?php // $Id: authall.inc.php,v 1.4 2009/12/16 10:46:51 Shtifanov Exp $

	require_login();

	switch($type_ou)	{
		case 0:	$admin_is = isadmin();
				$region_operator_is = ismonitoperator('staff');
				$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
				$dod_rayon_operator_is  = false; 
				$school_operator_is = ismonitoperator('school', 0, $rid, $sid);
				if (!$admin_is && !$region_operator_is && !$rayon_operator_is && !$school_operator_is) {
		            add_to_log(1, 'authall.inc.php', 'school', 'staffaccess', fullname($USER), '', $USER->id);
			        error(get_string('staffaccess', 'block_mou_att'));
				}

				if (!$admin_is && !$region_operator_is && !$rayon_operator_is && $school_operator_is)  {
				      if ($school = get_record_sql ("SELECT id, uniqueconstcode FROM {$CFG->prefix}monit_school
				                                     WHERE rayonid=$rid AND uniqueconstcode=$sid AND yearid=$yid"))	{
				     		$sid = $school->id;
				     		$school2 = get_record('monit_school', 'id', $sid);
				     		if ($school2->rayonid != $rid) {
           						add_to_log(1, 'authall.inc.php', 'school', 'selectownrayon', fullname($USER), '', $USER->id);
				     			error(get_string('selectownrayon', 'block_monitoring'));
				     		}
				      }
				}
				
				if ($sid != 0) {
					$school = get_record('monit_school', 'id', $sid);
				}	
		break;
		case 1: $admin_is = isadmin();
				$region_operator_is = ismonitoperator('staff');
				$dod_rayon_operator_is  = ismonitoperator('dod_rayon', 0, 0, 0, true);
				$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
				// $rayon_operator_is = $dod_rayon_operator_is;
				$dou_operator_is = ismonitoperator('dou', 0, $rid, $douid);
				if (!$admin_is && !$region_operator_is && !$rayon_operator_is  && !$dod_rayon_operator_is && !$dou_operator_is) {
					add_to_log(1, 'authall.inc.php', 'dou', 'staffaccess', fullname($USER), '', $USER->id);
			        error(get_string('staffaccess', 'block_mou_att'));
				}

				if (!$admin_is && !$region_operator_is && !$dod_rayon_operator_is && $dou_operator_is)  {
				      if ($dou = get_record_sql ("SELECT id, uniqueconstcode FROM {$CFG->prefix}monit_education
				                                   WHERE rayonid=$rid AND uniqueconstcode=$douid AND yearid=$yid"))	{
				     		$douid = $dou->id;
				     		$dou2 = get_record('monit_education', 'id', $douid);
				     		if ($dou2->rayonid != $rid) {
								add_to_log(1, 'authall.inc.php', 'dou', 'selectownrayon', fullname($USER), '', $USER->id);
				     			error(get_string('selectownrayon', 'block_monitoring'));
				     		}
				     		
				      }
				}
				if ($douid != 0) {
					$dou = get_record('monit_education', 'id', $douid);
				}	
				
		break;
		
		case 3: $admin_is = isadmin();
				$region_operator_is = ismonitoperator('staff');
				$dod_rayon_operator_is  = ismonitoperator('dod_rayon', 0, 0, 0, true);
				$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
				// $rayon_operator_is = $dod_rayon_operator_is;
				$dod_school_operator_is = ismonitoperator('dod_school', 0, $rid, $udodid);
				if (!$admin_is && !$region_operator_is && !$rayon_operator_is  && !$dod_rayon_operator_is && !$dod_school_operator_is) {
					add_to_log(1, 'authall.inc.php', 'udod', 'staffaccess', fullname($USER), '', $USER->id);
			        error(get_string('staffaccess', 'block_mou_att'));
				}

				if (!$admin_is && !$region_operator_is && !$dod_rayon_operator_is && $dod_school_operator_is)  {
				      if ($udod = get_record_sql ("SELECT id, uniqueconstcode FROM {$CFG->prefix}monit_udod
				                                   WHERE rayonid=$rid AND uniqueconstcode=$udodid AND yearid=$yid"))	{
				     		$udodid = $udod->id;
				     		$udod2 = get_record('monit_udod', 'id', $udodid);
				     		if ($udod2->rayonid != $rid) {
								add_to_log(1, 'authall.inc.php', 'udod', 'selectownrayon', fullname($USER), '', $USER->id);
				     			error(get_string('selectownrayon', 'block_monitoring'));
				     		}
				     		
				      }
				}
				if ($udodid != 0) {
					$udod = get_record('monit_udod', 'id', $udodid);
				}	
				
		break;
		default:
				add_to_log(1, 'authall.inc.php', 'Unknown type education', 'selectownrayon', fullname($USER), '', $USER->id);		
				error('Unknown type education.');
	}

	if ((!$admin_is && !$region_operator_is && $rayon_operator_is && $rayon_operator_is != $rid) ||  
		($sid !=0 && $school->rayonid != $rid) || 
		($udodid !=0 && $udod->rayonid != $rid) || 
		($douid != 0 && $dou->rayonid != $rid))  {
			add_to_log(1, 'authall.inc.php', 'nobody_1', 'selectownrayon', fullname($USER), '', $USER->id);
			error(get_string('selectownrayon', 'block_monitoring'));
			exit();
	}

	if (!$admin_is && !$region_operator_is && $dod_rayon_operator_is && $dod_rayon_operator_is != $rid || 
		($udodid !=0 && $udod->rayonid != $rid) || 
		($douid != 0 && $dou->rayonid != $rid))  {
		add_to_log(1, 'authall.inc.php', 'nobody_2', 'selectownrayon', fullname($USER), '', $USER->id);
		error(get_string('selectownrayon', 'block_monitoring'));
		exit();
	}

	$staffview_operator = isstaffviewoperator();
	$staffview_rayon_operator = israyonviewoperator();

?>