<?php // $Id: indicators.php,v 1.3 2009/11/30 10:08:12 Shtifanov Exp $

    require_once("../../../config.php");
	require_once('../../monitoring/lib.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
	$action  = optional_param('action', '');
	$yid = 2;

	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	$school_operator_is = ismonitoperator('school', 0, $rid, $sid);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is && !$school_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    if ($action == 'excel') {
        print_excel_indicators($rid, $sid, $cid);
        exit();
	}


	$staffview_operator = isstaffviewoperator();

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strindicators = get_string('indicators', 'block_monitoring');


/*
    if ($sid!=0)	{
    	$school = get_record('monit_school', 'id', $sid);
   	    $strschool = $school->name;
    }	else  {
   	    $strschool = get_string('school', 'block_monitoring');
    }
*/

    $strrayon = get_string('rayon', 'block_monitoring');
    $strrayons = get_string('rayons', 'block_monitoring');

    $strschools = get_string('schools', 'block_monitoring');
    $strreports = get_string('reportschool', 'block_monitoring');

	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accreditation.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('criterions', 'block_mou_att').'</a>';	
	$breadcrumbs .= " -> $strindicators";
    print_header_mou("$SITE->shortname: $strindicators", $SITE->fullname, $breadcrumbs);


	if ($rec = data_submitted())  {
	      if (isset($rec->prevcrit))		{
		        if ($cid == 1) $cid = 6;
		        else $cid--;
		  } else if (isset($rec->nextcrit))		{
		        if ($cid == 6) $cid = 1;
		        else $cid++;
		  }
   	    //  redirect("indicators.php?rid=$rid&amp;sid=$sid&amp;cid=$cid", '', 0);
	}

    // add_to_log(SITEID, 'monit', 'school view', 'school.php?id='.SITEID, $strschool);

	if ($rid == 0 ||  $sid == 0) {
	    print_footer();
	 	exit();
	}


	if ($rayon_operator_is && $rayon_operator_is != $rid)  {
		notify(get_string('selectownrayon', 'block_monitoring'));
	    print_footer();
		exit();
	}

    $school = get_record('monit_school', 'id', $sid);

	print_heading($straccreditation.': '.$school->name, "center", 3);

	$criteria =  get_record('monit_accr_criteria', 'id', $cid);

	$strcriter = 'Критерий '.$criteria->num . '. ' . $criteria->name;
	print_heading($strcriter, "center", 3);


	$strsql = "SELECT Sum(a.mark) AS sum
			   FROM {$CFG->prefix}monit_school s, {$CFG->prefix}monit_accreditation  a
			   WHERE s.id = a.schoolid AND a.schoolid = $sid AND a.criteriaid = $cid";

 	$sum = '-';

    if ($rec = get_record_sql($strsql))  {
		$sum = $rec->sum;
	}

//    echo $cid.'<hr>';
//    echo $sum.'<hr>';

	$indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, 'num');

    $straction = get_string('action', 'block_monitoring');
    $table->head  = array ('№', get_string('indicator', 'block_monitoring'),
	    						get_string('mark', 'block_monitoring'),
	    						$straction);
    $table->align = array ('left', 'left', 'center', 'center');
    $table->class = 'moutable';

    if(!empty($indicators)) {

		  $sum = 0;

          foreach ($indicators as $indicator) {

				$strindicator = ' ';
                if ($staffview_operator)	{
					$title = get_string('viewindicator','block_monitoring');
				} else {
					$title = get_string('editindicator','block_monitoring');
				}

				$strlinkupdate = "<a title=\"$title\" href=\"editindicator.php?rid=$rid&amp;sid=$sid&amp;cid=$cid&amp;iid={$indicator->id}\">";
				$criterianame = $strlinkupdate . "<strong>$indicator->name</strong></a>&nbsp;";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

			    if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $indicator->id))	 {
					$mark = $accr->mark;
                    if ($cid == 1 && $indicator->id == 17) {
                    	$plusum = $plusum1 = $plusum2 = 0;
				  	    if ($accr_table = get_record('monit_accr_table_17', 'accrid', $accr->id))  {
					  	  	  if(!empty($accr_table->numregion))  {
					  	  	  	 $plusum1 += $accr_table->numregion*2;
					  	  	  }
					  	  	  if(!empty($accr_table->numrayon)) {
					  	  	  	 $plusum2 += $accr_table->numrayon;
					  	  	  }
					  	  	  if ($plusum1 > 20) $plusum1 = 20;
					  	  	  if ($plusum2 > 10) $plusum2 = 10;
					  	  	  $plusum = $plusum1 + $plusum2;
					  	  	  if ($plusum > 20) $mark = 20;
					  	  	  else $mark = $plusum;
					  	}
					}
					$sum += $mark;

		       		if ($estimate = get_record('monit_accr_estimates', 'indicatorid', $indicator->id, 'mark', $mark))	{
				        $strindicator = '&raquo; ' . $estimate->name;
		       		}

				} else {
					$mark = '-';
				}

	 		    if ($strindicator != ' ')	{
  		 		    $strindicator = $indicator->name . '<p><b>' . $strindicator . '</b></p>';
	 		    } else 	{
  		 		    $strindicator = $indicator->name;
	 		    }

       			$table->data[] = array ($indicator->num . '.', $strindicator, $mark, $strlinkupdate);
          }
		  $strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $sum;
	  	  print_heading($strtotlamark, 'center', 4);
          print_color_table($table);
    }
