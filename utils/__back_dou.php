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

	$dousids = array();
	$dous = get_records('monit_education', 'yearid', $PREVYEARID);
	foreach($dous as $dou)	{
		$dousids[$dou->uniqueconstcode]->oldid = $dou->id;
		$dousids[$dou->uniqueconstcode]->newid = $dou->id;
	}
	unset($dous);
	$dous = get_records('monit_education', 'yearid', $CURRYEARID);
	foreach($dous as $dou)	{
		$dousids[$dou->uniqueconstcode]->newid = $dou->id;
	}
	
	$olddousids = array();
	foreach ($dousids as $schsid)	{
		$olddousids[$schsid->newid] = $schsid->oldid;
	}
	
	// $strsql = "SELECT id, name FROM mdl_monit_education where yearid=$CURRYEARID order by id";
	// if ($firstdou = get_record_sql($strsql))	{
	$firstdou = 3887;
	if ($firstdou) {
		$accreds = get_records_sql("SELECT DISTINCT douid FROM mdl_monit_accreditation 
									WHERE douid>=$firstdou");
		print_r($accreds); echo '<hr>';							
		if ($accreds) {
			foreach ($accreds as $accred)	{
				if ($accred->douid == 0) continue;
				if (!isset($olddousids[$accred->douid])) continue;
				if ($existrecs = get_records('monit_accreditation', 'douid', $olddousids[$accred->douid]))	{
					$oldcount = count($existrecs);
					echo $olddousids[$accred->douid] . " old = " . $oldcount . '<br>'; 
					$existrecs = get_records('monit_accreditation', 'douid', $accred->douid);
					$newcount = count($existrecs);
					echo $accred->douid . " new = " . $newcount . '<br><br>';
				
					if ($oldcount > $newcount)	{
						delete_records('monit_accreditation', 'douid', $accred->douid);
					}  else if ($oldcount < $newcount)	{
						delete_records('monit_accreditation', 'douid', $olddousids[$accred->douid]);
					} else if ($oldcount < 10 && $newcount < 10)	{
						delete_records('monit_accreditation', 'douid', $accred->douid);
						delete_records('monit_accreditation', 'douid', $olddousids[$accred->douid]);
					}
				
				} else {
					$strsql = 'UPDATE mdl_monit_accreditation SET douid = '. $olddousids[$accred->douid] . ' WHERE douid = ' . $accred->douid;
					echo $strsql . '<hr>';  
					$db->Execute($strsql);
				}	
			}
		}
		notify('Accreditation dou changed.');
		// exit();
	
	

		foreach ($olddousids as $oldid => $newid)	{
			$oldbasedir = $CFG->dataroot . '/0/dou/' . $oldid;
			$newbasedir = $CFG->dataroot . '/0/dou/' . $newid;
			
	  		if ($files = get_directory_list($oldbasedir)) 	{
	               foreach ($files as $key => $file) {
	                    $icon = mimeinfo('icon', $file);
	                    $ffurl = "$CFG->wwwroot/file.php/0/dou/$newid/$file";
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
