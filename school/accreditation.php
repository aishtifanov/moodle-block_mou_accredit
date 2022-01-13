<?php // $Id: accreditation.php,v 1.4 2009/12/16 10:46:50 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_accredit.php');

    $rid = optional_param('rid', '0', PARAM_INT);   // Rayon id
    $sid = optional_param('sid', '0', PARAM_INT);	// School id
    $yid = optional_param('yid', '0', PARAM_INT);       // Year id
    $mid = optional_param('mid', '0', PARAM_INT);       // Remark id
  	$action = optional_param('action', '');       // action
    $ataba = optional_param('ataba', 'acr');
    $tab = '';

    if ($yid == 0)	{
	    $yid = get_current_edu_year_id();
    }

    if ($action == 'ok')	{
       	  set_field('monit_accr_remark', 'status', 1, 'id', $mid, 'schoolid', $sid);
    } else if ($action == 'break')	{
       	  set_field('monit_accr_remark', 'status', 0, 'id', $mid, 'schoolid', $sid);
    }

    // print_r ($action);
    if ($action == 'infcard') {
		form_download($rid, $sid, $yid, $action);
        exit();
	} else  if ($action == 'expertzakl') {
		form_download($rid, $sid, $yid, $action);
        exit();
	}


	$admin_is = isadmin();
	$region_operator_is = ismonitoperator('staff');
	$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
	$school_operator_is = ismonitoperator('school', 0, $rid, $sid);
	if (!$admin_is && !$region_operator_is && !$rayon_operator_is && !$school_operator_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	if (!$admin_is && !$region_operator_is && !$rayon_operator_is && $school_operator_is)  {
	      if ($school = get_record_sql ("SELECT id, uniqueconstcode FROM {$CFG->prefix}monit_school
	                                     WHERE rayonid=$rid AND uniqueconstcode=$sid AND yearid=$yid"))	{
	     		$sid = $school->id;
	      }
	}


	$staffview_operator = isstaffviewoperator();

    $straccreditation = get_string('accreditation', 'block_monitoring');
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

    $strtitle = get_string('schools', 'block_monitoring');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

    // add_to_log(SITEID, 'monit', 'school view', 'school.php?id='.SITEID, $strschool);

	if ($action == 'upload')	{

          $dir = '1/schools/'.$sid;

          require_once($CFG->dirroot.'/lib/uploadlib.php');

          $um = new upload_manager('newfile',true,false,1,false,2097152);

          if ($um->process_file_uploads($dir))  {
	          // $newfile_name = $um->get_new_filename();
              print_heading(get_string('uploadedfile'), 'center', 4);
          } else {
	          notify(get_string("uploaderror", "assignment")); //submitting not allowed!
          }
	}


		if ($admin_is  || $region_operator_is )	 	{
			echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
			listbox_rayons("accreditation.php?tab=$tab&amp;ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;rid=", $rid);
			listbox_schools("accreditation.php?tab=$tab&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);
			echo '</table>';

		} else if ($rayon_operator_is) 		{
			echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
			listbox_schools("accreditation.php?tab=$tab&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=", $rid, $sid, $yid);
			echo '</table>';
		}

		if ($sid == 0) {
		    print_footer();
		 	exit();
		}


		if ($rayon_operator_is && $rayon_operator_is != $rid)  {
			notify(get_string('selectownrayon', 'block_monitoring'));
		    print_footer();
			exit();
		}

	    $school = get_record('monit_school', 'id', $sid);
		if ($rid == 0 &&  $sid != 0) {
			$rid = $school->rayonid;
		}

	    $curryearid = get_current_edu_year_id();
	    if ($yid == 0)	{
	    	$yid = $curryearid;
	    }


		print_tabs_years_link_accredit($rid, $sid, $yid, $ataba);


  		$toprow = array();
	   $toprow[] = new tabobject('acr', "accreditation.php?tab=$tab&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;ataba=acr",
 	               get_string('accreditation', 'block_monitoring'));
	   $toprow[] = new tabobject('infcard', "accreditation.php?tab=$tab&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;ataba=infcard",
 	               get_string('ainfcard', 'block_monitoring'));
 	               /*
	   $toprow[] = new tabobject('remark', "accreditation.php?tab=$tab&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;ataba=remark",
 	               get_string('remarks', 'block_monitoring'));
		*/ 	               
	   $tabs = array($toprow);

 	   print_tabs($tabs, $ataba, NULL, NULL);

		switch ($ataba)	{
			case 'acr':
				print_heading($straccreditation.': '.$school->name, "center", 3);


				$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
						   FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
						   WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid";

			 	$sum = 0.0;

			    if ($rec = get_record_sql($strsql))  {
					$sum = $rec->sum;
				}

		        $sum += get_olimpiad_mark($sid);
		        $sum -= get_olimpiad_estimate($sid);

				$proc = number_format($sum/120*100, 2, ',', '');
			    $proc .= '%';

			   	$strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $sum . ' (' . $proc . ')';
				print_heading($strtotlamark, 'center', 4);

			    $straction = get_string('action', 'block_monitoring');
			    $table->head  = array ('№', get_string('criteria', 'block_monitoring'),
				    						get_string('mark', 'block_monitoring'),
				    						get_string('maxmark', 'block_monitoring'),
				    						'%',
				    						$straction);
			    $table->align = array ('center', 'left', 'center', 'center', 'center', 'center');
			    $table->class = 'moutable';

				$criterions =  get_records('monit_accr_criteria', 'yearid', $yid);

			    if(!empty($criterions)) {

			          foreach ($criterions as $criteria) {

							$title = get_string('indicators','block_monitoring');
							$strlinkupdate = "<a title=\"$title\" href=\"indicators.php?rid=$rid&amp;sid=$sid&amp;cid={$criteria->id}\">";

							$criterianame = $strlinkupdate . "<strong>$criteria->name</strong></a>&nbsp;";
							$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/report.gif\" alt=\"$title\" /></a>&nbsp;";
/*
		                    if (!$staffview_operator)	{
								$title = get_string('clearindicators','block_monitoring');
								$strlinkupdate .= "<a title=\"$title\" href=\"clearindicator.php?rid=$rid&amp;sid=$sid&amp;cid={$criteria->id}\">";
								$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/monitoring/i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
							}
*/
							$strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
									   FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
									   WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid AND {$CFG->prefix}monit_accreditation.criteriaid = {$criteria->id}";

						 	$sum = '-';
						    if ($rec = get_record_sql($strsql))  {
								$sum = $rec->sum;
							}

		                    /*
							echo $sum . '<br>';

					        $accrsss = get_records_sql("SELECT id, mark  FROM {$CFG->prefix}monit_accreditation
					                                    WHERE schoolid=$sid AND criteriaid = {$criteria->id}");
					        print_r ($accrsss);
							echo '<hr>';
		                    */

		                    $maxmark = count_records('monit_accr_indicator', 'criteriaid', $criteria->id) * 2;

		                    if ($criteria->id == 1) {
		                    	$maxmark -=  2;  // za olimpiadu
		                    	$maxmark += 20;  // za olimpiadu
					  	  	    $sum += get_olimpiad_mark($sid);
		  				        $sum -= get_olimpiad_estimate($sid);
		                   }

							$proc = number_format($sum/$maxmark*100, 2, ',', '');
						    $proc .= '%';

			       			$table->data[] = array ($criteria->num . '.', $criterianame, $sum, $maxmark, $proc, $strlinkupdate);
			          }
			          print_color_table($table);
			    }

			    print_simple_box_start('center', '50%', 'white');
			   	print_heading($strtotlamark, 'center', 4);

				echo '<small>Максимальное количество баллов – 120.<br> Учреждение может быть аккредитовано на 5 лет, если набирает:<br>';
				echo '- гимназия, лицей, школа с углубленным изучением отдельных предметов – 80-100% от максимального количества баллов;<br>';
				echo '- средняя общеобразовательная школа – 60-80% от максимального количества баллов;<br>';
				echo '- основная общеобразовательная школа – 40-60% от максимального количества баллов;<br>';
				echo '- начальная общеобразовательная школа – 30-40% от максимального количества баллов.</small>';

			    // print_string('accreditation_result', 'block_monitoring');

			    print_simple_box_end();
			break;

			case 'infcard':

		        $infcard = get_string('infcard', 'block_monitoring');
		        $expertzakl = get_string('expertzakl', 'block_monitoring');

				print_simple_box_start('center', '50%', 'white');
			    echo '<table border=0 align=center> <tr valign="top">';
				echo "<td align=center><form name='downloadinfcard' method='post' action='accreditation.php?tab=$tab&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;action=infcard'>";
			 	echo "<input type='submit' name='infcard' value='".$infcard."'>";
				echo "</form></td>";
				echo "<td align=center><form name='downloadexpzakl' method='post' action='accreditation.php?tab=$tab&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;action=expertzakl'>";
			 	echo "<input type='submit' name='expertzakl' value='".$expertzakl."'>";
				echo "</form></td></tr></table>";
				
		        //echo '<hr>';
			    print_simple_box_end();
	
			    print_simple_box_start('center', '50%', 'white');
				echo '<small><i>Замечание: заполненную информационную карту высылать по адресу litsenz@belnet.ru.<br>';
				echo 'В названии файла должно присутствовать название школы и района.<br>';
			    print_simple_box_end();
	
			    print_simple_box_start('center', '50%', 'white');
		//		echo '<B><p align=center><a href="' . $CFG->wwwroot. '/mod/assignment/view.php?id=156">Загрузить заполненную информационную карту в систему ЭМОУ</a>';
				print_heading("Загрузка заполненной информационной карты в систему", "center", 4);
?>				
				</i><p>1. Заполните информационную карту.</p>
				<p>2. Сохраните информационную карту в файле, в имени которого должны присутствовать название школы и района.
				<strong>Внимание! В имени файла разрешается использовать только буквы латинского алфавита и цифры.
				 Например, MOU Razumenskaya SOSH 1 Belgorodscii rayon.doc</strong></p>
				<p>3. Полученный файл необходимо «загрузить» в систему ЭМОУ. Для этого необходимо выполнить следующие действия:
				<br />- на данной странице нажмите на кнопку &quot;Обзор&quot; и в открывшемся диалоговом окне «Выбор файлов»
				выберите документ с заполненной информационной картой и нажмите на кнопку &quot;Открыть&quot;;
				<br />- убедитесь, что в строке для отправки файла правильно указан путь к документу с информационной картой,
				и нажмите на кнопку &quot;Отправить&quot;. </p>
<?php				
		        $CFG->maxbytes = 2097152;

		        $struploadafile = "Загрузить документ с заполненной информационной картой";// get_string("uploadafile");
		        $strmaxsize = get_string("maxsize", "", display_size($CFG->maxbytes));

		        echo '<p><div style="text-align:center">';
		        echo '<form enctype="multipart/form-data" method="post" action="accreditation.php">';
		        echo '<fieldset class="invisiblefieldset">';
		        echo "<p>$struploadafile <br>($strmaxsize)</p>";
		        echo '<input type="hidden" name="rid" value="'.$rid.'" />';
		        echo '<input type="hidden" name="sid" value="'.$sid.'" />';
		        echo '<input type="hidden" name="yid" value="'.$yid.'" />';
		        echo '<input type="hidden" name="action" value="upload" />';
		        require_once($CFG->libdir.'/uploadlib.php');
		        upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);
		        echo '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
		        echo '</fieldset>';
		        echo '</form>';
		        echo '</div>';

				print_heading("Загруженная информационная карта:", "center", 4);
				$filearea = '1/schools/'.$sid;
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
		                            '<a href="'.$ffurl.'" >'.$file.'</a><br />';
		                }
		            } else {
		            	$output = get_string('no');
		            }
		        }

		        echo '<div class="files">'.$output.'</div>';

			    print_simple_box_end();
			break;
			case 'remark':

			    $table->head  = array (get_string('status', 'block_monitoring'),
			    						get_string('remarks', 'block_monitoring'),
				    					get_string('action', 'block_monitoring'));
			    $table->align = array ('center', 'left', 'center');
			    $table->class = 'moutable';
			   	$table->width = '60%';
		        $table->size = array ('20%', '50%', '10%');


				$remarks =  get_records('monit_accr_remark', 'schoolid', $sid, 'id');

			    if(!empty($remarks)) {

			          foreach ($remarks as $remark) {
			          	    $mid = $remark->id;

//		                    if (!$staffview_operator)	{
							if ($admin_is || $region_operator_is || $rayon_operator_is) 	{
								$title = get_string('editremark','block_monitoring');
								$strlinkupdate = "<a title=\"$title\" href=\"addremark.php?mode=edit&amp;sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;mid=$mid\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('setokremark', 'block_monitoring');
								$strlinkupdate .= "<a title=\"$title\" href=\"accreditation.php?action=ok&amp;tab=$tab&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;mid=$mid\">";
								$strlinkupdate .=  "<img src=\"{$CFG->pixpath}/i/tick_green_big.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('breakremark','block_monitoring');
								$strlinkupdate .= "<a title=\"$title\" href=\"accreditation.php?mid=$mid&amp;action=break&amp;tab=$tab&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=$sid\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/minus.gif\" alt=\"$title\" /></a>&nbsp;";

								$title = get_string('deleteremark','block_monitoring');
							    $strlinkupdate .= "<a title=\"$title\" href=\"delremark.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;mid=$mid\">";
								$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
							} else $strlinkupdate = '-';

							$strformrkpu_status = get_string('accrstatus'.$remark->status, "block_monitoring");
							$strcolor = get_string('accrstatus'.$remark->status.'color',"block_monitoring");

			       			$table->data[] = array ($strformrkpu_status, $remark->name, $strlinkupdate);
		          			$table->bgcolor[] = array ($strcolor);
			          }
			          print_color_table($table);

			    }

			    else   {
			    	// notice(get_string('remarknotfound', 'block_monitoring'), "accreditation.php?tab=$tab&amp;rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;ataba=acr");
					notify(get_string('remarknotfound', 'block_monitoring'));
			    }

				if  (($admin_is || $region_operator_is || $rayon_operator_is) && (!$staffview_operator && !$staffview_rayon_operator)) {
				    $options = array('rid' => $rid, 'sid' => $sid, 'yid' => $yid, 'mode' => 'new');
					echo '<table align="center" border=0><tr><td>';
				    print_single_button("addremark.php", $options, get_string('addremark','block_monitoring'));
					echo '</td></tr></table>';
				}

			break;
		}
	    print_footer();





