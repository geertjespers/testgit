<?php
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
<title>Bond Beter Leefmilieu &gt; Admin &gt; Verocheck facturatiecontrole</title>
<meta http-equiv="Expires" content="0" />
<style>
body{
  background-color:#fff;
  font-family:Trebuchet MS;  
  font-size:12px;  
  }
table{
  clear:both;
  margin:5px 0 5px 0;
  }
td{
  font-size: 10px;
  }
.header{
  border:0px;
  background-color:#141413;
  color:#fff;
  }
.datarow{
  border:0px;
  background-color:#f0f0f0;
  }
.button{
  background-color:#000;
  color:#fff; 
  border:0;
  margin-top:5px;
  }
h1{
  float:left;
  font-size:14px;
  font-weight:normal;  
  width:400px;
  }
a.close{
  float:right;
  color:#000;
  }
p.errormsg{
  color:#cc0001;
  font-size:12px;
  font-weight:bold;
  }
</style>
</head>
  <body>
		<table border="0" cellpadding="5" cellspacing="0" width="100%">
			<tr bgcolor="#dddddd">
				<td valign="middle" align="left"><a href="../../" target="_top">Bond Beter Leefmilieu</a> &gt; <a href="../" target="_top">Admin</a> &gt; Verocheck facturatiecontrole</td>
				<td valign="middle" align="right">Gebruiker: <?echo$user_voornaam." ".$user_achternaam;?></td>
			</tr>
		</table>

<?php

// geen prijzen voor wie in 2011 een prijs kreeg 
	$winnaars2011 = array();  
	$row = 0; 
	$bestand_2011 = "userid_prijzen_2011.csv"; //  resultaten_werknemers_allebeperkt.csv
    $lines = file($bestand_2011);
    foreach ($lines as $line_num => $line) {  
      
      $l = split(";", $line);
      foreach($l as $_k => $_v){
			$winnaars2011[$row] = $_v;
			$row++;  
        }
		}

// print_r ($winnaars2011); 
		
