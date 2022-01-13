<?php // $Id: lib_accredit.php,v 1.6 2010/03/02 13:39:34 Shtifanov Exp $


function print_tabs_years_link_accredit($type_ou, $rid, $sid, $udodid, $douid, $yid, $ataba = '')
{
	global $CFG;
		
	switch($type_ou)	{
		case 0: $table ='monit_school';
				$id = $sid;	
		break;
		case 3: $table ='monit_udod';;
				$id = $udodid;
		break;
		case 1: $table ='monit_education';;
				$id = $douid;
		break;
	}
		
	$toprow1 = array();

	$uniqueconstcode = 0;
   	if ($rid != 0 && $id != 0)	{
   		if ($school = get_record_select($table, "rayonid = $rid AND id = $id AND yearid = $yid", 'id, uniqueconstcode'))		{
			$uniqueconstcode = $school->uniqueconstcode;   			
   		}
   	} 

    if ($years = get_records('monit_years'))  {
    	foreach ($years as $year)	{
    		if ($year->id < 3) continue;
    		// $link = "&amp;rid=$rid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;yid=" . $year->id;
	    	if ($uniqueconstcode != 0)	{
				if ($edu = get_record_select($table, "uniqueconstcode=$uniqueconstcode AND yearid = {$year->id}", 'id, rayonid'))	{
						switch($type_ou)	{
							case 0: $link = "&amp;type_ou=$type_ou&amp;rid={$edu->rayonid}&amp;sid={$edu->id}&amp;udodid=0&amp;douid=0&amp;yid={$year->id}";
							break;
							case 3: $link = "&amp;type_ou=$type_ou&amp;rid={$edu->rayonid}&amp;sid=0&amp;udodid={$edu->id}&amp;douid=0&amp;yid={$year->id}";
							break;
							case 1: $link = "&amp;type_ou=$type_ou&amp;rid={$edu->rayonid}&amp;sid=0&amp;udodid=0&amp;douid={$edu->id}&amp;yid={$year->id}";
							break;
						}

					
				}	
	    	}
	    	$fulllink =  "$CFG->wwwroot/blocks/mou_accredit/accredit/accredit.php?" . $link;
	    	/*
	    	switch ($year->id)	{
	    		case 2: $fulllink =  "$CFG->wwwroot/blocks/mou_accredit/school/accreditation.php?" . $link;
	    		break;
	    		case 3: $fulllink =  "$CFG->wwwroot/blocks/mou_accredit/accredit/accredit.php?" . $link;
	    		break;
	    		
	    	}
	    	*/
 	        $toprow1[] = new tabobject($year->id, $fulllink, get_string('uchyear', 'block_monitoring', $year->name));
	    }
  	}
    $tabs1 = array($toprow1);

   //  print_heading(get_string('terms','block_dean'), 'center', 4);
	print_tabs($tabs1, $yid, NULL, NULL);
}


function get_next_id_bycircle ($table, $select, $id, $bycircle = '+') // may by '-'
{
	  $recs =  get_records_select($table, $select, 'num');
	  $ids = array();
	  foreach ($recs as $rec)	{
	  		$ids[] = $rec->id;
	  }
	  $first = current($ids);
	  $last = end($ids);

	  // echo $firstcriteria; echo '<hr>'; echo $lastcriteria; echo '<hr>'; echo $cid; 	echo '<hr>';
	  $i = array_search ($id, $ids);
	  if ($i === false)	{
	  		$id = 0;
	  } else {
	      if ($bycircle == '-')		{
	   			if ($id == $first) {
      				$id = $last;
      			}
		        else {
		        	$id = $ids[$i-1];
		        }
		  } else if ($bycircle == '+')		{
		  		if ($id == $last) {
		  			$id = $first;
		  		}
		        else {
		        	$id = $ids[$i+1];
		        }
		  }
	  }

	  return $id;
}


function get_max_estimate($yid, $type_ou) // , $id)
{
	global $CFG;

	$criterions =  get_records_select('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou");
	$maxsum = 0;
	if ($criterions) foreach ($criterions as $criteria) {
			$indicators =  get_records('monit_accr_indicator', 'criteriaid', $criteria->id, 'num');
			$sum = 0;
			foreach ($indicators as $indicator) {					
	
				$strsql = "SELECT Max(ae.mark) AS max
						    FROM {$CFG->prefix}monit_accr_estimates ae INNER JOIN {$CFG->prefix}monit_accr_indicator ai ON ai.id = ae.indicatorid
						    WHERE ae.indicatorid = {$indicator->id}";
			
			    if ($amax = get_record_sql($strsql))	 {
					$sum += $amax->max;
				}
				// print_r($amax); echo '<hr>';
			}
			// echo 'sum='.$sum;
			$maxsum += $sum;
	}			 
	// echo '<hr>'.$maxsum; 
	return $maxsum;
}


function listbox_ou($scriptname, $type_ou)
{
  $oumenu = array();
  $oumenu[0] = get_string('school', 'block_monitoring');
  $oumenu[1] = get_string('dous', 'block_mou_att');
  $oumenu[3] = get_string('udod', 'block_monitoring');
  
  echo '<tr><td>'.get_string('typereport', 'block_mou_att').':</td><td>';
  popup_form($scriptname, $oumenu, 'switchlevel', $type_ou, '', '', '', false);
  echo '</td></tr>';
  return 1;
}


function check_optional_param($type_ou, $rid, $sid, $udodid, $douid)	
{
	global  $USER;
	
	if ($rid == 0 ||  ($sid == 0 && $udodid == 0 && $douid == 0)) {
		// add_to_log(1, 'lib_accredit.php', 'all zero', 'check_optional_param', fullname($USER), '', $USER->id);
		return false;
	}
	
	switch($type_ou)	{
		case 0:	if ($sid == 0)	{
					add_to_log(1, 'lib_accredit.php', 'Unknown school', 'check_optional_param', fullname($USER), '', $USER->id);			
					error('Unknown school.');
				}
		break;
		case 3: if ($udodid == 0)	{
					add_to_log(1, 'lib_accredit.php', 'Unknown UDOD', 'check_optional_param', fullname($USER), '', $USER->id);			
					error('Unknown UDOD.');
				}
		break;
		case 1: if ($douid == 0)	{
					add_to_log(1, 'lib_accredit.php', 'Unknown DOU', 'check_optional_param', fullname($USER), '', $USER->id);			
					error('Unknown DOU.');
				}
		break;
		default:
				add_to_log(1, 'lib_accredit.php', 'Unknown type education', 'check_optional_param', fullname($USER), '', $USER->id);		
				error('Unknown type education.');
	}
	
	return true;
	
}


function slovo_ballov($count)
{
	switch ($count%10) {
		case '1': return 'балл'; 
		break;
		
		case '2': 
		case '3': 
		case '4': return 'балла'; 
		break;
		
		case '0': 		
		case '5': 
		case '6': 
		case '7': 
		case '8': 
		case '9': return 'баллов'; 
		break;
	}
}


?>
