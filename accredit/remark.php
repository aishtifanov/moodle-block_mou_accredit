<?php // $Id: remark.php,v 1.5 2010/09/02 12:54:36 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_accredit.php');

    $rid = optional_param('rid', 0, PARAM_INT);   // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);	// School id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $yid = optional_param('yid', 3, PARAM_INT);       // Year id
    $ataba = optional_param('ataba', 'acr');
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
   	$action = optional_param('action', '');       // action
	$mid = optional_param('mid', '0', PARAM_INT);       // Remark id   	

    $curryearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryearid;
    }

    require_once('../authall.inc.php');

    if ($action == 'ok')	{
       	  set_field('monit_accr_remark', 'status', 1, 'id', $mid, 'schoolid', $sid);
    } else if ($action == 'break')	{
       	  set_field('monit_accr_remark', 'status', 0, 'id', $mid, 'schoolid', $sid);
    }

    $straccreditation = get_string('title_accredit', 'block_mou_att');
    $strtitle = get_string('remarks', 'block_monitoring');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if ($admin_is  || $region_operator_is )	 	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_rayons("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=", $rid);
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
			break;
		}
		echo '</table>';
	} else if ($rayon_operator_is || $dod_rayon_operator_is) 		{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("remark.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
			break;
		}
		echo '</table>';
	}
	/*
	else if ($school_operator_is) {

		print_heading($strtitle.': '.$school->name, "center", 3);
	} else if  ($dod_school_operator_is)	{

		print_heading($strtitle.': '.$school->name, "center", 3);
	}
	*/


	if ($sid == 0 && $udodid == 0 && $douid == 0) {
	    print_footer();
	 	exit();
	}

	if ($rayon_operator_is && $rayon_operator_is != $rid)  {
		notify(get_string('selectownrayon', 'block_monitoring'));
	    print_footer();
		exit();
	}

	// print_tabs_years_link_accredit($type_ou, $rid, $sid, $udodid, $douid, $yid, $ataba);

    $currenttab = 'remark';
    include('tabs.php');


	switch($type_ou)	{
		case 0:	$school = get_record('monit_school', 'id', $sid);
				print_heading($straccreditation.': '.$school->name, "center", 3);
				$folder = 'school';
				$id = $sid;
				$remarks =  get_records('monit_accr_remark', 'schoolid', $sid, 'id');				
		break;
		case 3: $udod = get_record('monit_udod', 'id', $udodid);
				print_heading($straccreditation.': '.$udod->name, "center", 3);
				$folder = 'udod';
				$id = $udodid;
				$remarks =  get_records('monit_accr_remark', 'udodid', $udodid, 'id');				
		break;
		case 1: $dou = get_record('monit_education', 'id', $douid);
				print_heading($straccreditation.': '.$dou->name, "center", 3);
				$folder = 'dou';
				$id = $douid;
				$remarks =  get_records('monit_accr_remark', 'douid', $douid, 'id');
		break;
	}

	
			    $table->head  = array (get_string('status', 'block_monitoring'),
			    						get_string('remarks', 'block_monitoring'),
				    					get_string('action', 'block_monitoring'));
			    $table->align = array ('center', 'left', 'center');
			    $table->class = 'moutable';
			   	$table->width = '60%';
		        $table->size = array ('20%', '50%', '10%');


			    if(!empty($remarks)) {

			          foreach ($remarks as $remark) {
			          	    $mid = $remark->id;

//		                    if (!$staffview_operator)	{
							if ($admin_is || $region_operator_is || $rayon_operator_is) 	{
								$title = get_string('editremark','block_monitoring');
								$strlinkupdate = "<a title=\"$title\" href=\"addremark.php?mode=edit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;mid=$mid&amp;douid=$douid&amp;udodid=$udodid&amp;type_ou=$type_ou\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('setokremark', 'block_monitoring');
								$strlinkupdate .= "<a title=\"$title\" href=\"remark.php?action=ok&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;mid=$mid&amp;douid=$douid&amp;udodid=$udodid&amp;type_ou=$type_ou\">";
								$strlinkupdate .=  "<img src=\"{$CFG->pixpath}/i/tick_green_big.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('breakremark','block_monitoring');
								$strlinkupdate .= "<a title=\"$title\" href=\"remark.php?mid=$mid&amp;action=break&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid&amp;type_ou=$type_ou\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/minus.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('deleteremark','block_monitoring');
							    $strlinkupdate .= "<a title=\"$title\" href=\"delremark.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;mid=$mid&amp;douid=$douid&amp;udodid=$udodid&amp;type_ou=$type_ou\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
							} else $strlinkupdate = '-';

							$strformrkpu_status = get_string('accrstatus'.$remark->status, "block_monitoring");
							$strcolor = get_string('accrstatus'.$remark->status.'color',"block_monitoring");

			       			$table->data[] = array ($strformrkpu_status, $remark->name, $strlinkupdate);
		          			$table->bgcolor[] = array ($strcolor);
			          }
			          print_color_table($table);

			    }

			    else   {
			    	// notice(get_string('remarknotfound', 'block_monitoring'), "accreditation.php?tab=$tab&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;ataba=acr");
					notify(get_string('remarknotfound', 'block_monitoring'));
			    }

				if  (($admin_is || $region_operator_is || $rayon_operator_is)) { //  && (!$staffview_operator && !$staffview_rayon_operator)) {
				    $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'mode' => 'new',
				    				 'type_ou' => $type_ou, 'douid' => $douid, 'udodid' => $udodid);
					echo '<table align="center" border=0><tr><td>';
				    print_single_button("addremark.php", $options, get_string('addremark','block_monitoring'));
					echo '</td></tr></table>';
				}

    print_footer();
    
    
?>