// STAP 1: inlezen van deelnemers > 20 dagen: naam, bedrijf, userid, aantal duurzame dagen
/* bestand: 
	- zelf de personen er uit halen met groter dan of gelijk aan 20 duurzame dagen
	Kolommen: 
	- Userid (op eerste plaats) 
	- Bedrijf
	- Provincie
	- Email
	- Voornaam
	- Naam
	- Duurzaam
	- CO2
	- Prijs (wordt default "Geen" > niet in CSV opnemen) 
*/  
	$bestand_resultaten = "resultaten_werknemers_alle.csv"; //  resultaten_werknemers_allebeperkt.csv


    $lines = file($bestand_resultaten);
    $content = array();
    $rowid = 0;
    $searcha = array("\t", "\r", "\n","\"");
    $replacea = array("", "", "", "");
    $fileheader = array();
    $deelnemers = array();
    $deelnemersovl = array();
    $deelnemerswvl = array();
    $bedrijven = array();
    $bedrijven2040 = array();
    $bedrijven40plus = array();
    $aantal_per_bedrijf = array();
    $bedrijf = array();
    $provincie = array();
    $aantal_totaal = 0;
    $aantal_totaal_geactiveerd = 0;
    $aantal_totaal20 = 0;
    $aantal_co2 = 0;
    $maxprijs = 0;
    foreach ($lines as $line_num => $line) {  
      
      $l = split(";", $line);    
      $columns = array();    
      $deelnemer = array();
      
      $colid = 0; 
      foreach($l as $_k => $_v){
		$_vr = str_replace($searcha, $replacea, $_v);  
        if($rowid == 0){
          $fileheader[$_k] = $_vr;
        }
        else{
            switch ($colid) {
            	case 0: 
						$deelnemer_userid = $_vr;
						$deelnemer[$fileheader[$_k]] = $_vr;
						$colid++;
						break;
            	case 1: 
						$deelnemer_bedrijf = $_vr;
						$deelnemer[$fileheader[$_k]] = $_vr;
						$colid++;
						break; 
            	default: 
						$deelnemer[$fileheader[$_k]] = $_vr;
						break; 
            	}
 			}
        }
    if($rowid > 0){
			if (in_array((int)$deelnemer_userid,$winnaars2011)) {
	         $deelnemer['Prijs'] = "(2011)";
				}
				else {
	         $deelnemer['Prijs'] = "Geen";
				}
 			$deelnemers[$deelnemer_userid] = $deelnemer;
 			$bedrijven[$deelnemer_bedrijf][$deelnemer_userid] = $deelnemer;
			$aantal_per_bedrijf[$deelnemer_bedrijf]++; 
			$bedrijf[$deelnemer_bedrijf]['CO2'] = $bedrijf[$deelnemer_bedrijf]['CO2'] + $deelnemer['CO2']; 
			$bedrijf[$deelnemer_bedrijf]['geactiveerd']++;
			if ( $deelnemer['Duurzaam'] > 0 ) {
 			$bedrijf[$deelnemer_bedrijf]['deelnemers']++;
			}  
			$provincie[$deelnemer['Provincie']]['CO2'] = $provincie[$deelnemer['Provincie']]['CO2'] + $deelnemer['CO2'];
			$provincie[$deelnemer['Provincie']]['geactiveerd']++;
			if ( $deelnemer['Duurzaam'] >= 20 ) {
			$bedrijf[$deelnemer_bedrijf]['deelnemers20']++;
			$aantal_totaal20++;
			$provincie[$deelnemer['Provincie']]['deelnemers20']++;
			}  
			if ( $deelnemer['Duurzaam'] > 0 ) {
			$aantal_totaal++;
			$provincie[$deelnemer['Provincie']]['deelnemers']++;
			}  
			if ( $deelnemer['Duurzaam'] >= 20 ) {
 			$bedrijven20plus[$deelnemer_bedrijf][$deelnemer_userid] = $deelnemer;
			}  
			if ( $deelnemer['Duurzaam'] > 40 ) {
 			$bedrijven40plus[$deelnemer_bedrijf][$deelnemer_userid] = $deelnemer;
			} 
			if ( $deelnemer['Provincie'] == "oost-vlaanderen" AND $deelnemer['Duurzaam'] >= 20 ) {
 			$deelnemersovl[$deelnemer_userid] = $deelnemer;
			} 
			if ( $deelnemer['Provincie'] == "west-vlaanderen" AND $deelnemer['Duurzaam'] >= 20 ) {
 			$deelnemerswvl[$deelnemer_userid] = $deelnemer;
			} 
			$aantal_totaal_geactiveerd++; 
			$aantal_co2 = $aantal_co2 + $deelnemer['CO2']; 
			if (in_array((int)$deelnemer_userid,$winnaars2011)) {
	         unset ($bedrijven20plus[$deelnemer_bedrijf][$deelnemer_userid]); 
	         unset ($bedrijven40plus[$deelnemer_bedrijf][$deelnemer_userid]); 
  	         unset ($deelnemersovl[$deelnemer_userid]); 
  	         unset ($deelnemerswvl[$deelnemer_userid]); 
				}
        }
   $rowid++;
}




// print_r ($deelnemersovl);
// print "xxxxxxx"; 
// print_r ($bedrijven40plus);

/* TO DO
- csv file zodat kan geëxporteerd worden naar excel > daarin verder werken 
- toevoegen nid van profiel van de werkgever > latere koppeling 
- hoe prijsrapport goed uitprinten om te mailen? 

*/

// print_r ($bedrijven); 




// controle print html table bedrijven MAG WEG 

/* print "Totaal aantal deelnemers: " . $aantal_totaal; 
print "<table border='1'>";
print "<tr>";
print "<td>bedrijf</td>";
print "<td>aantal_deelnemers > 20</td>";
print "</tr>";
foreach($aantal_per_bedrijf as $_k => $_v){
	print "<tr>";
	print "<td>" . $_k . "</td>";
	print "<td>" . $_v . "</td>";
	print "</tr>";
}	
print "</table>";  */

// controle print html table provincies
print "<table border='1'>";
print "<tr>";
print "<td>provincie</td>";
print "<td>geactiveerd</td>";
print "<td>deelnemers</td>";
print "<td>deelnemers gt 20</td>";
print "<td>co2</td>";
print "<td>%</td>";
print "</tr>";
foreach($provincie as $_k => $_v){
	print "<tr>";
	print "<td>" . $_k . "</td>";
	print "<td>" . $_v['geactiveerd'] . "</td>";
	print "<td>" . $_v['deelnemers'] . "</td>";
	print "<td>" . $_v['deelnemers20'] . "</td>";
	print "<td>" . $_v['CO2'] . "</td>";
	print "<td>" . round($_v['CO2'] / $aantal_co2 * 100) . "</td>";
	print "</tr>";
}	
print "</table>"; 

