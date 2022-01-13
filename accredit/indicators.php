<?php // $Id: indicators.php,v 1.8 2010/10/04 12:52:00 Shtifanov Exp $

    require_once("../../../config.php");
    require_once($CFG->libdir.'/filelib.php');    
	require_once('../../monitoring/lib.php');
 	require_once('../../mou_ege/lib_ege.php');
	require_once('../lib_accredit.php');	

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $cid = required_param('cid', PARAM_INT);          // Critetia id
	$yid = required_param('yid', PARAM_INT);       // Year id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
	$action  = optional_param('action', '');

	require_once('../authall.inc.php');
	
    if (!$criteria =  get_record('monit_accr_criteria', 'id', $cid, 'yearid', $yid, 'type_ou', $type_ou))	{
		add_to_log(1, 'editindicator.php', 'Unknown criteria', 'monit_accr_criteria', fullname($USER), '', $USER->id);		
		error('Unknown criteria.');
    }
	

	switch($type_ou)	{
		case 0:	$strsql = "SELECT Sum(a.mark) AS sum
			   FROM {$CFG->prefix}monit_school s, {$CFG->prefix}monit_accreditation  a
			   WHERE s.id = a.schoolid AND a.schoolid = $sid AND a.criteriaid = $cid";
		break;
		case 3:$strsql = "SELECT Sum(a.mark) AS sum
			   FROM {$CFG->prefix}monit_udod s, {$CFG->prefix}monit_accreditation  a
			   WHERE s.id = a.udodid AND a.udodid = $udodid AND a.criteriaid = $cid";
		break;
		case 1:  $strsql = "SELECT Sum(a.mark) AS sum
			   FROM {$CFG->prefix}monit_education s, {$CFG->prefix}monit_accreditation  a
			   WHERE s.id = a.douid AND a.douid = $douid AND a.criteriaid = $cid";
		break;
	}

 	$sum = '-';
    if ($rec = get_record_sql($strsql))  {
		$sum = $rec->sum;
	}


    if ($action == 'excel') {
   		$table = table_criteria($rid, $sid, $cid, $yid, $udodid, $douid, $type_ou);
		print_table_to_excel($table, 1);
        exit();
	}

	if ($action == 'word') {
   		$table = table_criteria($rid, $sid, $cid, $yid, $udodid, $douid, $type_ou);
		print_table_to_word($table, 1);
        exit();
    }    


	$eduyear = get_record('monit_years', 'id', $yid);
	$streduyear = get_string('uchyear', 'block_monitoring', $eduyear->name);
	
    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strtitle = get_string('criteriagroup', 'block_mou_att') . " ($streduyear)";

	$breadcrumbs  = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"accredit.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">".get_string('criteriagroups', 'block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);


	if ($rec = data_submitted())  {
		  if (isset($rec->prevcrit))		{
		   	  $cid = get_next_id_bycircle ('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou", $cid, '-');
		  } else if (isset($rec->nextcrit))		{
			  $cid = get_next_id_bycircle ('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou", $cid, '+');
		  } 
	}

    // add_to_log(SITEID, 'monit', 'school view', 'school.php?id='.SITEID, $strschool);


	if (!check_optional_param($type_ou, $rid, $sid, $udodid, $douid))	{
	    print_footer();
	 	exit();
	}

	print_heading($streduyear, "center", 1);

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


	$criteria =  get_record('monit_accr_criteria', 'id', $cid);

	$strcriter = get_string('criteriagroup', 'block_mou_att') . ' №' .  $criteria->num . '. ' . $criteria->name;
	print_heading($strcriter, "center", 3);


  	$strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $sum;
	print_heading($strtotlamark, 'center', 4);

	$table = table_criteria($rid, $sid, $cid, $yid, $udodid, $douid, $type_ou);
    print_color_table($table);

?>
	<table align="center">
    <form name=form method=post action=indicators.php>
	<input type="hidden" name="rid" value="<?php echo $rid ?>" />
	<input type="hidden" name="sid" value="<?php echo $sid ?>" />
	<input type="hidden" name="cid" value="<?php echo $cid ?>" />
	<input type="hidden" name="yid" value="<?php echo $yid ?>" />
	<input type="hidden" name="type_ou" value="<?php echo $type_ou ?>" />
	<input type="hidden" name="udodid" value="<?php echo $udodid ?>" />
	<input type="hidden" name="douid" value="<?php echo $douid ?>" />
	<input type="hidden" name="action" value="copy" />
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
    </form>
	</table>


	<table align="center">
	<tr><td>
			<form name="download" method="post" action="indicators.php">
				<input type="hidden" name="rid" value="<?php echo $rid ?>" />
				<input type="hidden" name="sid" value="<?php echo $sid ?>" />
				<input type="hidden" name="cid" value="<?php echo $cid ?>" />
				<input type="hidden" name="yid" value="<?php echo $yid ?>" />
				<input type="hidden" name="type_ou" value="<?php echo $type_ou ?>" />
				<input type="hidden" name="udodid" value="<?php echo $udodid ?>" />
				<input type="hidden" name="douid" value="<?php echo $douid ?>" />
				<input type="hidden" name="action" value="excel" />
				<input type="submit" name="downloadexcel" value="<?php print_string("downloadexcel")?>">
		    </form>
		</td>
<td>
			<form name="download" method="post" action="indicators.php">
				<input type="hidden" name="rid" value="<?php echo $rid ?>" />
				<input type="hidden" name="sid" value="<?php echo $sid ?>" />
				<input type="hidden" name="cid" value="<?php echo $cid ?>" />
				<input type="hidden" name="yid" value="<?php echo $yid ?>" />
				<input type="hidden" name="type_ou" value="<?php echo $type_ou ?>" />
				<input type="hidden" name="udodid" value="<?php echo $udodid ?>" />
				<input type="hidden" name="douid" value="<?php echo $douid ?>" />
				<input type="hidden" name="action" value="word" />
				<input type="submit" name="downloadexcel" value="<?php print_string("downloadword", 'block_mou_att')?>">
		    </form>
		</td>		
	</tr>
	</table>
<?php

    print_footer();


function table_criteria($rid, $sid, $cid, $yid, $udodid, $douid, $type_ou)
{
	global $CFG, $staffview_operator, $staffview_rayon_operator, $sum;

    $table->head  = array ('№', get_string('criteria', 'block_monitoring'),
	    						get_string('mark', 'block_monitoring'),
	    						get_string('action', 'block_monitoring'));
    $table->align = array ('left', 'left', 'center', 'center');
    $table->columnwidth = array (5, 70, 10, 5);
    $table->class = 'moutable';

    $straccreditation = get_string('accreditation', 'block_monitoring');
	switch($type_ou)	{
		case 0:	$school = get_record('monit_school', 'id', $sid);
				$table->titles[] = $straccreditation.': '.$school->name;
				$table->downloadfilename = 'accreditation_'.$rid . '_'. $sid;
		break;
		case 3: $udod = get_record('monit_udod', 'id', $udodid);
				$table->titles[] = $straccreditation.': '.$udod->name;
				$table->downloadfilename = 'accreditation_'.$rid . '_'. $udodid;
		break;
		case 1: $dou = get_record('monit_education', 'id', $douid);
				$table->titles[] = $straccreditation.': '.$dou->name;
				$table->downloadfilename = 'accreditation_'.$rid . '_'. $douid;
		break;
	}
 
 	$criteria =  get_record('monit_accr_criteria', 'id', $cid);
	$table->titles[] =  get_string('criteriagroup', 'block_mou_att') . ' №' .  $criteria->num . '. ' . $criteria->name;
  	$table->titles[] =  get_string('total_mark', 'block_monitoring') . ': ' . $sum;

    $table->titlesrows = array(30, 30, 30);
    $table->worksheetname = 'accreditation';
	


	$indicators =  get_records('monit_accr_indicator', 'criteriaid', $cid, 'num');
    if(!empty($indicators)) {

		  $sum = 0;

          foreach ($indicators as $indicator) {
				$iid = $indicator->id;
				
				$strindicator = ' ';
                if ($staffview_operator || $staffview_rayon_operator)	{
					$title = get_string('viewindicator','block_monitoring');
				} else {
					$title = get_string('editindicator','block_monitoring');
				}
	
				$link0 = "<a title=\"$title\" href=\"editindicator.php?rid=$rid&amp;sid=$sid&amp;cid=$cid&amp;yid=$yid&amp;iid=$iid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou\">";
				$strlinkupdate = $link0; 
				$criterianame = $strlinkupdate . "<strong>$indicator->name</strong></a>&nbsp;";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

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

				$mark = '-';
			    if ($accr = get_record_select('monit_accreditation', $strsql))	 {
					$mark = $accr->mark;
					$sum += $mark;

		       		if ($estimate = get_record('monit_accr_estimates', 'indicatorid', $iid, 'mark', $mark))	{
				        $strindicator = '&raquo; ' . $estimate->name;
		       		}
				}
				
				if ($yid == 3)	{
					$filearea = "0/$folder/$id/$iid";
				} else {
					$filearea = "0/$folder/$rid/$id/$iid";
				}	
				$basedir = $CFG->dataroot . '/' . $filearea;
		        if ($files = get_directory_list($basedir)) {
		            $output = '';
		            foreach ($files as $key => $file) {
		                $icon = mimeinfo('icon', $file);
		                if ($CFG->slasharguments) {
		                    $ffurl = "$CFG->wwwroot/file.php/$filearea/$file";
		                } else {
		                    $ffurl = "$CFG->wwwroot/file.php?file=/$filearea/$file";
		                }
		
		                $output .=  '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
		                        '<a href="'.$ffurl.'" >'.$file.'</a>';
		            }
		        } else {
		        	$output = '' ;
		        }
	        

				$strindicatorlink = $link0 . $indicator->name . '</a>';
	 		    if ($strindicator != ' ')	{
  		 		    $strindicator = $strindicatorlink . '<p><b>' . $strindicator . '';
    	 		    if ($output != '')	{
	  		 		    $strindicator .= " ($output)</b></p>";
		 		    } 	 		    
	 		    } else 	{
  		 		    $strindicator = $strindicatorlink;
	 		    }
 		    
       			$table->data[] = array ($indicator->num . '.', $strindicator, $mark, $strlinkupdate);
          }
      }

	  return $table;
}


?>


