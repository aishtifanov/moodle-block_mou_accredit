<?php // $Id: __back.php,v 1.2 2010/10/04 10:39:07 Shtifanov Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('../monitoring/lib.php');
    
    $PREVYEARID = 3;
	$CURRYEARID = 4;
	
    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_ege/index.php">'.get_string('title','block_mou_ege').'</a>';
	$breadcrumbs .= " -> __ Move accreditation BACK";
    print_header("$SITE->shortname: __ Move accreditation BACK", $SITE->fullname, $breadcrumbs);

	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	$schoolsids = array();
	$schools = get_records('monit_school', 'yearid', $PREVYEARID);
	foreach($schools as $school)	{
		$schoolsids[$school->uniqueconstcode]->oldid = $school->id;
		$schoolsids[$school->uniqueconstcode]->newid = $school->id;
	}
	unset($schools);
	$schools = get_records('monit_school', 'yearid', $CURRYEARID);
	foreach($schools as $school)	{
		$schoolsids[$school->uniqueconstcode]->newid = $school->id;
	}
	
	$oldschoolsids = array();
	foreach ($schoolsids as $schsid)	{
		$oldschoolsids[$schsid->newid] = $schsid->oldid;
	}
	
	$strsql = "SELECT id, name FROM mdl_monit_school
					where yearid=$CURRYEARID
					order by id";
	if ($firstschool = get_record_sql($strsql))	{			
		$accreds = get_records_sql("SELECT DISTINCT schoolid FROM mdl_monit_accreditation 
									WHERE schoolid>=$firstschool->id");
		print_r($accreds); echo '<hr>';							
		if ($accreds) {
			foreach ($accreds as $accred)	{
				if ($accred->schoolid == 0)	continue;
				if ($existrecs = get_records('monit_accreditation', 'schoolid', $oldschoolsids[$accred->schoolid]))	{
					$oldcount = count($existrecs);
					echo $oldschoolsids[$accred->schoolid] . " old = " . $oldcount . '<br>'; 
					$existrecs = get_records('monit_accreditation', 'schoolid', $accred->schoolid);
					$newcount = count($existrecs);
					echo $accred->schoolid . " new = " . $newcount . '<br><br>';
				
					if ($oldcount > $newcount)	{
						delete_records('monit_accreditation', 'schoolid', $accred->schoolid);
					}  else if ($oldcount < $newcount)	{
						delete_records('monit_accreditation', 'schoolid', $oldschoolsids[$accred->schoolid]);
					} else if ($oldcount < 10 && $newcount < 10)	{
						delete_records('monit_accreditation', 'schoolid', $accred->schoolid);
						delete_records('monit_accreditation', 'schoolid', $oldschoolsids[$accred->schoolid]);
					}

				
				} else {
					$strsql = 'UPDATE mdl_monit_accreditation SET schoolid = '. $oldschoolsids[$accred->schoolid] . ' WHERE schoolid = ' . $accred->schoolid;
					echo $strsql . '<hr>';  
					$db->Execute($strsql);
				}	
			}
		}
		notify('Accreditation school changed.');
		// exit();
	
	

		foreach ($oldschoolsids as $oldid => $newid)	{
			$oldbasedir = $CFG->dataroot . '/0/school/' . $oldid;
			$newbasedir = $CFG->dataroot . '/0/school/' . $newid;
			
	  		if ($files = get_directory_list($oldbasedir)) 	{
	               foreach ($files as $key => $file) {
	                    $icon = mimeinfo('icon', $file);
	                    $ffurl = "$CFG->wwwroot/file.php/0/school/$newid/$file";
	                    echo '<img align="middle" src="'.$CFG->pixpath.'/f/'.$icon.'" class="icon" alt="'.$icon.'" />'.
			                       '<a href="'.$ffurl.'" >'.$file.'</a><br />';
	                    if (!file_exists($newbasedir)) {
				            if (mkdir($newbasedir, $CFG->directorypermissions)) {
								rename($oldbasedir.'/'.$file, $newbasedir . '/' . $file);
								echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $file . '<br/>';
									// exit();	
			                }	else 	{
		             	        echo '<div class="notifyproblem" align="center">ERROR: Could not find or create a directory ('. 
	                    		     $newbasedir .')</div>'."<br />\n";
			                }
			       		} else {
								rename($oldbasedir.'/'.$file, $newbasedir . '/' . $file);
								echo $oldbasedir.'/'.$file . '====>' . $newbasedir . '/' . $file . '<br/>';
			       		}
				   }
			}
		}
	}					                   
 			
?>