print "<li>Totaal aantal geactiveerd: " . $aantal_totaal_geactiveerd; 
print "<li>Totaal aantal deelnemers (minstens 1 duurzame verplaatsing): " . $aantal_totaal; 
print "<li>Totaal aantal deelnemers gt 20 dagen: " . $aantal_totaal20; 
print "<li>Totaal CO2: " . $aantal_co2; 

// max aantal per bedrijf berekenen en aantal prijzen op 0 
foreach($bedrijf as $_k => $_v){
	if ( $_v['deelnemers20'] == 0 ) { $bedrijf[$_k]['maxprijs'] = 0; } 
	if ( ( $_v['deelnemers20'] >= 1 ) AND ($_v['deelnemers20'] <= 3  ) ) { $bedrijf[$_k]['maxprijs'] = 1; } 
	if ( ( $_v['deelnemers20'] >= 4 ) AND ($_v['deelnemers20'] <= 5  ) ) { $bedrijf[$_k]['maxprijs'] = 2; } 
	if ( ( $_v['deelnemers20'] >= 6 ) AND ($_v['deelnemers20'] <= 10  ) ) { $bedrijf[$_k]['maxprijs'] = 3; } 
	if ( ( $_v['deelnemers20'] >= 11 ) AND ($_v['deelnemers20'] <= 20  ) ) { $bedrijf[$_k]['maxprijs'] = 5; } 
	if ( ( $_v['deelnemers20'] >= 21 ) AND ($_v['deelnemers20'] <= 30  ) ) { $bedrijf[$_k]['maxprijs'] = 7; } 
	if ( ( $_v['deelnemers20'] >= 31 ) AND ($_v['deelnemers20'] <= 40  ) ) { $bedrijf[$_k]['maxprijs'] = 10; } 
	if ( ( $_v['deelnemers20'] >= 41 ) AND ($_v['deelnemers20'] <= 50  ) ) { $bedrijf[$_k]['maxprijs'] = 12; } 
	if ( ( $_v['deelnemers20'] >= 51 ) AND ($_v['deelnemers20'] <= 60  ) ) { $bedrijf[$_k]['maxprijs'] = 15; } 
	if ( $_v['deelnemers20'] >= 61 ) { $bedrijf[$_k]['maxprijs'] = 20; } 
	$bedrijf[$_k]['aantalprijs'] = 0; 
	$maxprijs = $maxprijs + $bedrijf[$_k]['maxprijs']; 
	
}	


print "<li>Aantal volgens max per bedrijf: " . $maxprijs; 


// eerst  prijzen toekennen aan de bedrijven 
// per bedrijf array: $prijzen['bedrijf'][nr] = "naam"; 

$prijzen = array(); 

$prijs_bluebike_max = 6; // aantal keer 10 > 2 keer doorlopen 
$prijs_omnipas_max = 6; // 60 omnipas De Lijn 
$prijs_nmbs_max = 5; //  2x doorlopen
$prijs_basil_max = 115; //  
$prijs_dwars_max = 14; //  2x doorlopen
$prijs_licht_max = 5; //  aantal keer 10 > 2x doorlopen
$prijs_teller_max = 5; //  aantal keer 10 > 2x doorlopen
$prijs_zitbal_max = 3; // aantal keer 10 + 5 extra  
$prijs_hoofd_max = 11; //  

$bedrijvenpot = $bedrijven; 

// bedrijven zonder prijzen er nu al uit halen
foreach($bedrijvenpot as $_k => $_v){
	if ( $bedrijf[$_k]['maxprijs'] == 0 ) { unset ($bedrijvenpot[$_k]); } 
}	

for ( $x = 1; $x <= $prijs_omnipas_max ; $x++ )
{
$prijs_omnipas = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_omnipas[$i]][$bedrijf[$prijs_omnipas[$i]]['aantalprijs']] = "Omnipas";
    $bedrijf[$prijs_omnipas[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_omnipas[$i]]['aantalprijs'] == $bedrijf[$prijs_omnipas[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_omnipas[$i]]);     
    }
}
}


for ( $x = 1; $x <= $prijs_teller_max ; $x++ )
{
$prijs_teller = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_teller[$i]][$bedrijf[$prijs_teller[$i]]['aantalprijs']] = "Stappenteller";
    $bedrijf[$prijs_teller[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_teller[$i]]['aantalprijs'] == $bedrijf[$prijs_teller[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_teller[$i]]);     
    }
}
}


