<?php  // $Id: tabs.php,v 1.2 2009/11/19 10:10:43 Shtifanov Exp $
/// This file to be included so we can assume config.php has already been included.
/// We also assume that $user, $course, $currenttab have been set


    if (empty($currenttab)) {
        error('You cannot call this script in that way');
    }

	$link = "rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;udodid=$udodid&amp;douid=$douid&amp;type_ou=$type_ou";
	
   $toprow   = array();
   $toprow[] = new tabobject('acr', "accredit.php?$link ",
               get_string('accreditation', 'block_monitoring'));
   $toprow[] = new tabobject('infcard', "infcard.php?$link ",
               get_string('ainfcard', 'block_monitoring'));
   $toprow[] = new tabobject('remark', "remark.php?$link ",
               get_string('remarks', 'block_monitoring'));
   $tabs = array($toprow);

    print_tabs($tabs, $currenttab, NULL, NULL);

?>
