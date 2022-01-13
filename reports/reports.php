<?php // $Id: reports.php,v 1.5 2009/12/08 09:14:30 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_accredit.php');

    $rid = optional_param('rid', 0, PARAM_INT);   // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);	// School id    
	$udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $ataba = optional_param('ataba', 'acr');
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
   	$action = optional_param('action', '');       // action
	$mid = optional_param('mid', '0', PARAM_INT);       // Remark id   	
	//$oid = optional_param('oid', '0', PARAM_INT);		// OU id

    $curryearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryearid;
    }

    require_once('../authall.inc.php');

    $straccreditation = get_string('title_accredit', 'block_mou_att');
    $strtitle = get_string('reportschool', 'block_monitoring');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if ($admin_is  || $region_operator_is )	 	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_rayons("reports.php?ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=", $rid);
		listbox_ou("reports.php?ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=$rid&amp;type_ou=", $type_ou);
		echo '</table>';
	} else if ($rayon_operator_is || $dod_rayon_operator_is) 		{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_ou("reports.php?ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=$rid&amp;type_ou=", $type_ou);
		echo '</table>';
	}
	
	if ($rid == 0) {
	    print_footer();
 		exit();
	}

	if ($rayon_operator_is && $rayon_operator_is != $rid)  {
		notify(get_string('selectownrayon', 'block_monitoring'));
	    print_footer();
		exit();
	}

	print_tabs_years_link("reports.php?ataba=$ataba&amp;type_ou=$type_ou", $rid, $sid, $yid);


 	    $numberf = get_string('symbolnumber', 'block_monitoring');
	    $strname = get_string('school', 'block_monitoring');
   	    $strtotal = get_string("total");

   	    $table->head  = array(); 	$table->align = array ();
        $table->head[] = $numberf; 	$table->align[] = 'center';
        $table->head[] = $strname; 	$table->align[] = 'left';

		$criterions =  get_records_select('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou");
	    if(!empty($criterions)) {
  	          foreach ($criterions as $criteria) {
			  	    $table->head[] = $criteria->num;
			  	    $table->align[] = 'center';
			  }
		}
		$table->head[] = $strtotal;
		$table->align[] = 'center';
	    $table->class = 'moutable';

		$maxsum = get_max_estimate($yid, $type_ou);
		 
		switch($type_ou)	{
			
			case 0:	$arr_schools =  get_records_sql("SELECT *  FROM {$CFG->prefix}monit_school
						     				WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid
						     				ORDER BY number");
					if ($yid == 2) 	$maxsum = 120;     				
			break;
			case 3: $arr_schools =  get_records_sql("SELECT *  FROM {$CFG->prefix}monit_udod
						     				WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid
						     				ORDER BY number");
			break;
			case 1: $arr_schools =  get_records_sql("SELECT *  FROM {$CFG->prefix}monit_education
						     				WHERE rayonid = $rid AND isclosing=0 AND yearid=$yid AND typeeducation=1
						     				ORDER BY number");
			break;
		}

        // print_r($arr_schools);
        $i=0;
		if ($arr_schools) foreach ($arr_schools as $school) {
				$schoolname = "<strong>$school->name</strong></a>&nbsp;";
				$douid = $udodid = $sid = 0;
				switch($type_ou)	{
					case 0:	$sid = $school->id;
							$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
									   FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
									   WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid";
     				
					break;
					case 3: $udodid = $school->id;
							$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
									   FROM {$CFG->prefix}monit_udod INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_udod.id = {$CFG->prefix}monit_accreditation.udodid
									   WHERE {$CFG->prefix}monit_accreditation.udodid = $udodid";
					
					break;
					case 1: $douid = $school->id;
							$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
									   FROM {$CFG->prefix}monit_education INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_education.id = {$CFG->prefix}monit_accreditation.douid
									   WHERE {$CFG->prefix}monit_accreditation.douid = $douid";
					break;
				}


			 	$sum = 0.0;
			    if ($rec = get_record_sql($strsql))  {
					$sum = $rec->sum;
				}

				$proc = number_format($sum/$maxsum*100, 2, ',', '');
			    $proc .= '%';

			   	$strtotlamark = $sum . ' (' . $proc . ')';
				if ($school->number == 0)	{
					$table->data[$i][] = $i+1;
				} else {
					$table->data[$i][] = $school->number;	
				}	
				
				$link = "accredit.php?yid=$yid&amp;rid=$rid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;cid={$criteria->id}&amp;type_ou=$type_ou";
				$table->data[$i][] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/". $link . '"> ' . $schoolname . '</a>';
				
 		        if ($criterions) 
				 	foreach ($criterions as $criteria) {

					switch($type_ou)	{
						case 0: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
										   FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
										   WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid AND criteriaid={$criteria->id}";
	     				
						break;
						case 3: $udodid = $school->id;
								$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
										   FROM {$CFG->prefix}monit_udod INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_udod.id = {$CFG->prefix}monit_accreditation.udodid
										   WHERE {$CFG->prefix}monit_accreditation.udodid = $udodid AND criteriaid={$criteria->id}";
						
						break;
						case 1: $douid = $school->id;
								$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
										   FROM {$CFG->prefix}monit_education INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_education.id = {$CFG->prefix}monit_accreditation.douid
										   WHERE {$CFG->prefix}monit_accreditation.douid = $douid AND criteriaid={$criteria->id}";
						break;
					}

				 	$sum = 0.0;

				    if ($rec = get_record_sql($strsql))  {
						$sum = $rec->sum;
					}

					$proc = number_format($sum/$maxsum*100, 2, ',', '');
				    $proc .= '%';

				   	$strmark = $sum . ' (' . $proc . ')';
			  	    $table->data[$i][] = $strmark;
			    }
				$table->data[$i][] = $strtotlamark;
				$i++;

        }
      	print_color_table($table);

    print_footer();
    
    
?>


