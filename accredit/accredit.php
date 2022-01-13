<?php // $Id: accredit.php,v 1.17 2013/06/08 05:44:40 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_accredit.php');


	define("MAXSUM_SCHOOL", 202);
	 
    $rid = optional_param('rid', 0, PARAM_INT);   // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);	// School id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $yid = optional_param('yid', 0, PARAM_INT);       // Year id
    $ataba = optional_param('ataba', 'acr');
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
   	$numdoc  = optional_param('numdoc', 0, PARAM_INT); 
	$action = optional_param('action', '');       // action          

    $curryearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryearid;
    }

    require_once('../authall.inc.php');

    $straccreditation = get_string('title_accredit', 'block_mou_att');
    $strtitle = get_string('criteriagroups', 'block_mou_att');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

/*
	if (!$admin_is && !$region_operator_is) {
        error(get_string('accesstemporarylock', 'block_mou_ege'));
	}
*/

	if ($admin_is  || $region_operator_is )	 	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_rayons("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=", $rid);
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
			break;
		}
		echo '</table>';
	} else if ($rayon_operator_is || $dod_rayon_operator_is) 		{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
			break;
		}
		echo '</table>';
	}
	/*
	else if ($school_operator_is) {

		print_heading($strtitle.': '.$school->name, "center", 3);
	} else if  ($dod_school_operator_is)	{

		print_heading($strtitle.': '.$school->name, "center", 3);
	}
	*/

	if (!check_optional_param($type_ou, $rid, $sid, $udodid, $douid))	{
	    print_footer();
	 	exit();
	}

    // if ($type_ou == 0)	
	print_tabs_years_link_accredit($type_ou, $rid, $sid, $udodid, $douid, $yid, $ataba);
	
    
    if ($type_ou == 0 && $yid >= 5) {

    	if ($action == 'clear') 	{
    	    $dir = $CFG->dataroot."/1/school/$sid/$numdoc";
            remove_dir($dir, true);
    	}
        
    	if ($action == 'upload')	{
              $dir = "1/school/$sid/$numdoc";
              require_once($CFG->dirroot.'/lib/uploadlib.php');
              $um = new upload_manager('doc_'.$sid.'_'.$numdoc, true, false, 1, false,$CFG->maxbytes);
    
              if ($um->process_file_uploads($dir))  {
    	          // $newfile_name = $um->get_new_filename();
                  print_heading(get_string('uploadedfile'), 'center', 4);
              } else {
    	          notify(get_string("uploaderror", "assignment")); //submitting not allowed!
              }
    	}
        
	    $table = table_import_documents($yid,  $rid,  $sid, $type_ou);
        print_color_table($table);
        
        $strmaxsize = display_size($CFG->maxbytes);
	    echo "<div align=center><i>Замечание: максимальный размер одного документа $strmaxsize.</i></div>";
 
        print_footer();
        exit();
    }
    
	// echo '<div align=center> <font color=red><b>ВНИМАНИЕ! 5 октября 2010 г. опубликованы измененные критерии аккредитации ОУ.<br> Все образовательные учреждения, заполнившие критерии до 5 октября 2010 г., смотрите вкладку 2009/2010 уч.года.</b></font></div>';

    $currenttab = 'acr';
    include('tabs.php');


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


	$table = table_accredit($rid, $sid, $yid, $udodid, $douid, $type_ou);
	print_color_table($table);

    print_simple_box_start('center', '70%', 'white');

	switch($type_ou)	{
		case 0: // $maxsum = get_max_estimate($yid, $type_ou); // , $sid); // 228
				$maxsum = MAXSUM_SCHOOL;
				echo "<small><b>Максимальное количество баллов – $maxsum*</b>.<br> Учреждение может быть аккредитовано на 5 лет, если набирает:<br>";
				echo '- гимназия, лицей, школа с углубленным изучением отдельных предметов – 80-100% от максимального количества баллов;<br>';
				echo '- средняя общеобразовательная школа – 60-80% от максимального количества баллов;<br>';
				echo '- основная общеобразовательная школа – 40-60% от максимального количества баллов;<br>';
				echo '- начальная общеобразовательная школа – 30-40% от максимального количества баллов;<br>';
				echo '- общеобразовательные школы-интернаты начального общего образования – 30-40% от максимального количества баллов;<br>';
				echo '- общеобразовательные школы-интернаты основного общего образования – 40-60% от максимального количества баллов;<br>';
				echo '- общеобразовательные школы-интернаты среднего(полного) образования – 60-80% от максимального количества баллов;<br>';
				echo '- общеобразовательные школы-интернаты с углубленным изучением отдельных предметов, гимназии-интернаты, лицеи-интернаты  – 80-100% от максимума.</small>';
				
				echo '<p><p><small><i>* в группе критериев №5 максимальный балл равен 76, т.к. критерии 4а и 4б введены только для начальных и основных общеобразовательных школ.</i></small>';
		break;
		case 3:	$maxsum = get_max_estimate($yid, $type_ou); // , $udodid);
				echo "<small><b>Максимальное количество баллов – $maxsum (100%).</b><br> "; // 55
				echo 'от 41 балла до 55 баллов (75% - 100%)  - камерально<br>';
				echo 'ниже 41  баллов –  с выездом</small>';

		break;
		case 1: $maxsum = get_max_estimate($yid, $type_ou); // , $douid);
		/*
				echo "<small>1. Максимальное количество баллов по показателям и критериям  первого этапа - $maxsum.<br>"; // 81
				echo ' Если  общая сумма баллов по всем критериям - 60 баллов и выше - дошкольное учреждение проходит дальнейшие этапы государственной аккредитации в виде документарной экспертизы.<br>';
				echo '2. Дошкольные учреждения, повышающие статус, а также дошкольные учреждения - детский сад компенсирующего вида и центр развития ребенка подлежат обязательной выездной экспертизе</small>';
		*/		
		break;
	}

    print_simple_box_end();
    
    print_footer();



