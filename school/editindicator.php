<?php // $Id: editindicator.php,v 1.3 2009/11/30 10:08:11 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $iid = required_param('iid', PARAM_INT);          // Indicator id
	$yid = 2;

	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	$school_operator_is = ismonitoperator('school', 0, $rid, $sid);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is && !$school_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	$staffview_operator = isstaffviewoperator();
    // $rayon = get_record('monit_rayon', 'id', $rid);

    $school = get_record('monit_school', 'id', $sid);

    $criteria =  get_record('monit_accr_criteria', 'id', $cid);

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strindicators = get_string('indicators', 'block_monitoring');
    $strindicator = get_string('indicator', 'block_monitoring');

	$redirurl = "indicators.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cid=$cid";
	 
	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accreditation.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('criterions', 'block_mou_att').'</a>';	
	$breadcrumbs .= " -> <a href=\"$redirurl\">".$strindicators.'</a>';	
	$breadcrumbs .= " -> $strindicator";
    print_header_mou("$SITE->shortname: $strindicators", $SITE->fullname, $breadcrumbs);


	/// A form was submitted so process the input
	if ($rec = data_submitted())  {
	   // print_r($rec);  echo '<hr>';

       $accreditation->schoolid = $sid;
       $accreditation->criteriaid = $cid;
       $accreditation->indicatorid = $iid;
       $accreditation->mark = $rec->estimate;

       if (!isset($err)) 	{

          if (!$staffview_operator)	  {

           $strmessagesave = get_string('succesavedata','block_monitoring');

		   if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $iid))	 {
				$accreditation->id = $accr->id;
			    if (!update_record('monit_accreditation', $accreditation))	{
					error(get_string('errorinupdatingindicator','block_monitoring'), $redirurl);
				}

				if ($iid == 17)		{
					if ($accr_table = get_record('monit_accr_table_17', 'accrid', $accr->id))  {

 	  					if (isset($rec->kol_mest_49) && !empty($rec->kol_mest_49))	{
	    					   $accr_table->numregion = $rec->kol_mest_49;
						}
 	  					if (isset($rec->kol_mest_50) && !empty($rec->kol_mest_50))	{
	    					   $accr_table->numrayon = $rec->kol_mest_50;
						}

					    if (!update_record('monit_accr_table_17', $accr_table))	{
							error(get_string('errorinupdatingindicator','block_monitoring') . '(monit_accr_table_17)', $redirurl);
						}

  					} else {
					   unset($accr_table);
					   $accr_table->accrid = $accr->id;
						if (isset($rec->kol_mest_49) && !empty($rec->kol_mest_49))	{
						   $accr_table->numregion = $rec->kol_mest_49;
						}
						if (isset($rec->kol_mest_50) && !empty($rec->kol_mest_50))	{
						   $accr_table->numrayon = $rec->kol_mest_50;
						}
				        if (!$new_id = insert_record('monit_accr_table_17', $accr_table))	{
							error(get_string('errorincreatingindicator','block_monitoring') . '(monit_accr_table_17)', $redirurl);
					    }
  					}
				}

	      } else {
		       if (!$new_id = insert_record('monit_accreditation', $accreditation))	{
					error(get_string('errorincreatingindicator','block_monitoring'), $redirurl);
			   }
			   if ($iid == 17)	{
				   unset($accr_table);
				   $accr_table->accrid = $new_id;
					if (isset($rec->kol_mest_49) && !empty($rec->kol_mest_49))	{
					   $accr_table->numregion = $rec->kol_mest_49;
					}
					if (isset($rec->kol_mest_50) && !empty($rec->kol_mest_50))	{
					   $accr_table->numrayon = $rec->kol_mest_50;
					}
			       if (!$new_id = insert_record('monit_accr_table_17', $accr_table))	{
						error(get_string('errorincreatingindicator','block_monitoring') . '(monit_accr_table_17)', $redirurl);
				   }
			   }

		  }
		  } else {
				$strmessagesave = '';
		  }

	 	  // $strmessagesave = '<b>Данные НЕ СОХРАНЕНЫ, так как это устаревшие критерии аккредитации</b>';
	 	  $startid	= $criteria->startiid;
	 	  $endid 	= $criteria->endiid;

          if (isset($rec->nextind))  {
   				if ($iid == 51)  $iid = 16;
				else if ($iid == $endid)  $iid = $startid;
				else $iid++;
				$num = $iid - $startid + 1;
		   	    redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;num=$num", $strmessagesave, 0);
		  } else if (isset($rec->prevind))  {
   				if ($iid == 51)  $iid = 15;
				else if ($iid == $startid)  $iid = $endid;
				else $iid--;
				$num = $iid - $startid + 1;
		   	    redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;num=$num", $strmessagesave, 0);
		  } else if (isset($rec->prevcrit))		{
		        if ($cid == 1) $cid = 6;
		        else $cid--;
			    $criteriaprev =  get_record('monit_accr_criteria', 'id', $cid);
			    $iidprev = $criteriaprev->startiid;
		   	    redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iidprev&amp;cid=$cid", $strmessagesave, 0);
		  } else if (isset($rec->nextcrit))		{
		        if ($cid == 6) $cid = 1;
		        else $cid++;
			    $criterianext =  get_record('monit_accr_criteria', 'id', $cid);
			    $iidnext = $criterianext->startiid;
		   	    redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iidnext&amp;cid=$cid", $strmessagesave, 0);
		  } else {
	          // $strlinkupdate = "<a title=\"$title\" href=\"editcriteria.php?cat=$category&amp;rid=$rid&amp;sid=$sid&amp;uid=$uid&amp;cid={$criteria->id}&amp;num=$num\">";
	          // notice(get_string('succesavedata','block_monitoring'), "indicators.php?rid=$rid&amp;sid=$sid&amp;cid=$cid");
		   	  redirect("indicators.php?rid=$rid&amp;sid=$sid&amp;cid=$cid", $strmessagesave, 0);
		  }
       }

    }


	$indicator =  get_record('monit_accr_indicator', 'id', $iid);

	$estimates = get_records('monit_accr_estimates', 'indicatorid', $iid);

    $text = $indicator->num . '. ' . $indicator->name;

   // print_simple_box($text, 'center', '70%', 'white', 5, 'generalbox', 'intro');

	print_heading($straccreditation.': '.$school->name, "center", 3);

	$strcriter = 'Критерий '.$criteria->num . '. ' . $criteria->name;
	print_heading($strcriter, "center", 3);

   	print_heading($strindicator.' №'.$indicator->num, 'center', 4);

    print_simple_box_start('center', '70%', 'white');

    echo "<i><b>$text</b></i> ";

    foreach ($estimates as $estimate) {
        $answerchecked[$estimate->mark] = '';
    }

    if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $iid))	 {
        $answerchecked[$accr->mark] = 'checked="checked"';
    } else {
		$answerchecked[0] = 'checked="checked"';
    }

    echo '<form name=form method=post action=editindicator.php>';
    echo '<table cellpadding=10 cellspacing=10 align=center>';


    if ($iid == 17)		{
      $strt49 = $strt50 = 0;
      if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $iid))	 {
	  	  if ($accr_table = get_record('monit_accr_table_17', 'accrid', $accr->id))  {
 	 	  	  if(!empty($accr_table->numregion)) {
  		  	  	 $strt49 = $accr_table->numregion;
  	 	 	  }
  	  		  if(!empty($accr_table->numrayon)) {
  	  		  	 $strt50 = $accr_table->numrayon;
  	  	  	}
		  }
	  }
	  echo "<tr><td>";
	  echo '<p>1. Количество мест на областных и всероссийских олимпиадах <br>(2 балла за одно место, но не более 20 за все места):&nbsp;';
  	  echo "</td><td>";
	  echo "<INPUT maxLength=3 size=3 name=kol_mest_49  value='$strt49'>";
  	  echo "</td></tr><tr><td>";
	  echo '<p>2. Количество мест на городских и районных олимпиадах <br>(1 балл за одно место, но не более 10 за все места):&nbsp;';
  	  echo "</td><td>";
	  echo "<INPUT maxLength=3 size=3 name=kol_mest_50  value='$strt50'>";
  	  echo "</td></tr>";
  	  echo "<input type=hidden name=estimate value=0 />";

	//   if (isset($err[$namefield])) echo 'style="border-color:#FF0000"';
    } else {
	    foreach ($estimates as $estimate) {

	    	   if ($estimate->mark == 0) {
		     	   $strb = 'баллов';
			   } else if ($estimate->mark == 1) {
		     	   $strb = 'балл';
			   } else {
		    	   $strb = 'балла';
		       }
	 		   $text = "$estimate->name ($estimate->mark $strb)";

	           echo "<tr><td>";

	           if ($answerchecked[$estimate->mark]) {
	    		    echo " <strong><font color='green'>";
	           }

		       echo "<input type=radio name=estimate value=\"".$estimate->mark."\" ".$answerchecked[$estimate->mark]." alt=\"".$text."\" />";
	           echo $text;

	           if ($answerchecked[$estimate->mark]) {
	    		    echo "</font></strong>";
	           }

	           echo '</td></tr>';
	    }
	}
?>
	</table>
	<center>
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="iid" value="<?php echo $iid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
	<input type="hidden" name="num" value="<?php echo $indicator->num ?>" />
	<input type="hidden" name="action" value="copy" />
	<table align="center">
	<tr>
		<td align="center">
		<input type="submit" name=prevind value="<?php print_string('previndicator', 'block_monitoring') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=save value="<?php  print_string('indicators1', 'block_monitoring') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=nextind value="<?php print_string('nextindicator', 'block_monitoring') ?>" />
		</td>
	</tr>
		</table>
    </center>
<?php

  	print_simple_box_end();
?>

	<table align="center">
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
	</table>
    </form>

<?php
    print_footer();

?>

