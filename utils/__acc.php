<?php // $Id: accreditation.php,v 1.12 2008/10/08 06:54:09 Shtifanov Exp $

    require_once('../../config.php');
    require_once($CFG->libdir.'/filelib.php');
    require_once('../monitoring/lib.php');


    $breadcrumbs = '<a href="'.$CFG->wwwroot.'/blocks/mou_ege/index.php">'.get_string('title','block_mou_ege').'</a>';
	$breadcrumbs .= " -> __ Move accreditation ";
    print_header("$SITE->shortname: __ Move accreditation ", $SITE->fullname, $breadcrumbs);


	$admin_is = isadmin();
	if (!$admin_is) {
        error(get_string('staffaccess', 'block_mou_att'));
	}

	$strcurryear = current_edu_year();
	if (!$year = get_record('monit_years', 'name', $strcurryear)) {	
		error('Current study year not found.', 'studyyear.php');
	}	

	if (!$lastyear = get_record('monit_years', 'id', $year->id - 1)) {
		error('Old year not found.', 'studyyear.php');
	}


	$schoolsids = array();
	$schools = get_records('monit_school', 'yearid', $lastyear->id);
	foreach($schools as $school)	{
		$schoolsids[$school->uniqueconstcode]->oldid = $school->id;
	}
	$schools = get_records('monit_school', 'yearid', $year->id);
	foreach($schools as $school)	{
		$schoolsids[$school->uniqueconstcode]->newid = $school->id;
	}
	
	$newschoolsids = array();
	foreach ($schoolsids as $schsid)	{
		$newschoolsids[$schsid->oldid] = $schsid->newid;
	}

	foreach ($newschoolsids as $oldid => $newid)	{
		$oldbasedir = $CFG->dataroot . '/1/schools/' . $oldid;
		$newbasedir = $CFG->dataroot . '/1/schools/' . $newid;
		
  		if ($files = get_directory_list($oldbasedir)) 	{
               foreach ($files as $key => $file) {
                    $icon = mimeinfo('icon', $file);
                    $ffurl = "$CFG->wwwroot/file.php/1/schools/$newid/$file";
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
  			
/*
ÝÌÎÓ / ? Ðåçóëüòàòû ÃÈÀ 
/ ? __ Move accreditation 
 

MOU_Varvarovskaja_SOCH_Alekseevskogo_rajona.doc
/var/www/html/moudata/1/schools/738/MOU_Varvarovskaja_SOCH_Alekseevskogo_rajona.doc====>/var/www/html/moudata/1/schools/1446/MOU_Varvarovskaja_SOCH_Alekseevskogo_rajona.doc
MOU_Lucshenkovskaya_SOSH_Alekseevscii_rayon.doc
MOU_Muchouderowka_SOSH_Belgorodscii_rayon.doc.doc
INFORMACIONNAJA_KARTA.doc
MOU_Podserednenskaya_SOSH_Alekseevskii_rayon.dok.doc
INFORMACIONNAJA_KARTA.doc
/var/www/html/moudata/1/schools/752/INFORMACIONNAJA_KARTA.doc====>/var/www/html/moudata/1/schools/1460/INFORMACIONNAJA_KARTA.doc
MOU_SOSH_4_Belgorod.doc
/var/www/html/moudata/1/schools/759/MOU_SOSH_4_Belgorod.doc====>/var/www/html/moudata/1/schools/1467/MOU_SOSH_4_Belgorod.doc
MOU_SOSH_8_Belgorod.doc.doc
MOU_SOSH_18_Belgorod.doc
/var/www/html/moudata/1/schools/771/MOU_SOSH_18_Belgorod.doc====>/var/www/html/moudata/1/schools/1479/MOU_SOSH_18_Belgorod.doc
MOU_Gimnazia_N22_g._Belgorod.doc
/var/www/html/moudata/1/schools/775/MOU_Gimnazia_N22_g._Belgorod.doc====>/var/www/html/moudata/1/schools/1483/MOU_Gimnazia_N22_g._Belgorod.doc
MOU_SOSH_34_Belgorod.doc.doc
/var/www/html/moudata/1/schools/783/MOU_SOSH_34_Belgorod.doc.doc====>/var/www/html/moudata/1/schools/1491/MOU_SOSH_34_Belgorod.doc.doc
Inf._karta_dlja_akkreditacii_1.doc
/var/www/html/moudata/1/schools/802/Inf._karta_dlja_akkreditacii_1.doc====>/var/www/html/moudata/1/schools/1510/Inf._karta_dlja_akkreditacii_1.doc
Severnaya_SOSH_2_Belgorodscii_rayon.doc
/var/www/html/moudata/1/schools/825/Severnaya_SOSH_2_Belgorodscii_rayon.doc====>/var/www/html/moudata/1/schools/1533/Severnaya_SOSH_2_Belgorodscii_rayon.doc
MOU_Oktyabrskogotnyanskaya_SOSH_Borisovskij_rayon.doc
/var/www/html/moudata/1/schools/833/MOU_Oktyabrskogotnyanskaya_SOSH_Borisovskij_rayon.doc====>/var/www/html/moudata/1/schools/1541/MOU_Oktyabrskogotnyanskaya_SOSH_Borisovskij_rayon.doc
MOU_HOTMIJSKAJ_SOSH_BORISOVSKIY_rayon.doc
/var/www/html/moudata/1/schools/835/MOU_HOTMIJSKAJ_SOSH_BORISOVSKIY_rayon.doc====>/var/www/html/moudata/1/schools/1543/MOU_HOTMIJSKAJ_SOSH_BORISOVSKIY_rayon.doc
MOU_Kaznacheevskaja_SOCH_Valuiskogo_raiona.doc
/var/www/html/moudata/1/schools/846/MOU_Kaznacheevskaja_SOCH_Valuiskogo_raiona.doc====>/var/www/html/moudata/1/schools/1554/MOU_Kaznacheevskaja_SOCH_Valuiskogo_raiona.doc
MOU_Bolsie.doc
/var/www/html/moudata/1/schools/859/MOU_Bolsie.doc====>/var/www/html/moudata/1/schools/1567/MOU_Bolsie.doc
Inf_karta_Deg.doc
/var/www/html/moudata/1/schools/863/Inf_karta_Deg.doc====>/var/www/html/moudata/1/schools/1571/Inf_karta_Deg.doc
informac_karta._.doc
/var/www/html/moudata/1/schools/870/informac_karta._.doc====>/var/www/html/moudata/1/schools/1578/informac_karta._.doc
MOU_Fozhevatovo_soh_Volokonovskii_rayon.doc
/var/www/html/moudata/1/schools/883/MOU_Fozhevatovo_soh_Volokonovskii_rayon.doc====>/var/www/html/moudata/1/schools/1591/MOU_Fozhevatovo_soh_Volokonovskii_rayon.doc
MOU_pohaevskaj_SOSH_Graivoronskii_raion.doc
/var/www/html/moudata/1/schools/895/MOU_pohaevskaj_SOSH_Graivoronskii_raion.doc====>/var/www/html/moudata/1/schools/1603/MOU_pohaevskaj_SOSH_Graivoronskii_raion.doc
MOU_Grafovskaya_SOSH_Shebekinscii_rayon.doc
/var/www/html/moudata/1/schools/914/MOU_Grafovskaya_SOSH_Shebekinscii_rayon.doc====>/var/www/html/moudata/1/schools/1622/MOU_Grafovskaya_SOSH_Shebekinscii_rayon.doc
MOU-Chernyanskaya-SOSH2-Chernyaskii-rayon-Belgorodskay-oblast.doc
/var/www/html/moudata/1/schools/929/MOU-Chernyanskaya-SOSH2-Chernyaskii-rayon-Belgorodskay-oblast.doc====>/var/www/html/moudata/1/schools/1637/MOU-Chernyanskaya-SOSH2-Chernyaskii-rayon-Belgorodskay-oblast.doc
MOU_Volotovskaya_SOSH_Chernaynscii_rayon.doc
/var/www/html/moudata/1/schools/933/MOU_Volotovskaya_SOSH_Chernaynscii_rayon.doc====>/var/www/html/moudata/1/schools/1641/MOU_Volotovskaya_SOSH_Chernaynscii_rayon.doc
MOU_Volokonovskaya_SOSH_Chernyanskii_rayon.doc.doc
/var/www/html/moudata/1/schools/934/MOU_Volokonovskaya_SOSH_Chernyanskii_rayon.doc.doc====>/var/www/html/moudata/1/schools/1642/MOU_Volokonovskaya_SOSH_Chernyanskii_rayon.doc.doc
MOU_Wolkowskaya_SOSH_Chernyanskii_rayon.doc.doc
/var/www/html/moudata/1/schools/935/MOU_Wolkowskaya_SOSH_Chernyanskii_rayon.doc.doc====>/var/www/html/moudata/1/schools/1643/MOU_Wolkowskaya_SOSH_Chernyanskii_rayon.doc.doc
MOU_SOSH_Kuzkino_Cernjnskiy_rayon.doc
/var/www/html/moudata/1/schools/937/MOU_SOSH_Kuzkino_Cernjnskiy_rayon.doc====>/var/www/html/moudata/1/schools/1645/MOU_SOSH_Kuzkino_Cernjnskiy_rayon.doc
MOU_Lubyanskaya_OOSH_Chernaynscii_rayon._dos.doc
/var/www/html/moudata/1/schools/941/MOU_Lubyanskaya_OOSH_Chernaynscii_rayon._dos.doc====>/var/www/html/moudata/1/schools/1649/MOU_Lubyanskaya_OOSH_Chernaynscii_rayon._dos.doc
MOU_SOSH_RUSSKAYA_HALAN.doc
/var/www/html/moudata/1/schools/946/MOU_SOSH_RUSSKAYA_HALAN.doc====>/var/www/html/moudata/1/schools/1654/MOU_SOSH_RUSSKAYA_HALAN.doc
MOU_SOSh8_Starooskolskii_rayon.htm
/var/www/html/moudata/1/schools/952/MOU_SOSh8_Starooskolskii_rayon.htm====>/var/www/html/moudata/1/schools/1660/MOU_SOSh8_Starooskolskii_rayon.htm
Tablica_No2_gimnazii.doc
/var/www/html/moudata/1/schools/960/Tablica_No2_gimnazii.doc====>/var/www/html/moudata/1/schools/1668/Tablica_No2_gimnazii.doc
MOU_SOSH_22_Starooskolsky_rayon_.doc
/var/www/html/moudata/1/schools/964/MOU_SOSH_22_Starooskolsky_rayon_.doc====>/var/www/html/moudata/1/schools/1672/MOU_SOSH_22_Starooskolsky_rayon_.doc
MOU_Gorodizhenskaya_SOSH_Starooskolskii_rayon.doc
/var/www/html/moudata/1/schools/979/MOU_Gorodizhenskaya_SOSH_Starooskolskii_rayon.doc====>/var/www/html/moudata/1/schools/1687/MOU_Gorodizhenskaya_SOSH_Starooskolskii_rayon.doc
MOU_Rogowatowskaia_SOSH_Staroockolskii_rayon.doc
/var/www/html/moudata/1/schools/992/MOU_Rogowatowskaia_SOSH_Staroockolskii_rayon.doc====>/var/www/html/moudata/1/schools/1700/MOU_Rogowatowskaia_SOSH_Staroockolskii_rayon.doc
MOU_Soldatskaya_SOSH_Staarooscii_rayon.doc
/var/www/html/moudata/1/schools/993/MOU_Soldatskaya_SOSH_Staarooscii_rayon.doc====>/var/www/html/moudata/1/schools/1701/MOU_Soldatskaya_SOSH_Staarooscii_rayon.doc
MOU_Novooskolskaya_SOSH_3_Belgorodscii_obl.doc.doc
/var/www/html/moudata/1/schools/1049/MOU_Novooskolskaya_SOSH_3_Belgorodscii_obl.doc.doc====>/var/www/html/moudata/1/schools/1757/MOU_Novooskolskaya_SOSH_3_Belgorodscii_obl.doc.doc
Rashoveckaya_SOSH_Krasnenskii_rayon.doc
/var/www/html/moudata/1/schools/1066/Rashoveckaya_SOSH_Krasnenskii_rayon.doc====>/var/www/html/moudata/1/schools/1774/Rashoveckaya_SOSH_Krasnenskii_rayon.doc
Lesnoukolovskaya_SOSH_Krasnenskii_rayon.doc
/var/www/html/moudata/1/schools/1068/Lesnoukolovskaya_SOSH_Krasnenskii_rayon.doc====>/var/www/html/moudata/1/schools/1776/Lesnoukolovskaya_SOSH_Krasnenskii_rayon.doc
Krasnenskaya_SOSH_Krasnenskii_rayon.doc
/var/www/html/moudata/1/schools/1071/Krasnenskaya_SOSH_Krasnenskii_rayon.doc====>/var/www/html/moudata/1/schools/1779/Krasnenskaya_SOSH_Krasnenskii_rayon.doc
Gorskaya_SOSH_Krasnenskii_rayon.doc
/var/www/html/moudata/1/schools/1072/Gorskaya_SOSH_Krasnenskii_rayon.doc====>/var/www/html/moudata/1/schools/1780/Gorskaya_SOSH_Krasnenskii_rayon.doc
MOU_Verhnepokrovskaya_SOSH_Krasnogvardeyscii_rayon.doc.doc
MOU_Gredykinskaya_OOSH_Krasnogvardeyscii_rayon.doc.doc
MOU_Kolomizevskaya_SOSH_Krasnogvardeyscii_rayon.dok.doc
MOU_Plotavskay_SOSH_Korochanskogo_rayona.doc
/var/www/html/moudata/1/schools/1121/MOU_Plotavskay_SOSH_Korochanskogo_rayona.doc====>/var/www/html/moudata/1/schools/1829/MOU_Plotavskay_SOSH_Korochanskogo_rayona.doc
MOU_Dragunskaj_SOSH_Ivnjnskiy_rayon.doc
/var/www/html/moudata/1/schools/1132/MOU_Dragunskaj_SOSH_Ivnjnskiy_rayon.doc====>/var/www/html/moudata/1/schools/1840/MOU_Dragunskaj_SOSH_Ivnjnskiy_rayon.doc
MOU_Novenskya_SOSH_IVNYANSKII_RAYON.doc
/var/www/html/moudata/1/schools/1135/MOU_Novenskya_SOSH_IVNYANSKII_RAYON.doc====>/var/www/html/moudata/1/schools/1843/MOU_Novenskya_SOSH_IVNYANSKII_RAYON.doc
MOU_POKROVSKYA_SOSH_IVNYANSKII_RAYON.doc
/var/www/html/moudata/1/schools/1137/MOU_POKROVSKYA_SOSH_IVNYANSKII_RAYON.doc====>/var/www/html/moudata/1/schools/1845/MOU_POKROVSKYA_SOSH_IVNYANSKII_RAYON.doc
MOU_Xomytchanskaya_SOSH_Ivnyanskii_rayon.doc
/var/www/html/moudata/1/schools/1141/MOU_Xomytchanskaya_SOSH_Ivnyanskii_rayon.doc====>/var/www/html/moudata/1/schools/1849/MOU_Xomytchanskaya_SOSH_Ivnyanskii_rayon.doc
inf_karta_school_16_Gubkin.doc
/var/www/html/moudata/1/schools/1154/inf_karta_school_16_Gubkin.doc====>/var/www/html/moudata/1/schools/1862/inf_karta_school_16_Gubkin.doc
infcard_MOU_Nikanorovskaya_SOSH_Gubkinscii_rayon.doc
/var/www/html/moudata/1/schools/1164/infcard_MOU_Nikanorovskaya_SOSH_Gubkinscii_rayon.doc====>/var/www/html/moudata/1/schools/1872/infcard_MOU_Nikanorovskaya_SOSH_Gubkinscii_rayon.doc
MOU_Sergievskaja_SOSH_Gubkinski_rajon._doc.doc
/var/www/html/moudata/1/schools/1166/MOU_Sergievskaja_SOSH_Gubkinski_rajon._doc.doc====>/var/www/html/moudata/1/schools/1874/MOU_Sergievskaja_SOSH_Gubkinski_rajon._doc.doc
iKrivc_inf_karta_2009.doc
/var/www/html/moudata/1/schools/1180/iKrivc_inf_karta_2009.doc====>/var/www/html/moudata/1/schools/1888/iKrivc_inf_karta_2009.doc
MOU_Soch_1_Stroitel_Yakovlevskij_raion.doc
/var/www/html/moudata/1/schools/1185/MOU_Soch_1_Stroitel_Yakovlevskij_raion.doc====>/var/www/html/moudata/1/schools/1893/MOU_Soch_1_Stroitel_Yakovlevskij_raion.doc
karta.docx
INFORMACIONNAJA_KARTA.doc
Informacionnaja_karta_1.doc
/var/www/html/moudata/1/schools/1190/Informacionnaja_karta_1.doc====>/var/www/html/moudata/1/schools/1898/Informacionnaja_karta_1.doc
MOU_OOSH_9_Belgorodscii_rayon.doc
/var/www/html/moudata/1/schools/1199/MOU_OOSH_9_Belgorodscii_rayon.doc====>/var/www/html/moudata/1/schools/1907/MOU_OOSH_9_Belgorodscii_rayon.doc
MOU_NOSH_31_Stary_Oskol.htm
/var/www/html/moudata/1/schools/1200/MOU_NOSH_31_Stary_Oskol.htm====>/var/www/html/moudata/1/schools/1908/MOU_NOSH_31_Stary_Oskol.htm
Inf._karta_dlja_akkreditacii_Novoklad.doc
/var/www/html/moudata/1/schools/1205/Inf._karta_dlja_akkreditacii_Novoklad.doc====>/var/www/html/moudata/1/schools/1913/Inf._karta_dlja_akkreditacii_Novoklad.doc
mou_nicoosh_ooh_alexeevsc_rayon._doc.doc
MOU_Pirogovskaia_OOsch_1_.htm
/var/www/html/moudata/1/schools/1213/MOU_Pirogovskaia_OOsch_1_.htm====>/var/www/html/moudata/1/schools/1921/MOU_Pirogovskaia_OOsch_1_.htm
MVSOU_vecernaya_SOSH_1_g.Alekseevka.doc
MOU_OOSH_9_Gubkinckii_rayon.doc.htm
/var/www/html/moudata/1/schools/1229/MOU_OOSH_9_Gubkinckii_rayon.doc.htm====>/var/www/html/moudata/1/schools/1936/MOU_OOSH_9_Gubkinckii_rayon.doc.htm
MOU_Melavskaya_OOSH_Gubkinscii_rayon.doc.doc
/var/www/html/moudata/1/schools/1233/MOU_Melavskaya_OOSH_Gubkinscii_rayon.doc.doc====>/var/www/html/moudata/1/schools/1940/MOU_Melavskaya_OOSH_Gubkinscii_rayon.doc.doc
Bogoslovskaya_OOSH_Krasnenskii_rayon.doc
/var/www/html/moudata/1/schools/1239/Bogoslovskaya_OOSH_Krasnenskii_rayon.doc====>/var/www/html/moudata/1/schools/1945/Bogoslovskaya_OOSH_Krasnenskii_rayon.doc
MOU_Berezobckay_SOH_Borisobskiy_rayon.doc
/var/www/html/moudata/1/schools/1248/MOU_Berezobckay_SOH_Borisobskiy_rayon.doc====>/var/www/html/moudata/1/schools/1954/MOU_Berezobckay_SOH_Borisobskiy_rayon.doc
INFORM_KARTA_Kubraki
/var/www/html/moudata/1/schools/1266/INFORM_KARTA_Kubraki====>/var/www/html/moudata/1/schools/1970/INFORM_KARTA_Kubraki
MOU_Rodnikovskaja_NOSCH.htm
MOU_Xolkovskaya_OOSH_Chernaynscii_rayon.doc
/var/www/html/moudata/1/schools/1307/MOU_Xolkovskaya_OOSH_Chernaynscii_rayon.doc====>/var/www/html/moudata/1/schools/2010/MOU_Xolkovskaya_OOSH_Chernaynscii_rayon.doc
MOU_Zavalskaya_OOSH_Krasnogvardeyscii_rayon.doc.doc
/var/www/html/moudata/1/schools/1318/MOU_Zavalskaya_OOSH_Krasnogvardeyscii_rayon.doc.doc====>/var/www/html/moudata/1/schools/2020/MOU_Zavalskaya_OOSH_Krasnogvardeyscii_rayon.doc.doc
MOU_Endovickaya_NOSH_Krasnogvardeyscii_rayon.doc.doc
/var/www/html/moudata/1/schools/1325/MOU_Endovickaya_NOSH_Krasnogvardeyscii_rayon.doc.doc====>/var/www/html/moudata/1/schools/2027/MOU_Endovickaya_NOSH_Krasnogvardeyscii_rayon.doc.doc
MOU_Gorodichenskaj_NOCH_Korochanskii_rayon.doc
/var/www/html/moudata/1/schools/1339/MOU_Gorodichenskaj_NOCH_Korochanskii_rayon.doc====>/var/www/html/moudata/1/schools/2041/MOU_Gorodichenskaj_NOCH_Korochanskii_rayon.doc
MOY_BATRATSKAY_OOSH_SHEBEKINSKII_rayon.doc
/var/www/html/moudata/1/schools/1390/MOY_BATRATSKAY_OOSH_SHEBEKINSKII_rayon.doc====>/var/www/html/moudata/1/schools/2083/MOY_BATRATSKAY_OOSH_SHEBEKINSKII_rayon.doc
MOU_Staroselcevssaya_NOSH_Volokonovscii_rayon.doc
/var/www/html/moudata/1/schools/1415/MOU_Staroselcevssaya_NOSH_Volokonovscii_rayon.doc====>/var/www/html/moudata/1/schools/2107/MOU_Staroselcevssaya_NOSH_Volokonovscii_rayon.doc

*/  			
?>