for ( $x = 1; $x <= $prijs_bluebike_max ; $x++ )
{
$prijs_bluebike = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    // echo "<li>Blue Bike " . $i . ": " . $prijs_bluebike[$i];
    $prijzen[$prijs_bluebike[$i]][$bedrijf[$prijs_bluebike[$i]]['aantalprijs']] = "Blue Bike";
    $bedrijf[$prijs_bluebike[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_bluebike[$i]]['aantalprijs'] == $bedrijf[$prijs_bluebike[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_bluebike[$i]]);     
    }
}
}

for ( $x = 1; $x <= $prijs_nmbs_max ; $x++ )
{
$prijs_nmbs = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10 ; $i++) {
    $prijzen[$prijs_nmbs[$i]][$bedrijf[$prijs_nmbs[$i]]['aantalprijs']] = "NMBS reischeque";
    $bedrijf[$prijs_nmbs[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_nmbs[$i]]['aantalprijs'] == $bedrijf[$prijs_nmbs[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_nmbs[$i]]);     
    }
}
}

$prijs_basil = array_rand($bedrijvenpot,$prijs_basil_max);
for ($i = 0; $i < $prijs_basil_max; $i++) {
    $prijzen[$prijs_basil[$i]][$bedrijf[$prijs_basil[$i]]['aantalprijs']] = "Basil fietstas";
    $bedrijf[$prijs_basil[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_basil[$i]]['aantalprijs'] == $bedrijf[$prijs_basil[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_basil[$i]]);     
    }
}

// hoe hoger hoe meer kans dat bedrijven met weinig prijzen 'm krijgen, hoe lager > altijd voor bedrijven met veel prijzen 
$prijs_hoofd = array_rand($bedrijvenpot,$prijs_hoofd_max);
for ($i = 0; $i < $prijs_hoofd_max; $i++) {
	 $nummerhoofdprijs = $i + 1;     
    $prijzen[$prijs_hoofd[$i]][$bedrijf[$prijs_hoofd[$i]]['aantalprijs']] = "Hoofdprijs" . $nummerhoofdprijs;
    $bedrijf[$prijs_hoofd[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_hoofd[$i]]['aantalprijs'] == $bedrijf[$prijs_hoofd[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_hoofd[$i]]);     
    }
}


for ( $x = 1; $x <= $prijs_dwars_max ; $x++ )
{
$prijs_dwars = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_dwars[$i]][$bedrijf[$prijs_dwars[$i]]['aantalprijs']] = "Dwarsligger";
    $bedrijf[$prijs_dwars[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_dwars[$i]]['aantalprijs'] == $bedrijf[$prijs_dwars[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_dwars[$i]]);     
    }
}
}

for ( $x = 1; $x <= $prijs_licht_max ; $x++ )
{
$prijs_licht = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_licht[$i]][$bedrijf[$prijs_licht[$i]]['aantalprijs']] = "Fietslicht";
    $bedrijf[$prijs_licht[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_licht[$i]]['aantalprijs'] == $bedrijf[$prijs_licht[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_licht[$i]]);     
    }
}
}


for ( $x = 1; $x <= $prijs_bluebike_max ; $x++ )
{
$prijs_bluebike = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    // echo "<li>Blue Bike " . $i . ": " . $prijs_bluebike[$i];
    $prijzen[$prijs_bluebike[$i]][$bedrijf[$prijs_bluebike[$i]]['aantalprijs']] = "Blue Bike";
    $bedrijf[$prijs_bluebike[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_bluebike[$i]]['aantalprijs'] == $bedrijf[$prijs_bluebike[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_bluebike[$i]]);     
    }
}
}

for ( $x = 1; $x <= $prijs_dwars_max ; $x++ )
{
$prijs_dwars = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_dwars[$i]][$bedrijf[$prijs_dwars[$i]]['aantalprijs']] = "Dwarsligger";
    $bedrijf[$prijs_dwars[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_dwars[$i]]['aantalprijs'] == $bedrijf[$prijs_dwars[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_dwars[$i]]);     
    }
}
}


for ( $x = 1; $x <= $prijs_nmbs_max ; $x++ )
{
$prijs_nmbs = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10 ; $i++) {
    $prijzen[$prijs_nmbs[$i]][$bedrijf[$prijs_nmbs[$i]]['aantalprijs']] = "NMBS reischeque";
    $bedrijf[$prijs_nmbs[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_nmbs[$i]]['aantalprijs'] == $bedrijf[$prijs_nmbs[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_nmbs[$i]]);     
    }
}
}