function table_accredit($rid, $sid, $yid, $udodid, $douid, $type_ou)
{
	global $CFG, $staffview_operator;

	switch($type_ou)	{
		case 0: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
			    FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
			    WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid";
			    // $maxsum = get_max_estimate($yid, $type_ou); // , $sid);
			    $maxsum = MAXSUM_SCHOOL;
		break;
		case 3: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
			    FROM {$CFG->prefix}monit_udod INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_udod.id = {$CFG->prefix}monit_accreditation.udodid
			    WHERE {$CFG->prefix}monit_accreditation.udodid = $udodid";
			    $maxsum = get_max_estimate($yid, $type_ou); //, $udodid);
		break;
		case 1: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
			    FROM {$CFG->prefix}monit_education INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_education.id = {$CFG->prefix}monit_accreditation.douid
			    WHERE {$CFG->prefix}monit_accreditation.douid = $douid";
			    $maxsum = get_max_estimate($yid, $type_ou); // , $douid);
		break;
	}
	$sum = 0.0;
	if ($rec = get_record_sql($strsql))  {
		$sum = $rec->sum;
	}

	if ($maxsum != 0)	{
		$proc = number_format($sum/$maxsum*100, 2, ',', '');
		$proc .= '%';
	} else {
		$proc = '0.0%';
	}	

	$strtotlamark = get_string('total_mark', 'block_monitoring') . ': ' . $sum . ' (' . $proc . ')';
	print_heading($strtotlamark, 'center', 4);

	$straction = get_string('action', 'block_monitoring');
	$table->head  = array ('№', get_string('criteriagroups', 'block_mou_att'),
							get_string('mark', 'block_monitoring'),
							get_string('maxmark', 'block_monitoring'),
							'%',
							$straction);
	$table->align = array ('center', 'left', 'center', 'center', 'center', 'center');
	$table->class = 'moutable';

	$criterions =  get_records_select('monit_accr_criteria', "yearid=$yid AND type_ou=$type_ou");

	if(!empty($criterions)) {

  		foreach ($criterions as $criteria) {

			$title = get_string('indicators','block_monitoring');
			$strlinkupdate = "<a title=\"$title\" href=\"indicators.php?yid=$yid&amp;rid=$rid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;cid={$criteria->id}&amp;type_ou=$type_ou\">";

			$criterianame = $strlinkupdate . "<strong>$criteria->name</strong></a>&nbsp;";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/report.gif\" alt=\"$title\" /></a>&nbsp;";

/*
	        if (!$staffview_operator)	{
				$title = get_string('clearindicators','block_monitoring');
				$strlinkupdate .= "<a title=\"$title\" href=\"clearindicator.php?yid=$yid&amp;rid=$rid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;cid={$criteria->id}&amp;type_ou=$type_ou\">";
				$strlinkupdate .= "<img src=\"{$CFG->wwwroot}/blocks/monitoring/i/goom.gif\" alt=\"$title\" /></a>&nbsp;";
			}
*/
			switch($type_ou)	{
				case 0: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
							   		FROM {$CFG->prefix}monit_school INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_school.id = {$CFG->prefix}monit_accreditation.schoolid
							   		WHERE {$CFG->prefix}monit_accreditation.schoolid = $sid AND {$CFG->prefix}monit_accreditation.criteriaid = {$criteria->id}";
				break;
				case 3: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
					    			FROM {$CFG->prefix}monit_udod INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_udod.id = {$CFG->prefix}monit_accreditation.udodid
								    WHERE {$CFG->prefix}monit_accreditation.udodid = $udodid AND {$CFG->prefix}monit_accreditation.criteriaid = {$criteria->id}";
				break;
				case 1: $strsql = "SELECT Sum({$CFG->prefix}monit_accreditation.mark) AS sum
					    			FROM {$CFG->prefix}monit_education INNER JOIN {$CFG->prefix}monit_accreditation ON {$CFG->prefix}monit_education.id = {$CFG->prefix}monit_accreditation.douid
					    			WHERE {$CFG->prefix}monit_accreditation.douid = $douid AND {$CFG->prefix}monit_accreditation.criteriaid = {$criteria->id}";
				break;
			}



		 	$sum = '-';
		    if ($rec = get_record_sql($strsql))  {
				$sum = $rec->sum;
			}


	        // $maxmark = count_records('monit_accr_indicator', 'criteriaid', $criteria->id) * 2;
			$indicators =  get_records('monit_accr_indicator', 'criteriaid', $criteria->id, 'num');
			$maxmark = 0;
			foreach ($indicators as $indicator) {					
	
				$strsql = "SELECT Max(ae.mark) AS max
						    FROM {$CFG->prefix}monit_accr_estimates ae INNER JOIN {$CFG->prefix}monit_accr_indicator ai ON ai.id = ae.indicatorid
						    WHERE ae.indicatorid = {$indicator->id}";
				
			    if ($amax = get_record_sql($strsql))	 {
					$maxmark += $amax->max;
				}
				// print_r($amax); echo '<hr>';
			}
	        
	        

			$proc = '-';
			if ($maxmark != 0)	{
				$proc = number_format($sum/$maxmark*100, 2, ',', '');
			    $proc .= '%';
			}

			$table->data[] = array ($criteria->num . '.', $criterianame, $sum, $maxmark, $proc, $strlinkupdate);
		}
	}

  	return $table;
}


