<?php // $Id: editindicator.php,v 1.13 2010/10/04 12:59:46 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once($CFG->libdir.'/tablelib.php');
    require_once($CFG->libdir.'/uploadlib.php');    
	require_once('../lib_accredit.php');    

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
    $iid = required_param('iid', PARAM_INT);          // Indicator id
	$yid = required_param('yid', PARAM_INT);       // Year id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
	$action  = optional_param('action', '');
	
	define("MAX_SCAN_COPY_SIZE", 8388608);

	require_once('../authall.inc.php');

    if (!$criteria =  get_record('monit_accr_criteria', 'id', $cid, 'yearid', $yid, 'type_ou', $type_ou))	{
		add_to_log(1, 'editindicator.php', 'Unknown criteria', 'monit_accr_criteria', fullname($USER), '', $USER->id);		
		error('Unknown criteria.');
    }

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strtitle = get_string('criteria', 'block_monitoring');
    $strcriteriagroup  = get_string('criteriagroup', 'block_mou_att') . ' ' . $criteria->num;
    $strindicator = get_string('onecriteria', 'block_monitoring');

	$eduyear = get_record('monit_years', 'id', $yid);
	$streduyear = get_string('uchyear', 'block_monitoring', $eduyear->name);

	$breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accredit.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".get_string('criteriagroups', 'block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"indicators.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cid=$cid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".$strcriteriagroup.'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	if (!check_optional_param($type_ou, $rid, $sid, $udodid, $douid))	{
	    print_footer();
	 	exit();
	}

	print_heading($streduyear, "center", 1);
	
	switch($type_ou)	{
		case 0:	$strsql = "schoolid=$sid AND criteriaid=$cid AND indicatorid=$iid";
				$folder = 'school';
				$id = $sid;
		break;
		case 3: $strsql = "udodid=$udodid AND criteriaid=$cid AND indicatorid=$iid";
				$folder = 'udod';
				$id = $udodid;
		break;
		case 1: $strsql = "douid=$douid AND criteriaid=$cid AND indicatorid=$iid";
				$folder = 'dou';
				$id = $douid;
		break;
	}
	
	/// A form was submitted so process the input
	if ($rec = data_submitted())  {
	    // print_r($rec);  echo '<hr>';
	    // print_r($_FILES['newfile']);
	   
	   if (!empty($_FILES['newfile']['name']))	{

			if ($yid == 3)	{
				$dir = "0/$folder/$id/$iid";
			} else {
				$dir = "0/$folder/$rid/$id/$iid";
			}	
	   	
       		
       		$um = new upload_manager('newfile',true,false, 1, false, MAX_SCAN_COPY_SIZE);
       		// print_r($um);  echo '<hr>';
	        if ($um->process_file_uploads($dir))  {
		          // $newfile_name = $um->get_new_filename();
        	      // print_heading(get_string('uploadedfile'), 'center', 4);
          	} else {
	          	  notify(get_string("uploaderror", "assignment")); //submitting not allowed!
       		}
	   }
	   
	   $redirlink =  "indicators.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;cid=$cid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou";

       $accreditation->schoolid = $sid;
       $accreditation->criteriaid = $cid;
       $accreditation->indicatorid = $iid;
       $accreditation->mark = $rec->estimate;
	   $accreditation->udodid = $udodid;
	   $accreditation->douid = $douid;

       if (!isset($err)) 	{

          if (!$staffview_operator && !$staffview_rayon_operator)	  {

	           $strmessagesave = get_string('succesavedata','block_monitoring');

			   // if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', $cid, 'indicatorid', $iid))	 {
			  if ($accr = get_record_select('monit_accreditation', $strsql))	{
					$accreditation->id = $accr->id;
				    if (!update_record('monit_accreditation', $accreditation))	{
						error(get_string('errorinupdatingindicator','block_monitoring'), $redirlink);
					}

		      } else {
			       if (!$new_id = insert_record('monit_accreditation', $accreditation))	{
						error(get_string('errorincreatingindicator','block_monitoring'), $redirlink);
				   }

			  }
		  } else {
				$strmessagesave = '';
		  }
		  
		  if (isset($rec->save))		{
				redirect($redirlink, $strmessagesave, 0);
		  }	

		  if (isset($rec->prevcrit))		{
		   	  $cid = get_next_id_bycircle ('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou", $cid, '-');
	    	  $indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, '', 'id', 0, 1);
	    	  $indicator = current($indicators);
	    	  // print_r($indicator); echo '<hr>'; exit();
	  		  redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid={$indicator->id}&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou", $strmessagesave, 0);
		  } else if (isset($rec->nextcrit))		{
			  $cid = get_next_id_bycircle ('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou", $cid, '+');
	    	  $indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, '', 'id', 0, 1);
	    	  $indicator = current($indicators);
	    	  // print_r($indicator); echo '<hr>'; exit();
	  		  redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid={$indicator->id}&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou", $strmessagesave, 0);
		  } else if (isset($rec->nextind))  {
  		   	  $iid = get_next_id_bycircle ('monit_accr_indicator', "criteriaid=$cid", $iid, '+');
	   	      redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou", $strmessagesave, 0);
          } else if (isset($rec->prevind))  {
   		   	  $iid = get_next_id_bycircle ('monit_accr_indicator', "criteriaid=$cid", $iid, '-');
	   	      redirect("editindicator.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou", $strmessagesave, 0);
          }
	   }
    }

	$indicator =  get_record('monit_accr_indicator', 'id', $iid);

	$estimates = get_records('monit_accr_estimates', 'indicatorid', $iid);

   // $text = $indicator->num . '. ' . $indicator->name;

	switch($type_ou)	{
		case 0:	$school = get_record('monit_school', 'id', $sid);
				print_heading($straccreditation.': '.$school->name, "center", 3);
		break;
		case 3: $udod = get_record('monit_udod', 'id', $udodid);
				print_heading($straccreditation.': '.$udod->name, "center", 3);
		break;
		case 1: $dou = get_record('monit_education', 'id', $douid);
				print_heading($straccreditation.': '.$dou->name, "center", 3);
		break;
	}

	$strcriter = $strcriteriagroup . '. ' . $criteria->name;
	print_heading($strcriter, "center", 4);

   	// print_heading($strindicator.' №'.$indicator->num, 'center', 5);

    print_simple_box_start('center', '80%', 'white');

    echo "<i><b>$strindicator №{$indicator->num}. $indicator->name</b></i><p></p>";

    foreach ($estimates as $estimate) {
        $answerchecked[$estimate->mark] = '';
    }

    if ($accr = get_record_select('monit_accreditation', $strsql))	{
        $answerchecked[$accr->mark] = 'checked="checked"';
        $currentmark =  $accr->mark;
    } else {
		$answerchecked[0] = 'checked="checked"';
		$currentmark = 0;
    }

    echo '<form enctype="multipart/form-data" name=form method=post action=editindicator.php>';
    echo '<table cellpadding=10 cellspacing=10 align=center>';

	if (count($estimates) > 1)	{
	    foreach ($estimates as $estimate) {

	    	   if ($estimate->mark == 0) {
		     	   $strb = 'баллов';
			   } else if ($estimate->mark == 1) {
		     	   $strb = 'балл';
			   } else {
		    	   $strb = 'балла';
		       }
	 		   $text = "&nbsp;&nbsp;$estimate->name ($estimate->mark $strb)";

	           echo "<tr><td>";

	           if ($answerchecked[$estimate->mark]) {
	    		    echo " <strong><font color='green'>";
	           }

		       echo "<input type=radio name=estimate value=\"".$estimate->mark."\" ".$answerchecked[$estimate->mark]." alt=\"".$text."\" />";
	           echo $text;

	           if ($answerchecked[$estimate->mark]) {
	    		    echo "</font></strong>";
	           }
	    }
	} else {
		$estimate = end($estimates);
		$strb =	slovo_ballov($estimate->mark);
		echo "<tr><td>";
		echo " <strong><font color='green'>";
		echo "&nbsp;&nbsp;$estimate->name (максимальное количество - $estimate->mark $strb): ";
		echo "<INPUT maxLength=3 size=3 name=estimate  value='$currentmark'>";
		echo "</font></strong>";
	}    
	echo '</td></tr><tr><td align=center>';


    $CFG->maxbytes = MAX_SCAN_COPY_SIZE; 

    $struploadafile = get_string('loadfiledocs', 'block_mou_att');
    $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

	echo "<p>$struploadafile($strmaxsize):</p>";
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);

	echo '</td></tr><tr><td align=center>';

	$strdelete   = get_string('delete');
	print_string('loadedfiledocs', 'block_mou_att');
	if ($yid == 3)	{
		$filearea = "0/$folder/$id/$iid";
	} else {
		$filearea = "0/$folder/$rid/$id/$iid";
	}	
    if ($basedir = make_upload_directory($filearea))   {
        if ($files = get_directory_list($basedir)) {
            require_once($CFG->libdir.'/filelib.php');
            $output = '';
            foreach ($files as $key => $file) {
                $icon = mimeinfo('icon', $file);
                if ($CFG->slasharguments) {
                    $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
                } else {
                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
                }

                $output .= '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                        '<a href="'.$ffurl.'" >'.$file.'</a>';

				if (!$staffview_operator && !$staffview_rayon_operator)	  {
                	$delurl  = "delete.php?rid=$rid&amp;sid=$sid&amp;iid=$iid&amp;cid=$cid&amp;yid=$yid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou&amp;file=$file";
                	$output .= '<a href="'.$delurl.'">&nbsp;' .'<img title="'.$strdelete.'" src="'.$CFG->pixpath.'/t/delete.gif" class="iconsmall" alt="" /></a><br /> ';
                } else {
                	$output .= '<br /> ';
                }
            }
        } else {
        	$output = '<i>' . get_string('isabscent', 'block_mou_att') . '</i>' ;
        }
    }

