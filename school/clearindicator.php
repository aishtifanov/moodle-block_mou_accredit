<?PHP // $Id: clearindicator.php,v 1.2 2009/11/19 10:10:43 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');

    $rid     = required_param('rid', PARAM_INT);        // Rayon id
    $sid     = required_param('sid', PARAM_INT);        // School id
  //  $yid 	 = required_param('yid', PARAM_INT);       		// Year id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
	$confirm = optional_param('confirm');

    $yid = get_current_edu_year_id();

    if (! $site = get_site()) {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }

	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	$school_operator_is = ismonitoperator('school', 0, $rid, $sid);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is && !$school_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strindicators = get_string('indicators', 'block_monitoring');
    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');
    $strschools = get_string('schools', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');
    $strclearindicators = get_string('clearindicators','block_monitoring');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"indicators.php?rid=$rid&amp;sid=$sid&amp;cid=$cid\">$strindicators</a>";
	$breadcrumbs .= " -> $strclearindicators";
    print_header_mou("$site->shortname: $strclearindicators", $site->fullname, $breadcrumbs);

	if (isset($confirm))   {
		if ($indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, 'num'))	{
          	foreach ($indicators as $indicator) {
			    if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $indicator->id))	 {
					delete_records('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $indicator->id);
                    if ($cid == 1 && $indicator->id == 17) {
				  	    if ($accr_table = get_record('monit_accr_table_17', 'accrid', $accr->id))  {
							delete_records('monit_accr_table_17', 'accrid', $accr->id);
				  	    }
				  	}
                }
            }
        }
		redirect("accreditation.php?rid=$rid&amp;sid=$sid", get_string('indicatorsdeleted','block_monitoring'), 3);
	}

	$criteria =  get_record('monit_accr_criteria', 'id', $cid);
  	$strcriter = 'Критерий '.$criteria->num . '. ' . $criteria->name;

	print_heading($strclearindicators .' :: ' .$strcriter);

    $s1 = get_string('clearcheckfull', 'block_monitoring', '');

	notice_yesno($s1, "clearindicator.php?rid=$rid&amp;sid=$sid&amp;cid=$cid&amp;confirm=1", "accreditation.php?rid=$rid&amp;sid=$sid");

	print_footer();
?>
