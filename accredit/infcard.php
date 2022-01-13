<?php // $Id: infcard.php,v 1.6 2010/09/08 11:58:57 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
	require_once('../lib_accredit.php');

    $rid = optional_param('rid', 0, PARAM_INT);   // Rayon id
    $sid = optional_param('sid', 0, PARAM_INT);	// School id
    $udodid = optional_param('udodid', 0, PARAM_INT);    // Udod id
    $douid = optional_param('douid', 0, PARAM_INT);    // DOU id
    $yid = optional_param('yid', 3, PARAM_INT);       // Year id
    $ataba = optional_param('ataba', 'acr');
   	$type_ou  = optional_param('type_ou', 0, PARAM_INT);
   	$action = optional_param('action', '');       // action

    $curryearid = get_current_edu_year_id();
    if ($yid == 0)	{
    	$yid = $curryearid;
    }

    require_once('../authall.inc.php');

    if ($action == 'infcard') {
		form_download($rid, $sid, $yid, $action, $type_ou, $udodid, $douid);
        exit();
	} else  if ($action == 'expertzakl') {
		form_download($rid, $sid, $yid, $action, $type_ou, $udodid, $douid);
        exit();
	}


    $straccreditation = get_string('title_accredit', 'block_mou_att');
    $strtitle = get_string('ainfcard', 'block_monitoring');

    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit','block_mou_att').'</a>';
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	 
	if ($admin_is  || $region_operator_is )	 	{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		listbox_rayons("accredit.php?type_ou=$type_ou&amp;ataba=$ataba&amp;sid=0&amp;yid=$yid&amp;udodid=0&amp;rid=", $rid);
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
			break;
		}
		echo '</table>';
	} else if ($rayon_operator_is || $dod_rayon_operator_is) 		{
		echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
		switch($type_ou)	{
			case 0:	$school = get_record('monit_school', 'id', $sid);
					listbox_schools("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;udodid=0&amp;sid=", $rid, $sid, $yid);
			break;
			case 3: listbox_udods("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;udodid=", $rid, $udodid, $yid);
			break;
			case 1: listbox_dous("infcard.php?type_ou=$type_ou&amp;ataba=$ataba&amp;rid=$rid&amp;yid=$yid&amp;sid=0&amp;douid=", $rid, $douid, $yid);
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


	if ($sid == 0 && $udodid == 0 && $douid == 0) {
	    print_footer();
	 	exit();
	}

	if ($rayon_operator_is && $rayon_operator_is != $rid)  {
		notify(get_string('selectownrayon', 'block_monitoring'));
	    print_footer();
		exit();
	}

    // print_tabs_years_link_accredit($type_ou, $rid, $sid, $udodid, $douid, $yid, $ataba);

    $currenttab = 'infcard';
    include('tabs.php');

	
	switch($type_ou)	{
		case 0:	// notice(get_string('vstadii', 'block_mou_att'), "accredit.php?$link ");
				$school = get_record('monit_school', 'id', $sid);
				print_heading($straccreditation.': '.$school->name, "center", 3);
				$folder = 'school';
				$id = $sid;
		break;
		case 3: // notice(get_string('vstadii', 'block_mou_att'), "accredit.php?$link ");
				$udod = get_record('monit_udod', 'id', $udodid);
				print_heading($straccreditation.': '.$udod->name, "center", 3);
				$folder = 'udod';
				$id = $udodid;
		break;
		case 1: $dou = get_record('monit_education', 'id', $douid);
				print_heading($straccreditation.': '.$dou->name, "center", 3);
				$folder = 'dou';
				$id = $douid;
		break;
	}

	if ($action == 'upload')	{

          $dir = "1/$folder/$id";

          require_once($CFG->dirroot.'/lib/uploadlib.php');

          $um = new upload_manager('newfile',true,false,1,false,2097152);

          if ($um->process_file_uploads($dir))  {
	          // $newfile_name = $um->get_new_filename();
              print_heading(get_string('uploadedfile'), 'center', 4);
          } else {
	          notify(get_string("uploaderror", "assignment")); //submitting not allowed!
          }
	}


	$redirlink = "infcard.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou";
	
    $infcard = get_string('infcard', 'block_monitoring');
    $expertzakl = get_string('expertzakl', 'block_monitoring');

	print_simple_box_start('center', '50%', 'white');
    echo '<table border=0 align=center> <tr valign="top">';
	echo "<td align=center><form name='downloadinfcard' method='post' action='$redirlink&amp;action=infcard'>";
 	echo "<input type='submit' name='infcard' value='".$infcard."'>";
	echo "</form></td>";
	/*
	echo "<td align=center><form name='downloadexpzakl' method='post' action='$redirlink&amp;action=expertzakl'>";
 	echo "<input type='submit' name='expertzakl' value='".$expertzakl."'>";
	echo "</form></td>";
	*/
	echo "</tr></table>";
    //echo '<hr>';
    print_simple_box_end();
	/*
    print_simple_box_start('center', '50%', 'white');
	echo '<small><i>Замечание: заполненную информационную карту высылать по адресу litsenz@belnet.ru.<br>';
	echo 'В названии файла должно присутствовать название школы и района.<br>';
    print_simple_box_end();
	*/
	
    print_simple_box_start('center', '50%', 'white');
    
	//		echo '<B><p align=center><a href="' . $CFG->wwwroot. '/mod/assignment/view.php?id=156">Загрузить заполненную информационную карту в систему ЭМОУ</a>';
	print_heading("Загрузка заполненной информационной карты в систему", "center", 4);
	?>
	<p>1. Заполните информационную карту.</p>
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
    echo '<form enctype="multipart/form-data" method="post" action="infcard.php">';
    echo '<fieldset class="invisiblefieldset">';
    echo "<p>$struploadafile <br>($strmaxsize)</p>";
    echo '<input type="hidden" name="rid" value="'.$rid.'" />';
    echo '<input type="hidden" name="sid" value="'.$sid.'" />';
    echo '<input type="hidden" name="yid" value="'.$yid.'" />';
    echo '<input type="hidden" name="type_ou" value="'.$type_ou.'" />';
    echo '<input type="hidden" name="udodid" value="'.$udodid.'" />';
    echo '<input type="hidden" name="douid" value="'.$douid.'" />';		    
    echo '<input type="hidden" name="action" value="upload" />';
    require_once($CFG->libdir.'/uploadlib.php');
    upload_print_form_fragment(1,array('newfile'),false,null,0,$CFG->maxbytes,false);
    echo '<input type="submit" name="save" value="'.get_string('uploadthisfile').'" />';
    echo '</fieldset>';
    echo '</form>';
    echo '</div>';

	print_heading("Загруженная информационная карта:", "center", 4);
	$filearea = "1/$folder/$id";
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

    print_footer();
    
    
function form_download($rid, $sid, $yid, $action, $type_ou, $udodid, $douid)
{
	global $CFG;

	switch($type_ou)	{
		case 0:	$id = $sid;
		break;
		case 3: $id = $udodid;
		break;
		case 1: $id = $douid;
		break;
	}

	$textlib = textlib_get_instance();

	$fp = fopen($action.$type_ou.'.doc', "r");

	$fstat = fstat($fp);
	$buffer = fread($fp, $fstat['size']);

//    echo $buffer;
    switch ($action)	{
    	case 'infcard':
    			
				switch($type_ou)	{
					case 0:	$school =  get_record_sql("SELECT *  FROM {$CFG->prefix}monit_school
								       				   WHERE rayonid=$rid AND id=$sid AND isclosing=0 AND yearid=$yid");
			
					break;
					case 3:	$school =  get_record_sql("SELECT *  FROM {$CFG->prefix}monit_udod
								       				   WHERE rayonid=$rid AND id=$udodid AND isclosing=0 AND yearid=$yid");
			
					break;
					case 1: $school =  get_record_sql("SELECT *  FROM {$CFG->prefix}monit_education
								       				   WHERE rayonid=$rid AND id=$douid AND isclosing=0 AND yearid=$yid");
					break;
				}
    	
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

    $fn = $action.'_'.$rid.'_'.$id.'.doc';

	header("Content-type: application/vnd.ms-word");
	header("Content-Disposition: attachment; filename=\"{$fn}\"");
	header("Expires: 0");
	header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
	header("Pragma: public");

	print $buffer;
}
    
?>


