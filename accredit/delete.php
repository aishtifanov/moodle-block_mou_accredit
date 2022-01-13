<?php  // $Id: delete.php,v 1.3 2010/10/04 12:59:47 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');    
    
    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $iid = required_param('iid', PARAM_INT);          // Indicator id
	$yid = required_param('yid', PARAM_INT);       // Year id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
    $file     = required_param('file', PARAM_FILE);
    $confirm  = optional_param('confirm', 0, PARAM_BOOL);

	require_once('../authall.inc.php');

    $criteria =  get_record('monit_accr_criteria', 'id', $cid);

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strtitle = get_string('criteria', 'block_monitoring');
    $strcriteriagroup  = get_string('criteriagroup', 'block_mou_att') . ' ' . $criteria->num;
    $strindicator = get_string('onecriteria', 'block_monitoring');

	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accredit.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".get_string('criteriagroups', 'block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"indicators.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cid=$cid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".$strcriteriagroup.'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if ($staffview_operator || $staffview_rayon_operator) {
        add_to_log(1, 'delete.php', 'staffview_operator', 'staffaccess', fullname($USER), '', $USER->id);
        error(get_string('staffaccess', 'block_mou_att'));
	}

    
    $returnurl = "editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou";
    $optionsreturn = array('rid'=>$rid, 'sid'=>$sid, 'cid'=>$cid, 'iid'=>$iid, 'yid'=>$yid, 
							'udodid'=>$udodid, 'douid' => $douid, 'type_ou' => $type_ou);

    if (!$confirm) {
        $optionsyes = $optionsreturn;
		$optionsyes['file'] = $file;
		$optionsyes['confirm']=1;
		$optionsyes['sesskey'] = sesskey();
        print_heading(get_string('delete'));
        notice_yesno(get_string('confirmdeletefile', 'assignment', $file), 'delete.php', 'editindicator.php', $optionsyes, $optionsreturn, 'post', 'get');
        print_footer('none');
        die;
    }

	switch($type_ou)	{
		case 0:	$folder = 'school';
				$id = $sid;
		break;
		case 3: $folder = 'udod';
				$id = $udodid;
		break;
		case 1: $folder = 'dou';
				$id = $douid;
		break;
	}
	
	if ($yid == 3)	{
		$filepath = $CFG->dataroot."/0/$folder/$id/$iid/$file";
	} else {
		$filepath = $CFG->dataroot."/0/$folder/$rid/$id/$iid/$file";
	}	
	

    
    
    if (file_exists($filepath)) {
        if (@unlink($filepath)) {
            redirect($returnurl, get_string('clamdeletedfile') , 0);
        }
    }

    // print delete error
    print_header(get_string('delete'));
    notify(get_string('deletefilefailed', 'assignment'));
    print_continue($returnurl);
    print_footer('none');
?>
