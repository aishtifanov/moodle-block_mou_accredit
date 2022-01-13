<?php // $Id: __back.php,v 1.1.1.1 2009/10/22 11:28:07 Shtifanov Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('../monitoring/lib.php');
    
    $PREVYEARID = 3;
	$CURRYEARID = 4;


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title','block_mou_ege').'</a>';
	$breadcrumbs .= " -> __ COPY CRITERIA IN NEW YEAR";
    print_header("$SITE->shortname: __ COPY CRITERIA IN NEW YEAR", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    ignore_user_abort(false); 
    @set_time_limit(0);
    @ob_implicit_flush(true);
    @ob_end_flush();
	@raise_memory_limit("512M");
 	if (function_exists('apache_child_terminate')) {
	    @apache_child_terminate();
	}    

	$criterions =  get_records_select('monit_accr_criteria', "yearid=$PREVYEARID", 'id');
	
	$listid = '';
	foreach ($criterions as $criterion)	{
		$id = $criterion->id + 100;
		$listid .= $criterion->id . ',';
		$strsql = "INSERT INTO mdl_monit_accr_criteria VALUES ($id, $CURRYEARID, 
				   $criterion->type_ou, '$criterion->num', '$criterion->name', $criterion->startiid, $criterion->endiid)";
		echo $strsql . '<hr>';  
		$db->Execute($strsql);		   
	}
	$listid .= '0';


	$strsql = "criteriaid in ($listid)";
	echo $strsql . '<hr><hr><hr>';  
	$indicators =  get_records_select('monit_accr_indicator', $strsql, 'id');
	$firstind = current($indicators);
	$endind = end($indicators);	
	foreach ($indicators as $indicator)	{
		$id = $indicator->id + 400;
		$criteriaid = $indicator->criteriaid + 100;
		$strsql = "INSERT INTO mdl_monit_accr_indicator VALUES ($id, $criteriaid, 
				   '$indicator->num', '$indicator->name', '$indicator->description')";
		echo $strsql . '<hr>';  
		$db->Execute($strsql);		   
	}

	$strsql = "indicatorid between $firstind->id and $endind->id";
	echo $strsql . '<hr><hr><hr>';	
	$estimatess =  get_records_select('monit_accr_estimates', $strsql, 'id');
	foreach ($estimatess as $estimates)	{
		$id = $estimates->id + 1000;
		$indicatorid = $estimates->indicatorid + 400;
		$strsql = "INSERT INTO mdl_monit_accr_estimates VALUES ($id, $indicatorid, 
				   '$estimates->name', $estimates->mark)";
		echo $strsql . '<hr>';  
		$db->Execute($strsql);		   
	}
		
?>