for ( $x = 1; $x <= $prijs_teller_max ; $x++ )
{
$prijs_teller = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_teller[$i]][$bedrijf[$prijs_teller[$i]]['aantalprijs']] = "Stappenteller";
    $bedrijf[$prijs_teller[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_teller[$i]]['aantalprijs'] == $bedrijf[$prijs_teller[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_teller[$i]]);     
    }
}
}


for ( $x = 1; $x <= $prijs_zitbal_max ; $x++ )
{
$prijs_zitbal = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_zitbal[$i]][$bedrijf[$prijs_zitbal[$i]]['aantalprijs']] = "Zitbal";
    $bedrijf[$prijs_zitbal[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_zitbal[$i]]['aantalprijs'] == $bedrijf[$prijs_zitbal[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_zitbal[$i]]);     
    }
}
}

for ( $x = 1; $x <= $prijs_licht_max ; $x++ )
{
$prijs_licht = array_rand($bedrijvenpot,10);
for ($i = 0; $i < 10; $i++) {
    $prijzen[$prijs_licht[$i]][$bedrijf[$prijs_licht[$i]]['aantalprijs']] = "Fietslicht";
    $bedrijf[$prijs_licht[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_licht[$i]]['aantalprijs'] == $bedrijf[$prijs_licht[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_licht[$i]]);     
    }
}
}

$prijs_zitbal = array_rand($bedrijvenpot,5);
for ($i = 0; $i < 5; $i++) {
    $prijzen[$prijs_zitbal[$i]][$bedrijf[$prijs_zitbal[$i]]['aantalprijs']] = "Zitbal";
    $bedrijf[$prijs_zitbal[$i]]['aantalprijs']++; 
    if ( $bedrijf[$prijs_zitbal[$i]]['aantalprijs'] == $bedrijf[$prijs_zitbal[$i]]['maxprijs']) {
	 unset ($bedrijvenpot[$prijs_zitbal[$i]]);     
    }
}



// print_r ($prijzen);

$aantal_toegekend = 0; 



// prijzen per bedrijf aan de deelnemers toekennen 
// resultaat $deelnemers['bedrijf'][userid]['prijs'] = 'xxx'; 
// $bedrijven20plus['bedrijf']['userid] bevat allen boven 20 duurzame dagen
// $bedrijven40plus['bedrijf']['userid] bevat allen met meer dan 40 duurzame dagen > extra kansen + hoofdprijs 

foreach($bedrijf as $_k => $_v){
	print "<li><b>" . $_k . "</b>";	
	for ( $i=0 ; $i < count($prijzen[$_k]) ; $i++ ){
		switch ($i) {
			case 0:
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;   
			case 1:
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;  
			case 2:
				if ( count($bedrijven40plus[$_k]) > 0 ) 
				{ 
				$prijsaandeelnemer = array_rand($bedrijven40plus[$_k],1); 
				}
				else
				{ 
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				} 
				break;  
			case 3:
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;  
			case 4:
				if ( count($bedrijven40plus[$_k]) > 0 ) 
				{ 
				$prijsaandeelnemer = array_rand($bedrijven40plus[$_k],1); 
				}
				else
				{ 
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				} 
				break;  
			case 5:
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;  
			case 6:
				if ( count($bedrijven40plus[$_k]) > 0 ) 
				{ 
				$prijsaandeelnemer = array_rand($bedrijven40plus[$_k],1); 
				}
				else
				{ 
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				} 
				break;  
			case 7:
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;  
			case 8:
				if ( count($bedrijven40plus[$_k]) > 0 ) 
				{ 
				$prijsaandeelnemer = array_rand($bedrijven40plus[$_k],1); 
				}
				else
				{ 
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				} 
				break;  
			default: 
				$prijsaandeelnemer = array_rand($bedrijven20plus[$_k],1); 
				break;  
			}
			$deelnemers[$prijsaandeelnemer]['Prijs'] = $prijzen[$_k][$i];
			print "<li>" . $prijzen[$_k][$i] . " aan " . $bedrijven[$_k][$prijsaandeelnemer]['Voornaam']. " " . $bedrijven[$_k][$prijsaandeelnemer]['Naam'] . " (" . $bedrijven[$_k][$prijsaandeelnemer]['Duurzaam'] . ")"; 
			unset ($bedrijven20plus[$_k][$prijsaandeelnemer]);
			unset ($bedrijven40plus[$_k][$prijsaandeelnemer]);
		}
	}