//    echo '<div class="files">'.$output.'</div>';
  	echo $output;

	echo '</td></tr></table>';

?>
	
	
	
	<p></p>
	<center>
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="iid" value="<?php echo $iid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
	<input type="hidden" name="num" value="<?php echo $indicator->num ?>" />
	<input type="hidden" name="yid" value="<?php echo $yid ?>" />
	<input type="hidden" name="type_ou" value="<?php echo $type_ou ?>" />
	<input type="hidden" name="udodid" value="<?php echo $udodid ?>" />
	<input type="hidden" name="douid" value="<?php echo $douid ?>" />
	<input type="hidden" name="action" value="copy" />
	<table align="center">
	<tr>
		<td align="center">
		<input type="submit" name=prevind value="<?php print_string('prevcriteria', 'block_mou_att') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=save value="<?php  print_string('criterions', 'block_mou_att') ?>" />
		</td>
		<td align="center">
		<input type="submit" name=nextind value="<?php print_string('nextcriteria', 'block_mou_att') ?>" />
		</td>
	</tr>
		</table>
    </center>
<?php

    if (isset($indicator->description))	{
    	$isrc = get_string('informationsource', 'block_mou_att');
        // <p>&nbsp;</p>
    	echo '<p align=right><i><small><strong>' . $isrc . ': </strong>' . $indicator->description . '</small></i>';
    }
  	print_simple_box_end();
?>

	<table align="center">
	<tr>
		<td align="center">
		<input type="submit" name=prevcrit value="<?php print_string('prevgroupcriteria', 'block_mou_att') ?>" />
		</td>
		<td align="center">&nbsp;
		</td>
		<td align="center">
		<input type="submit" name=nextcrit value="<?php print_string('nextgroupcriteria', 'block_mou_att') ?>" />
		</td>
	</tr>
	</table>
    </form>

<?php
    print_footer();


?>

