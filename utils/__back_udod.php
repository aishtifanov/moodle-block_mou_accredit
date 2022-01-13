<?php // $Id: __back.php,v 1.1.1.1 2009/10/22 11:28:07 Shtifanov Exp $

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

	$udodsids = array();
	$udods = get_records('monit_udod', 'yearid', $PREVYEARID);
	foreach($udods as $udod)	{
		$udodsids[$udod->uniqueconstcode]->oldid = $udod->id;
		$udodsids[$udod->uniqueconstcode]->newid = $udod->id;
	}
	unset($udods);
	$udods = get_records('monit_udod', 'yearid', $CURRYEARID);
	foreach($udods as $udod)	{
		$udodsids[$udod->uniqueconstcode]->newid = $udod->id;
	}
	
	$oldudodsids = array();
	foreach ($udodsids as $schsid)	{
		$oldudodsids[$schsid->newid] = $schsid->oldid;
	}
	
	$strsql = "SELECT id, name FROM mdl_monit_udod
					where yearid=$CURRYEARID
					order by id";
	if ($firstudod = get_record_sql($strsql))	{			
		$accreds = get_records_sql("SELECT DISTINCT udodid FROM mdl_monit_accreditation 
									WHERE udodid>=$firstudod->id");
		print_r($accreds); echo '<hr>';							
		if ($accreds) {
			foreach ($accreds as $accred)	{
				if ($accred->udodid == 0)	continue;
				if ($existrecs = get_records('monit_accreditation', 'udodid', $oldudodsids[$accred->udodid]))	{
					$oldcount = count($existrecs);
					echo $oldudodsids[$accred->udodid] . " old = " . $oldcount . '<br>'; 
					$existrecs = get_records('monit_accreditation', 'udodid', $accred->udodid);
					$newcount = count($existrecs);
					echo $accred->udodid . " new = " . $newcount . '<br><br>';
				
					if ($oldcount > $newcount)	{
						delete_records('monit_accreditation', 'udodid', $accred->udodid);
					}  else if ($oldcount < $newcount)	{
						delete_records('monit_accreditation', 'udodid', $oldudodsids[$accred->udodid]);
					} else if ($oldcount < 10 && $newcount < 10)	{
						delete_records('monit_accreditation', 'udodid', $accred->udodid);
						delete_records('monit_accreditation', 'udodid', $oldudodsids[$accred->udodid]);
					}
				
				} else {
					$strsql = 'UPDATE mdl_monit_accreditation SET udodid = '. $oldudodsids[$accred->udodid] . ' WHERE udodid = ' . $accred->udodid;
					echo $strsql . '<hr>';  
					$db->Execute($strsql);
				}	
			}
		}
		notify('Accreditation UDOD changed.');
		// exit();
	
	

		foreach ($oldudodsids as $oldid => $newid)	{
			$oldbasedir = $CFG->dataroot . '/0/udod/' . $oldid;
			$newbasedir = $CFG->dataroot . '/0/udod/' . $newid;
			
	  		if ($files = get_directory_list($oldbasedir)) 	{
	               foreach ($files as $key => $file) {
	                    $icon = mimeinfo('icon', $file);
	                    $ffurl = "$CFG->wwwroot/file.php/0/udod/$newid/$file";
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
