<?PHP // $Id: clearindicator.php,v 1.4 2009/11/30 10:08:04 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $rid = required_param('rid', PARAM_INT);        // Rayon id
  	$yid = required_param('yid', PARAM_INT);       // Year id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $sid = optional_param('sid', 0, PARAM_INT);        // School id    
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);

	$confirm = optional_param('confirm');

    $yid = get_current_edu_year_id();

	// add_to_log
	error(get_string('accessdenied', 'admin'));
	
	require_once('../authall.inc.php');
	
    $criteria =  get_record('monit_accr_criteria', 'id', $cid);

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strtitle = get_string('clearindicators','block_monitoring');
    $strcriteriagroup  = get_string('criteriagroup', 'block_mou_att') . ' ' . $criteria->num;
    $strindicator = get_string('onecriteria', 'block_monitoring');
    
	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accredit.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".get_string('criteriagroups', 'block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"indicators.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cid=$cid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".$strcriteriagroup.'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	$redirlink = "accredit.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou";
	
	if (isset($confirm))   {
		if ($indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, 'num'))	{
          	foreach ($indicators as $indicator) {
			    if ($accr = get_record('monit_accreditation', 'criteriaid', $cid, 'indicatorid', $indicator->id))	 {
					delete_records('monit_accreditation', 'criteriaid', $cid, 'indicatorid', $indicator->id);
                }
            }
        }
		redirect($redirlink, get_string('indicatorsdeleted','block_monitoring'), 2);
	}

  	$strcriter = 'Группа критериев '.$criteria->num . '. ' . $criteria->name;

	print_heading($strtitle .' :: ' .$strcriter);

    $s1 = get_string('clearcheckfull', 'block_monitoring', '');

	notice_yesno($s1, "clearindicator.php?yid=$yid&amp;rid=$rid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;cid={$criteria->id}&amp;type_ou=$type_ou&amp;confirm=1", $redirlink );

	print_footer();
?>