// print_r($deelnemers); 
// controle print html table deelnemers 

$deelnemersovlpot = $deelnemersovl; 
$deelnemerswvlpot = $deelnemerswvl; 

$prijs_wvl = array_rand($deelnemerswvlpot,50);
for ($i = 0; $i < 50; $i++) {
    $deelnemers[$prijs_wvl[$i]]['Prijs'] = "WVL Fietsbox"; 
    $bedrijf[$deelnemers[$prijs_wvl[$i]]['Bedrijf']]['aantalWVL']++; 
    }

$prijs_ovl = array_rand($deelnemersovlpot,39);
for ($i = 0; $i < 39; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Koerstrui m/v"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,20);
for ($i = 0; $i < 20; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Trainingsvest"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,30);
for ($i = 0; $i < 30; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Wielersokken"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,5);
for ($i = 0; $i < 5; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Trap door fietsbox"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,5);
for ($i = 0; $i < 5; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Trap mee fietsbox"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,5);
for ($i = 0; $i < 5; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Trap uit fietsbox"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }

$prijs_ovl = array_rand($deelnemersovlpot,100);
for ($i = 0; $i < 100; $i++) {
    $deelnemers[$prijs_ovl[$i]]['Prijs'] = "OVL Bon fietsknooppuntkaart"; 
    $bedrijf[$deelnemers[$prijs_ovl[$i]]['Bedrijf']]['aantalOVL']++; 
	 unset ($deelnemersovlpot[$prijs_ovl[$i]]); 
    }







// controle print html table bedrijven
print "<table border='1'>";
print "<tr>";
print "<td>bedrijf</td>";
print "<td>provincie</td>";
print "<td>geactiveerd</td>";
print "<td>deelnemers</td>";
print "<td>deelnemers gt 20</td>";
print "<td>co2</td>";
print "<td>max prijs</td>";
print "<td>aantal prijs</td>";
print "<td>aantal OVL</td>";
print "<td>aantal WVL</td>";
print "<td>prijzen</td>";
print "</tr>";
foreach($bedrijf as $_k => $_v){
	print "<tr>";
	print "<td>" . $_k . "</td>";
	print "<td>" . $_v['Provincie'] . "</td>";
	print "<td>" . $_v['geactiveerd'] . "</td>";
	print "<td>" . $_v['deelnemers'] . "</td>";
	print "<td>" . $_v['deelnemers20'] . "</td>";
	print "<td>" . $_v['CO2'] . "</td>";
	print "<td>" . $bedrijf[$_k]['maxprijs'] . "</td>";
	print "<td>" . $bedrijf[$_k]['aantalprijs'] . "</td>";
	print "<td>" . $bedrijf[$_k]['aantalOVL'] . "</td>";
	print "<td>" . $bedrijf[$_k]['aantalWVL'] . "</td>";
	$aantal_toegekend = $aantal_toegekend + $bedrijf[$_k]['aantalprijs']; 
	print "<td>";
	print_r ($prijzen[$_k]);
	print "</td>";
	print "</tr>";
	
}	
print "</table>"; 

print "<li>Aantal prijzen toegekend: " . $aantal_toegekend; 


print "<table border='1'>";
print "<tr>";
print "<td>userid</td>";
print "<td>bedrijf</td>";
print "<td>provincie</td>";
print "<td>email</td>";
print "<td>voornaam</td>";
print "<td>naam</td>";
print "<td>duurzaam</td>";
print "<td>co2</td>";
print "<td>prijs</td>";
print "</tr>";
foreach($deelnemers as $_k => $_v){
	print "<tr>";
	print "<td>" . $_v['Userid'] . "</td>";
	print "<td>" . $_v['Bedrijf'] . "</td>";
	print "<td>" . $_v['Provincie'] . "</td>";
	print "<td>" . $_v['Email'] . "</td>";
	print "<td>" . $_v['Voornaam'] . "</td>";
	print "<td>" . $_v['Naam'] . "</td>";
	print "<td>" . $_v['Duurzaam'] . "</td>";
	print "<td>" . $_v['CO2'] . "</td>";
	print "<td>" . $_v['Prijs'] . "</td>";
	print "</tr>";
}	
print "</table>"; 


?>
</body>
</html>