?>
	<table align="center">
    <form name=form method=post action=indicators.php>
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
	<input type="hidden" name="action" value="copy" />
	<tr>
		<td align="center">
		<input type="submit" name=prevcrit value="<?php print_string('prevcriteria', 'block_mou_att') ?>" />
		</td>
		<td align="center">&nbsp;
		</td>
		<td align="center">
		<input type="submit" name=nextcrit value="<?php print_string('nextcriteria', 'block_mou_att') ?>" />
		</td>
	</tr>
    </form>
	</table>


	<table align="center">
	<tr><td>
			<form name="download" method="post" action="indicators.php">
				<input type="hidden" name="rid" value="<?php echo $rid ?>" />
				<input type="hidden" name="sid" value="<?php echo $sid ?>" />
				<input type="hidden" name="cid" value="<?php echo $cid ?>" />
				<input type="hidden" name="action" value="excel" />
				<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
		    </form>
		</td>
	</tr>
	</table>
<?php

    print_footer();


function print_excel_indicators($rid, $sid, $cid)
{
    global $CFG;

    if ($rid == 0 || $sid == 0 || $cid == 0) return false;

    $downloadfilename = 'indicators_'.$sid.'_'.$cid;

    require_once("$CFG->libdir/excel/Worksheet.php");
    require_once("$CFG->libdir/excel/Workbook.php");

	// HTTP headers
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"{$downloadfilename}.xls\"");
    header("Expires: 0");
    header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
    header("Pragma: public");

    // Creating a workbook
    $workbook = new Workbook("-");
    $myxls =& $workbook->add_worksheet($downloadfilename);

    $myxls->set_margin_left(0.8);
    $myxls->set_margin_right(0.4);
    $myxls->set_margin_top(0.7);
    $myxls->set_margin_bottom(0.7);

	// Print names of all the fields
	$formath1 =& $workbook->add_format();
	$formath2 =& $workbook->add_format();
	$formath3 =& $workbook->add_format();
	$formatp =& $workbook->add_format();
	$formatb =& $workbook->add_format();
	$formatp2 =& $workbook->add_format();
	$formatp3 =& $workbook->add_format();

	$formath1->set_size(12);
	$formath1->set_align('center');
	$formath1->set_align('vcenter');
	$formath1->set_color('black');
	$formath1->set_bold(1);
	$formath1->set_italic();
	// $formath1->set_border(2);

	$formath2->set_size(11);
    $formath2->set_align('center');
    $formath2->set_align('vcenter');
	$formath2->set_color('black');
	$formath2->set_bold(1);
	//$formath2->set_italic();
	//$formath2->set_border(2);
	$formath2->set_text_wrap();

	$formath3->set_size(11);
    $formath3->set_align('center');
    $formath3->set_align('vcenter');
	$formath3->set_color('black');
	$formath3->set_bold(1);
	$formath3->set_italic();
	$formath3->set_border(1);
	$formath3->set_text_wrap();

	$formatb->set_size(10);
    $formatb->set_align('left');
    $formatb->set_align('vcenter');
	$formatb->set_color('black');
	// $formatb->set_top_color('gray');
	$formatb->set_bold(1);
	$formatb->set_border(1);
	$formatb->set_text_wrap();
	$formatb->set_top(0);

	$formatp->set_size(11);
    $formatp->set_align('left');
    $formatp->set_align('vcenter');
	$formatp->set_color('black');
	$formatp->set_bold(0);
	$formatp->set_border(1);
	$formatp->set_text_wrap();

	$formatp2->set_size(11);
    $formatp2->set_align('left');
    $formatp2->set_align('vcenter');
	$formatp2->set_color('black');
	$formatp2->set_bold(0);
	$formatp2->set_border(1);
	$formatp2->set_text_wrap();
	$formatp2->set_bottom(0);

	$formatp3->set_size(11);
    $formatp3->set_align('center');
    $formatp3->set_align('vcenter');
	$formatp3->set_color('black');
	$formatp3->set_bold(0);
	$formatp3->set_border(1);
	$formatp3->set_text_wrap();

    $txtl = new textlib();

    $rayon = get_record('monit_rayon', 'id', $rid);

   	$school = get_record('monit_school', 'id', $sid);

	$criteria =  get_record('monit_accr_criteria', 'id', $cid);


	$strsql = "SELECT Sum(a.mark) AS sum
			   FROM {$CFG->prefix}monit_school s, {$CFG->prefix}monit_accreditation  a
			   WHERE s.id = a.schoolid AND a.schoolid = $sid AND a.criteriaid = $cid";

 	$sum = '-';

    if ($rec = get_record_sql($strsql))  {
		$sum = $rec->sum;
	}

    $numberf = get_string('symbolnumber', 'block_monitoring');
    $exceltable->head  = array ($numberf, get_string('indicator', 'block_monitoring'), get_string('mark', 'block_monitoring'));
    $exceltable->column = array (5, 75, 6);
	$countcols = count($exceltable->head);

    $i = $j = 0;
    foreach ($exceltable->column as $key => $width) {
		$myxls->set_column($i++, $j++, $width);
	}

    $straccreditation = get_string('accreditation', 'block_monitoring');

	$myxls->set_row(0, 30);
	$strwin1251 =  $txtl->convert($straccreditation.': '.$school->name, 'utf-8', 'windows-1251');
    $myxls->write_string(0, 0, $strwin1251, $formath1);
	$myxls->merge_cells(0, 0, 0, $countcols-1);

	$strcriter = 'Критерий '.$criteria->num . '. ' . $criteria->name;
	$myxls->set_row(1, 30);
	$strwin1251 =  $txtl->convert($strcriter, 'utf-8', 'windows-1251');
    $myxls->write_string(1, 0, $strwin1251, $formath2);
	$myxls->merge_cells(1, 0, 1, $countcols-1);

