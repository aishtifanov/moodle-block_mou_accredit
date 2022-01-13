<?php // $Id: index.php,v 1.3 2009/12/16 10:46:51 Shtifanov Exp $

    require_once('../../config.php');
    require_once('../monitoring/lib.php');

    require_login();

    $strmonit = get_string('accrfrontpagetitle', 'block_mou_att');

    print_header_mou("$SITE->shortname: $strmonit", $SITE->fullname, $strmonit);

    print_heading($strmonit);

	$admin_is = isadmin();
	$staff_operator_is = ismonitoperator('staff');
	$region_operator_is = ismonitoperator('region');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	if  (!$admin_is && !$region_operator_is && $rayon_operator_is) 	{
		$rid = $rayon_operator_is;
	}	else {
		$rid = 1;
	}
	$sid = ismonitoperator('school', 0, 0, 0, true);
	$college_operator_is = ismonitoperator('college', 0, 0, 0, true);

	$staffview_operator = isstaffviewoperator();


	$dod_rayon_operator_is  = ismonitoperator('dod_rayon', 0, 0, 0, true);
	if  (!$admin_is && !$region_operator_is && $dod_rayon_operator_is) 	{
		$rid = $dod_rayon_operator_is;
	}
	$dod_school_operator_is = ismonitoperator('dod_school', 0, 0, 0, true);
	$dou_operator_is = ismonitoperator('dou', 0, 0, 0, true);	

    $table->align = array ('right', 'left');
    

	if ($admin_is || $staff_operator_is || $rayon_operator_is)	 {
	    	$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=0&amp;rid=$rid\">".get_string('schools', 'block_monitoring').'</a></strong>',
 	                          get_string('description_school_accr','block_mou_att'));

   	        $table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=3&amp;rid=$rid\">".get_string('udods', 'block_monitoring').'</a></strong>',
 	                          get_string('description_udod_accr','block_mou_att'));

			$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=1&amp;rid=$rid\">".get_string('dous', 'block_mou_att').'</a></strong>',
 	                          get_string('description_dous_accr','block_mou_att')); 	                          

	    	$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/reports/reports.php?rid=$rid&amp;sid=$sid\">".get_string('reports', 'block_mou_att').'</a></strong>',
 	                          get_string('description_reports','block_mou_att'));
    }


	if (!$admin_is && !$staff_operator_is && !$rayon_operator_is && $sid)	 {

		  $yid = get_current_edu_year_id();

	      if ($school = get_record_sql ("SELECT id, rayonid, uniqueconstcode FROM {$CFG->prefix}monit_school
	                                     WHERE uniqueconstcode=$sid AND yearid=$yid"))	{

	    	$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=0&amp;rid={$school->rayonid}&amp;sid={$school->id}\">".get_string('school', 'block_monitoring').'</a></strong>',
 	                          get_string('description_school_accr','block_mou_att'));
	                                     	
	   	  }
	}


	if (!$admin_is && !$staff_operator_is && ($dod_rayon_operator_is || $dod_school_operator_is))  {
		   if ($dod_school_operator_is)	{
		       if ($udod = get_record('monit_udod', 'id', $dod_school_operator_is))  {
	  	       	   $udodrayonid = $udod->rayonid;
				   $udodid = $udod->id;
 		 	   }
 		   } else {
		       $udodrayonid = $dod_rayon_operator_is;
			   $udodid	= 0;
 		   }
   	       $table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=3&amp;rid=$udodrayonid&amp;udodid=$udodid\">".get_string('udod', 'block_monitoring').'</a></strong>',
 	                          get_string('description_udod_staffs','block_mou_att'));
 	                          
		   if (!$dod_school_operator_is)	{
  		   		$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=1&amp;rid=$rid\">".get_string('dous', 'block_mou_att').'</a></strong>',
 	            		              get_string('description_dous_accr','block_mou_att'));
		   }						    	                          
 	                          
    }
    
    
		if (!$admin_is && !$staff_operator_is && $dou_operator_is)  {
	       if ($dou = get_record('monit_education', 'id', $dou_operator_is))  {
  	       	   $dourayonid = $dou->rayonid;
	 	   } else {
		       $dourayonid = 0;
 		   }

  		   $table->data[] = array('<strong><a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=1&amp;rid=$dourayonid&amp;douid=$dou_operator_is\">".get_string('dou', 'block_mou_att').'</a></strong>',
 	                          get_string('description_dous_accr','block_mou_att')); 	                          
	       
        }
    


/*
   	$table->data[] = array('<strong><a href="'.$CFG->wwwroot."/file.php/1/instruction_mo_att.doc\">".get_string('instruction_accr', 'block_mou_att').'</a></strong>',
 	                          get_string('description_instruction_accr', 'block_mou_att'));

*/
    print_table($table);
    // print_color_table($table);

    print_footer();

?>