function table_import_documents($yid, $rid, $sid, $type_ou)
{
    global $CFG;

    $table->head  = array ('№',  'Наименование документов и материалов', 
    							 get_string('loadedfiledocs','block_mou_att'),
    							 get_string('loaddoc','block_mou_att'),
    							 get_string('action','block_mou_ege'));
    $table->align = array ('center', 'left',  'left', 'center', 'center');
    $table->class = 'moutable';
  	$table->width = '90%';
    $table->size = array ('3%', '40%', '15%', '20%', '5%');
	$table->columnwidth = array (4, 10, 10, 10, 10, 10, 10, 10, 30, 10);
    $table->titles = array();
    $table->titles[] = get_string('disciplines_ege', 'block_mou_ege');
    $table->worksheetname = get_string('disciplines_ege', 'block_mou_ege');
    $table->titlesrows = array(30);
    $table->downloadfilename = 'publish_date_gia';

//	$disciplines = get_records ('school_discipline', 'curriculumid', $cid);
	$docs = array(  'Годовой календарный учебный график', 
                    'Договоры о взаимном сотрудничестве с другими образовательными учреждениями об организации предпрофильной подготовки, профильного обучения, реализации внеурочной деятельности',
                    'Справка о материально-техническом, учебно-методическом, информационно-техническом обеспечении образовательного процесса по форме (приложение № 1)',
                    'План учебно-воспитательной работы');

    $i = 1;
  
	foreach ($docs as $doc) {

    	$filearea = "1/school/$sid/$i";
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
            	$output = '(' . get_string('isabscent', 'block_mou_att') . ')';
            }
        }
    
        $name1 = 'doc_'.$sid.'_'.$i;
        $name2 = 'save'.$sid.'_'.$i;
        $name3 = 'Загрузить документ №'. $i;

	    $strload = '<form enctype="multipart/form-data" method="post" action="accredit.php">';
	    $strload .= '<input type="hidden" name="yid" value="'.$yid.'" />';
	    $strload .= '<input type="hidden" name="action" value="upload" />';
	    $strload .= '<input type="hidden" name="rid" value="'.$rid.'" />';
	    $strload .= '<input type="hidden" name="sid" value="'.$sid.'" />';                    
	    $strload .= '<input type="hidden" name="type_ou" value="'.$type_ou.'" />';
        $strload .= '<input type="hidden" name="numdoc" value="'.$i.'" />';
		$strload .= '<input type="hidden" name="MAX_FILE_SIZE" value="'. $CFG->maxbytes .'" />'."\n";
        $strload .= '<input type="file" size="40" name="'. $name1 .'" alt="'. $name1 .'" />'."\n";
		$strload .= '<input type="submit" name="'. $name2 .'" value="'. $name3 .'" />';
		$strload .= '</form>';

		$title = get_string('delete');
  	 	$strlinkupdate = "<a title=\"$title\" href=\"accredit.php?action=clear&yid=$yid&rid=$rid&sid=$sid&type_ou=$type_ou&numdoc=$i\">";
		$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

		$table->data[] = array ($i.'.', $doc, $output, $strload, $strlinkupdate);
        
        $i++;
    }
    
	return $table;
}

?>


