<?php // $Id: block_mou_accredit.php,v 1.4 2009/12/16 10:46:51 Shtifanov Exp $

class block_mou_accredit extends block_list
{
    function init() {
        $this->title = get_string('title_accredit', 'block_mou_att');
        $this->version = 2009101400;
    }


    function get_content() {
        global $CFG;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (empty($this->instance)) {
            $this->content = '';
        } else {
            $this->load_content();
        }

        return $this->content;
        }

    function load_content() {
        global $CFG, $yearmonit;

		$yid = $yearmonit;        


		$admin_is = isadmin();
		$staff_operator_is = ismonitoperator('staff');
		$region_operator_is = ismonitoperator('region');
		$rayon_operator_is  = ismonitoperator('rayon', 0, 0, 0, true);
		if  (!$admin_is && !$region_operator_is && $rayon_operator_is) 	{
			$rid = $rayon_operator_is;
		}	else {
			$rid = 1;
		}
		$sid = ismonitoperator('school', 0, 0, 0, true);
		
		$staffview_operator = isstaffviewoperator();

		$dod_rayon_operator_is  = ismonitoperator('dod_rayon', 0, 0, 0, true);
		if  (!$admin_is && !$region_operator_is && $dod_rayon_operator_is) 	{
			$rid = $dod_rayon_operator_is;
		}
		$dod_school_operator_is = ismonitoperator('dod_school', 0, 0, 0, true);
		$dou_operator_is = ismonitoperator('dou', 0, 0, 0, true);

		if ($admin_is || $staff_operator_is || $rayon_operator_is)	 {
 	       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=0&amp;rid=$rid\">".get_string('schools', 'block_monitoring').'</a>';
  	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';

   	       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=3&amp;rid=$rid\">".get_string('udods', 'block_monitoring').'</a>';
 	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';
 	       
 	       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=1&amp;rid=$rid\">".get_string('dous', 'block_mou_att').'</a>';
 	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';

	        $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/reports/reports.php?rid=$rid&amp;sid=$sid&amp;yid=$yid\">".get_string('reports', 'block_mou_att').'</a>';
		    $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/report.gif" height="16" width="16" alt="" />';
 	       
        }

		if (!$admin_is && !$staff_operator_is && !$rayon_operator_is && $sid)  {
	       if ($school = get_record('monit_school', 'id', $sid)) {
 		       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=0&amp;rid={$school->rayonid}&amp;sid=$sid\">".get_string('school', 'block_monitoring').'</a>';
	  	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';
           }
        }

		if (!$admin_is && !$staff_operator_is && ($dod_rayon_operator_is || $dod_school_operator_is))  {
		   if ($dod_school_operator_is)	{
		       if ($udod = get_record('monit_udod', 'id', $dod_school_operator_is))  {
	  	       	   $udodrayonid = $udod->rayonid;
				   $udodid = $udod->id;
 		 	   }
 		   } else {
		       $udodrayonid = $dod_rayon_operator_is;
			   $udodid	= 0;
 		   }
   	       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=3&amp;rid=$udodrayonid&amp;udodid=$udodid\">".get_string('udod', 'block_monitoring').'</a>';
 	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';
	       
	       if (!$dod_school_operator_is)	{
	        	$this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/reports/reports.php?type_ou=3&amp;rid=$rid&amp;udodid=$udodid&amp;yid=$yid\">".get_string('reports', 'block_mou_att').'</a>';
		    	$this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/report.gif" height="16" width="16" alt="" />';
		   } 	
 	       
        }

		if (!$admin_is && !$staff_operator_is && $dou_operator_is)  {
	       if ($dou = get_record('monit_education', 'id', $dou_operator_is))  {
  	       	   $dourayonid = $dou->rayonid;
	 	   } else {
		       $dourayonid = 0;
 		   }
 	       $this->content->items[] = '<a href="'.$CFG->wwwroot."/blocks/mou_accredit/accredit/accredit.php?type_ou=1&amp;rid=$dourayonid&amp;douid=$dou_operator_is\">".get_string('dou', 'block_mou_att').'</a>';
 	       $this->content->icons[] = '<img src="'.$CFG->pixpath.'/i/tick_amber_big.gif" height="16" width="16" alt="" />';
	       
        }

	    if ($admin_is || $staff_operator_is || $rayon_operator_is || $sid )  {
		      $this->content->footer = '<a href="'.$CFG->wwwroot.'/blocks/mou_accredit/index.php">'.get_string('title_accredit', 'block_mou_att').'</a>'.' ...';
	    }

    }

    function instance_allow_config() {
        return false;
    }


    function specialization() {
        $this->title =  get_string('title_accredit', 'block_mou_att');
    }
}


// tick_amber_big.gif  ===> tick_amber_big.gif  


?>
