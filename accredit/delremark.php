<?PHP // $Id: delremark.php,v 1.3 2010/02/09 09:30:34 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $rid = required_param('rid', PARAM_INT);   // Rayon id
	$yid = required_param('yid', PARAM_INT);			// Year id
	$mid = optional_param('mid', 0, PARAM_INT);			// Remark id
	$sid = optional_param('sid', 0, PARAM_INT);	// School id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $type_ou  = optional_param('type_ou', 0, PARAM_INT);
	$confirm = optional_param('confirm');


	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	if (isregionviewoperator())  {
        error(get_string('adminaccess', 'block_monitoring'));
	}

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strremarks = get_string('remarks', 'block_monitoring');
   	$straddremark = get_string('delremark','block_monitoring');

	$breadcrumbs  = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"remark.php?type_ou=$type_ou&amp;ataba=remark&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid\">".get_string('remarks', 'block_monitoring').'</a>';
	$breadcrumbs .= " -> $straddremark";
    print_header("$SITE->shortname: $straddremark", $SITE->fullname, $breadcrumbs);

	$redirlink = "remark.php?type_ou=$type_ou&amp;ataba=remark&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid";
	
	if (isset($confirm)) {
		delete_records('monit_accr_remark', 'id', $mid);
		//  add_to_log(1, 'school', 'Discipline deleted', 'deldiscipline.php', $USER->lastname.' '.$USER->firstname);
		redirect($redirlink, get_string('remarkdeleted','block_monitoring'));
	}

	$remark = get_record("monit_accr_remark", "id", $mid);

	print_heading($straddremark .' :: ' .$remark->name);

    // $str = get_string('disciplinelow', 'block_mou_ege') . ' ' . "'$adiscipl->name'";
    $str = "'$remark->name'";

	notice_yesno(get_string('deletecheckfull', '', $str),
               "delremark.php?type_ou=$type_ou&amp;ataba=remark&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid&amp;mid=$mid&amp;confirm=1",
               $redirlink);

	print_footer();
?>