function get_olimpiad_mark($sid)
{
  	$plusum = $plusum1 = $plusum2 = 0;

   	if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', 1, 'indicatorid', 17))	 {
   		// $plusum = $accr->mark;
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
  	  }
	}
	if ($plusum > 20) $plusum = 20;

	return $plusum;
}


function get_olimpiad_estimate($sid)
{
  	$plusum = 0;

   	if ($accr = get_record('monit_accreditation', 'schoolid', $sid, 'criteriaid', 1, 'indicatorid', 17))	 {
   		$plusum = $accr->mark;
	}

	return $plusum;
}


function form_download($rid, $sid, $yid, $action)
{
	global $CFG;

	$textlib = textlib_get_instance();

	$fp = fopen($action.'.doc', "r");
//	if (
	$fstat = fstat($fp);
	$buffer = fread($fp, $fstat['size']);

//    echo $buffer;
    switch ($action)	{
    	case 'infcard':
    			$school =  get_record_sql("SELECT *  FROM {$CFG->prefix}monit_school
					       				   WHERE rayonid=$rid AND id=$sid AND isclosing=0 AND yearid=$yid");

			 	$buffer = str_replace('name', $textlib->convert($school->name, 'utf-8', 'windows-1251'), $buffer);
				$buffer = str_replace('realaddress', $textlib->convert($school->realaddress, 'utf-8', 'windows-1251'), $buffer);
				$buffer = str_replace('phones', $school->phones, $buffer);
				$buffer = str_replace('fax', $school->fax, $buffer);
				$buffer = str_replace('email', $school->email, $buffer);

				$buffer = str_replace('inn', $school->inn, $buffer);
				$buffer = str_replace('kpp', $school->kpp, $buffer);
				$buffer = str_replace('okpo', $school->okpo, $buffer);
				$buffer = str_replace('okato', $school->okato, $buffer);
				$buffer = str_replace('okogu', $school->okogu, $buffer);
				$buffer = str_replace('okfs', $school->okfs, $buffer);
				$buffer = str_replace('okved', $school->okved, $buffer);

  				$buffer = str_replace('numlicense', $textlib->convert($school->numlicense, 'utf-8', 'windows-1251'), $buffer);
  				$buffer = str_replace('regnumlicense', $textlib->convert($school->regnumlicense, 'utf-8', 'windows-1251'), $buffer);

				$startdatelicense = date('d.m.Y',$school->startdatelicense);
				$buffer = str_replace('datelicens', $startdatelicense, $buffer);

  				$buffer = str_replace('numcertificate', $textlib->convert($school->numcertificate, 'utf-8', 'windows-1251'), $buffer);
  				$buffer = str_replace('regnumcertificate', $textlib->convert($school->regnumcertificate, 'utf-8', 'windows-1251'), $buffer);


				$temp =  get_record_sql("select name from {$CFG->prefix}monit_school_type where id=$school->typeinstitution");
				$buffer = str_replace('klass', $textlib->convert($temp->name, 'utf-8', 'windows-1251'), $buffer);

				$temp =  get_record_sql("select name from {$CFG->prefix}monit_school_category where id=$school->stateinstitution");
				$buffer = str_replace('typeeduc', $textlib->convert($temp->name, 'utf-8', 'windows-1251'), $buffer);
    	break;

    	case 'expertzakl':
    	break;
    }

    $fn = $action.'_'.$rid.'_'.$sid.'.doc';

	header("Content-type: application/vnd.ms-word");
	header("Content-Disposition: attachment; filename=\"{$fn}\"");
	header("Expires: 0");
	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");

	print $buffer;
}


?>