/*
	$strtitle =  '('.$rayon->name.', '.$school->name.')';
	$myxls->set_row(2, 30);
	$strwin1251 =  $txtl->convert($strtitle, 'utf-8', 'windows-1251');
    $myxls->write_string(2, 0, $strwin1251, $formath2);
*/


    if($indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, 'num')) {

		$countrows = count($indicators)*2+2;
	    for ($i=2; $i<$countrows; $i++)	{
	       for ($j=0; $j<$countcols; $j++)	   {
				$myxls->write_blank($i,$j,$formatp);
	 		}
	    }

		$i = 2;	$j = 0;
	    foreach ($exceltable->head as $key => $heading) {
			$strwin1251 =  $txtl->convert($heading, 'utf-8', 'windows-1251');
	        $myxls->write_string($i, $j++, $strwin1251, $formath3);
	    }

        $num = 1;
        $i = 3;
		foreach ($indicators as $indicator) {

				$strindicator = ' ';

			    if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $indicator->id))	 {
					$mark = $accr->mark;
                    if ($cid == 1 && $indicator->id == 17) {
                    	$plusum = $plusum1 = $plusum2 = 0;
				  	    if ($accr_table = get_record('monit_accr_table_17', 'accrid', $accr->id))  {
					  	  	  if(!empty($accr_table->numregion))  {
					  	  	  	 $plusum1 += $accr_table->numregion*2;
					  	  	  }
					  	  	  if(!empty($accr_table->numrayon)) {
					  	  	  	 $plusum2 += $accr_table->numrayon;
					  	  	  }
					  	  	  if ($plusum1 > 20) $plusum1 = 20;
					  	  	  if ($plusum2 > 10) $plusum2 = 10;
					  	  	  $plusum = $plusum1 + $plusum2;
					  	  	  if ($plusum > 20) $mark = 20;
					  	  	  else $mark = $plusum;
					  	}
					}
					$sum += $mark;

		       		if ($estimate = get_record('monit_accr_estimates', 'indicatorid', $indicator->id, 'mark', $mark))	{
				        $strindicator = '> ' . $estimate->name;
		       		}

				} else {
					$mark = '-';
				}

       			$exceltable->data = array ($indicator->num . '.', strip_tags($indicator->name), $mark);
				$num++;
                /*
	 	        for ($j=0; $j<$countcols; $j++)	 {
	        		$strwin1251 =  $txtl->convert($exceltable->data[$j], 'utf-8', 'windows-1251');
	    	       	$myxls->write($i, $j, $strwin1251, $formatp);
	 		    }
	 		    */
	        		$strwin1251 =  $txtl->convert($exceltable->data[0], 'utf-8', 'windows-1251');
	    	       	$myxls->write_string($i, 0, $strwin1251, $formatp);

	        		$strwin1251 =  $txtl->convert($exceltable->data[1], 'utf-8', 'windows-1251');
	    	       	$myxls->write($i, 1, $strwin1251, $formatp2);

	        		$strwin1251 =  $txtl->convert($exceltable->data[2], 'utf-8', 'windows-1251');
	    	       	$myxls->write($i, 2, $strwin1251, $formatp3);


	 		    if ($strindicator != ' ')	{
		 		    $strwin1251 =  $txtl->convert(strip_tags($strindicator), 'utf-8', 'windows-1251');
   	    	       	$myxls->write($i+1, 1, $strwin1251, $formatb);
               		$myxls->merge_cells($i, 0, $i+1, 0);
               		$myxls->merge_cells($i, 2, $i+1, 2);
		 		    $i+=2;
	 		    } else {
			  	    $i++;
			  	}

        }
		$strwin1251 =  $txtl->convert(get_string('vsego','block_monitoring'), 'utf-8', 'windows-1251');
	    $myxls->write_string($i, 1, $strwin1251, $formath1);
		$myxls->write_formula($i, 2, "=SUM(C3:C$i)", $formath1);

    }

    $workbook->close();

	return true;
}

?>


