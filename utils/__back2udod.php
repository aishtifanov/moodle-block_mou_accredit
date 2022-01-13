<?php // $Id: __back.php,v 1.1.1.1 2009/10/22 11:28:07 Shtifanov Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('../monitoring/lib.php');
    
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_ege/index.php">'.get_string('title','block_mou_ege').'</a>';
	$breadcrumbs .= " -> __ Move accreditation BACK 2";
    print_header("$SITE->shortname: __ Move accreditation BACK 2", $SITE->fullname, $breadcrumbs);

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

	$rid = 7;
	$oldudodid=112;
	$newudodid=309;

	delete_records('monit_accreditation', 'udodid', $newudodid);
	
	$oldaccreds = get_records_sql("SELECT * FROM mdl_monit_accreditation 
								   WHERE udodid=$oldudodid");
	print_r($oldaccreds); echo '<hr>';							
	if ($oldaccreds) {
		foreach ($oldaccreds as $accred)	{
			unset($accred->id);
			$accred->udodid = $newudodid;
			$accred->criteriaid += 100;
			$accred->indicatorid += 400;
			if (record_exists_select('monit_accreditation', "udodid=$newudodid AND criteriaid=$accred->criteriaid AND indicatorid=$accred->indicatorid")) continue;
			if (!insert_record('monit_accreditation', $accred)) {
				print_r($accred);
				error('Error');
			}
		}	

	}
	notify('Accreditation UDOD added.');
	// exit();
	

	$oldbasedir = $CFG->dataroot . '/0/udod/' . $oldudodid;
	$newbasedir = $CFG->dataroot . "/0/udod/$rid/" . $newudodid;
			
	if ($files = get_directory_list($oldbasedir)) 	{
        foreach ($files as $key => $file) {
        	 $names = explode('/', $file);
			 $names[0] += 400;
			 $newfile = $names[0] . '/' . $names[1];  	
			 mkdir($newbasedir.'/'.$names[0], $CFG->directorypermissions);
             $icon = mimeinfo('icon', $file);
             $ffurl = "$CFG->wwwroot/file.php/0/udod/$oldudodid/$file";
             echo '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
                    '<a href="'.$ffurl.'" >'.$file.'</a><br />';
	         if (!file_exists($newbasedir)) {
			     if (mkdir($newbasedir, $CFG->directorypermissions)) {
					if (!copy($oldbasedir.'/'.$file, $newbasedir . '/' . $newfile))	{
						notify('Copy failed!!!');
					}
					echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $newfile  . '<br/>';
					// exit();	
                }	else 	{
           	        echo '<div class="notifyproblem" align="center">ERROR: Could not find or create a directory ('. 
	          		     $newbasedir .')</div>'."<br />\n";
                }
	   		} else {
					if (!copy($oldbasedir.'/'.$file, $newbasedir . '/' . $newfile)) {
						notify('Copy failed!!!');
					}	
					echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $newfile  . '<br/>';
	   		}
	   }
	}

?>
