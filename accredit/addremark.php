<?PHP // $Id: addremark.php,v 1.3 2010/02/09 09:30:34 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $rid = required_param('rid', PARAM_INT);   // Rayon id
	$yid = required_param('yid', PARAM_INT);			// Year id
	$mid = optional_param('mid', 0, PARAM_INT);			// Remark id
	$sid = optional_param('sid', 0, PARAM_INT);	// School id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $type_ou  = optional_param('type_ou', 0, PARAM_INT);
	
	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

    $straccreditation = get_string('accreditation', 'block_monitoring');
    $strremarks = get_string('remarks', 'block_monitoring');
    if ($mode === "new" || $mode === "add" ) {
    	$straddremark = get_string('addremark','block_monitoring');
    } else {
    	$straddremark = get_string('updateremark','block_monitoring');
    }

	$breadcrumbs  = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> <a href=\"remark.php?type_ou=$type_ou&amp;ataba=remark&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid\">".get_string('remarks', 'block_monitoring').'</a>';
	$breadcrumbs .= " -> $straddremark";
    print_header("$SITE->shortname: $straddremark", $SITE->fullname, $breadcrumbs);

	$rec->schoolid = $sid;
	$rec->udodid = $udodid;
	$rec->douid = $douid;
	$rec->name = '';

	$redirlink = "remark.php?type_ou=$type_ou&amp;ataba=remark&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;douid=$douid&amp;udodid=$udodid";
	if ($mode === 'add')  {
		$rec->name = required_param('name');
		if (find_form_disc_errors($rec, $err) == 0) {
			// $rec->timemodified = time();
			if ($mid = insert_record('monit_accr_remark', $rec))		{
				 // add_to_log(1, 'school', 'one discipline added', "blocks/school/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('remarkadded','block_monitoring'), $redirlink);
			} else
				error(get_string('errorinaddingremark','block_monitoring'), $redirlink);
		}
		else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($mid > 0) 	{
			$remark = get_record('monit_accr_remark', 'id', $mid);
			$rec->id = $remark->id;
			$rec->name = $remark->name;
		}
	}
	else if ($mode === 'update')	{
		$rec->id = required_param('mid', PARAM_INT);
		$rec->name = required_param('name');

		if (find_form_disc_errors($rec, $err) == 0) {
			if (update_record('monit_accr_remark', $rec))	{
				 // add_to_log(1, 'school', 'discipline update', "blocks/school/curriculum/addiscipline.php?mode=2&amp;fid=$fid&amp;sid=$sid&amp;cid=$cid", $USER->lastname.' '.$USER->firstname);
				 notice(get_string('remarkupdate','block_monitoring'), $redirlink);
			} else  {
				error(get_string('errorinupdatingremark','block_monitoring'), $redirlink);
			}
		}
	}


	print_heading($straddremark, "center", 3);

    print_simple_box_start("center");

	if ($mode === 'new') $newmode='add';
	else 				 $newmode='update';

?>

<form name="addform" method="post" action="addremark.php">
<center>
<table cellpadding="5">
<tr valign="top">
    <td align="right"><b><?php  print_string('remark', 'block_monitoring') ?>:</b></td>
    <td align="left">
		<input type="text"  name="name" size="120" value="<?php p($rec->name) ?>" />
		<?php if (isset($err["name"])) formerr($err["name"]); ?>
    </td>
</tr>
</table>
<?php  // if (!isregionviewoperator() && !israyonviewoperator())  {  ?>
   <div align="center">
     <input type="hidden" name="mode" value="<?php echo $newmode ?>">
     <input type="hidden" name="rid" value="<?php echo $rid ?>">
     <input type="hidden" name="sid" value="<?php echo $sid ?>">
     <input type="hidden" name="yid" value="<?php echo $yid ?>">
     <input type="hidden" name="mid" value="<?php echo $mid ?>">
     <input type="hidden" name="douid" value="<?php echo $douid ?>">
     <input type="hidden" name="udodid" value="<?php echo $udodid ?>">	      
     <input type="hidden" name="type_ou" value="<?php echo $type_ou ?>">
 	 <input type="submit" name="adddisc" value="<?php print_string('savechanges')?>">
  </div>
<?php //  }  ?>
 </center>
</form>


<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_disc_errors(&$rec, &$err, $mode='add') {

    if (empty($rec->name)) {
            $err["name"] = get_string("missingname");
	}

    return count($err);
}

?>