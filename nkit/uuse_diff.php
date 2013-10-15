<?
/***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***
 *                                                                                                                * 
 *       This sourcecode file is part of OpenIMS CE (Community Edition).                                          *
 *       OpenIMS CE (Community Edition) is a program developed by OpenSesame ICT B.V.                             *
 *       Copyright (C) 2001-2011 OpenSesame ICT B.V. Meerwal 13, NL-3432ZV, Nieuwegein.                           *
 *                                                                                                                *
 *       This program is free software; you can redistribute it and/or modify it under                            *
 *       the terms of the GNU General Public License version 3 as published by the                                *
 *       Free Software Foundation with the addition of the following permission added                             *
 *       to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK                             *
 *       IN WHICH THE COPYRIGHT IS OWNED BY OpenSesame ICT, OpenSesame ICT DISCLAIMS                              *
 *       THE WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.                                                  *
 *                                                                                                                *
 *       This program is distributed in the hope that it will be useful, but WITHOUT                              *
 *       ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS                            *
 *       FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more                                   *
 *       details.                                                                                                 *
 *                                                                                                                *
 *       You should have received a copy of the GNU General Public License along with                             *
 *       this program; if not, see http://www.gnu.org/licenses or write to the Free                               *
 *       Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA                                   *
 *       02110-1301 USA.                                                                                          *
 *                                                                                                                *
 *       You can contact OpenSesame ICT B.V. at Meerwal 13, NL-3432 ZV, Nieuwegein                                *
 *       or at e-mail address info@osict.com.                                                                     *
 *                                                                                                                *
 *       The interactive user interfaces in modified source and object code versions                              *
 *       of this program must display Appropriate Legal Notices, as required under                                *
 *       Section 5 of the GNU General Public License version 3.                                                   *
 *                                                                                                                *
 *       In accordance with Section 7(b) of the GNU General Public License version 3,                             *
 *       these Appropriate Legal Notices must retain the display of the "OpenIMS" logo.                           *
 *       If the display of the logo is not reasonably feasible for technical reasons, the                         *
 *       Appropriate Legal Notices must display the words "Powered by OpenIMS".                                   *
 *                                                                                                                *
 *       Please note the OpenIMS EE (Enterprise Edition) license explicitly forbids                               *
 *       transfer of code or concepts from OpenIMS EE to OpenIMS CE.                                              *
 *                                                                                                                * 
 ***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***/



function DIFF_Text2Lines ($t) 
{
  $t = str_replace (chr(13), " ", $t);
  $t = str_replace (chr(10), " ", $t);
  for ($i=1; $i<10; $i++) {
    $t = str_replace ("  ", " ", $t);
  }
  $t = str_replace (".", ".".chr(10), $t);
  $t = str_replace (",", ",".chr(10), $t);
  $t = str_replace ("!", ".".chr(10), $t);
  $t = str_replace ("?", ".".chr(10), $t);
  $t = str_replace (":", ":".chr(10), $t);
  $t = str_replace (";", ";".chr(10), $t);
  $t = str_replace ("|", " ".chr(10), $t); // MS-Word tables
  return $t;
}

function DIFF_Line2Lines ($t)
{
  for ($i=1; $i<10; $i++) {
    $t = str_replace ("  ", " ", $t);
  }
  $t = str_replace (" ", chr(10), $t);
  return $t;
}

function DIFF_MiniOld ($oldline, $newline, $sourcecode) // for checkup purposes if DIFF_Mini ever changes
{
  global $myconfig;
  $f1 = $myconfig["tmp"]."comp_".N_Random();  
  $f2 = $myconfig["tmp"]."comp_".N_Random();  
  N_WriteFile ($f1, DIFF_Line2Lines ($oldline));
  N_WriteFile ($f2, DIFF_Line2Lines ($newline));
  $diff = $myconfig["diff"];
  $result = `$diff $f1 $f2`; 
  $result = str_replace (chr(13).chr(10), chr(10), $result);
  $result = str_replace (chr(10).chr(13), chr(10), $result);
  $result = str_replace (chr(10).chr(10), chr(10), $result);
  unlink ($f1);
  unlink ($f2);
  $oldlines = explode (chr(10), DIFF_Line2Lines ($oldline));
  $newlines = explode (chr(10), DIFF_Line2Lines ($newline));
  $list = explode (chr(10), $result.chr(10)."0");
  if ($result) {
    reset ($list);
    while (list(,$line)=each($list)) {
//      echo "xxx ".N_XML2HTML(serialize($line))." xxx ".substr ($line, 0, 1)."<br>";
      if (strpos ("xx0123456789", substr ($line, 0, 1))) {
        if ($line!="0") {
          $o = 0; 
          $n = 0;
          if (strpos($line,"d")) $mode="d";
          if (strpos($line,"a")) $mode="a";
          if (strpos($line,"c")) $mode="c";
          if (strpos($line,"d")) list ($o, $n) = explode ("d", $line);
          if (strpos($line,"a")) list ($o, $n) = explode ("a", $line);
          if (strpos($line,"c")) list ($o, $n) = explode ("c", $line);
          if (strpos ($o, ",")) {
            list ($o1, $o2) = explode (",", $o);
          } else {
            $o1 = $o;
            $o2 = $o;
          }
          if (strpos ($n, ",")) {
            list ($n1, $n2) = explode (",", $n);
          } else {
            $n1 = $n;
            $n2 = $n;
          }
          $o1--;
          $o2--;
          $n1--;
          $n2--;
//          echo "COMMAND $mode ($o1:$o2:$n1:$n2)<br>";
          if ($mode=="d") {
            for ($o=$o1; $o<=$o2; $o++) $oldlines[$o] = '<strike><font color="#000080">'.$oldlines[$o].'</font></strike>';
          } else if ($mode=="a") {
            for ($n=$n1; $n<=$n2; $n++) $newlines[$n] = '<font color="#FF0000">'.$newlines[$n].'</font>';
          } else if ($mode=="c") {
            for ($o=$o1; $o<=$o2; $o++) $oldlines[$o] = '<strike><font color="#000080">'.$oldlines[$o].'</font></strike>';
            for ($n=$n1; $n<=$n2; $n++) $newlines[$n] = '<font color="#FF0000">'.$newlines[$n].'</font>';
          }
        }
      }
    }    
    $oldline = "";
    reset ($oldlines);
    while (list(,$word)=each($oldlines)) {
      $oldline .= " ".$word;
    }
    $newline = "";
    reset ($newlines);
    while (list(,$word)=each($newlines)) {
      $newline .= " ".$word;
    }
    $oldline = str_replace ('</font></strike> <strike><font color="#000080">', ' ', $oldline);
    $newline = str_replace ('</font> <font color="#FF0000">', ' ', $newline);
    if ($sourcecode)
      return array ($oldline, $newline);
    else
      return array (trim($oldline), trim($newline));
  } else {
    return array ($oldline, $newline);
  }
}

function DIFF_Mini ($oldline, $newline, $sourcecode)
{
  global $myconfig;
  $f1 = $myconfig["tmp"]."comp_".N_Random();  
  $f2 = $myconfig["tmp"]."comp_".N_Random();  
  N_WriteFile ($f1, DIFF_Line2Lines ($oldline));
  N_WriteFile ($f2, DIFF_Line2Lines ($newline));
  $diff = $myconfig["diff"];
  $result = `$diff $f1 $f2`; 
  $result = str_replace (chr(13).chr(10), chr(10), $result);
  $result = str_replace (chr(10).chr(13), chr(10), $result);
  $result = str_replace (chr(10).chr(10), chr(10), $result);
  unlink ($f1);
  unlink ($f2);
  $oldlines = explode (chr(10), DIFF_Line2Lines ($oldline));
  $newlines = explode (chr(10), DIFF_Line2Lines ($newline));
  $list = explode (chr(10), $result.chr(10)."0");
  if ($result) {
    reset ($list);
    while (list(,$line)=each($list)) {
//      echo "xxx ".N_XML2HTML(serialize($line))." xxx ".substr ($line, 0, 1)."<br>";
      if (strpos ("xx0123456789", substr ($line, 0, 1))) {
        if ($line!="0") {
          $o = 0; 
          $n = 0;
          if (strpos($line,"d")) $mode="d";
          if (strpos($line,"a")) $mode="a";
          if (strpos($line,"c")) $mode="c";
          if (strpos($line,"d")) list ($o, $n) = explode ("d", $line);
          if (strpos($line,"a")) list ($o, $n) = explode ("a", $line);
          if (strpos($line,"c")) list ($o, $n) = explode ("c", $line);
          if (strpos ($o, ",")) {
            list ($o1, $o2) = explode (",", $o);
          } else {
            $o1 = $o;
            $o2 = $o;
          }
          if (strpos ($n, ",")) {
            list ($n1, $n2) = explode (",", $n);
          } else {
            $n1 = $n;
            $n2 = $n;
          }
          $o1--;
          $o2--;
          $n1--;
          $n2--;
//          echo "COMMAND $mode ($o1:$o2:$n1:$n2)<br>";
          if ($mode=="d") {
            for ($o=$o1; $o<=$o2; $o++) $oldlines[$o] = '<strike><font color="#000080">'.$oldlines[$o].'</font></strike>';
          } else if ($mode=="a") {
            for ($n=$n1; $n<=$n2; $n++) $newlines[$n] = '<font color="#FF0000">'.$newlines[$n].'</font>';
          } else if ($mode=="c") {
            for ($o=$o1; $o<=$o2; $o++) $oldlines[$o] = '<strike><font color="#000080">'.$oldlines[$o].'</font></strike>';
            for ($n=$n1; $n<=$n2; $n++) $newlines[$n] = '<font color="#FF0000">'.$newlines[$n].'</font>';
          }
        }
      }
    }    
    $oldline = "";
    reset ($oldlines);
    while (list(,$word)=each($oldlines)) {
      $oldline .= " ".$word;
    }
    $newline = "";
    reset ($newlines);
    while (list(,$word)=each($newlines)) {
      $newline .= " ".$word;
    }
    $oldline = str_replace ('</font></strike> <strike><font color="#000080">', ' ', $oldline);
    $newline = str_replace ('</font> <font color="#FF0000">', ' ', $newline);
    if ($sourcecode)
      return array ($oldline, $newline);
    else
      return array (trim($oldline), trim($newline));
  } else {
    return array ($oldline, $newline);
  }
}

function DIFF_Source2Lines ($t)
{
  $t = htmlentities ($t);
  $t = str_replace (chr(13), chr(10), $t);
  $t = str_replace (chr(10).chr(10), chr(10)." <br> ", $t);
  return $t;
}

function DIFF_SourceCode ($t1, $t2) 
{
  return DIFF (DIFF_Source2Lines($t1), DIFF_Source2Lines($t2), true);
}

function DIFF_Trim ($t, $sourcecode) {
  $t = trim ($t);
  if (substr ($t, 0, 4) == "<br>") $t = substr ($t, 4);
  if (substr ($t, strlen($t)-4) == "<br>") $t = substr ($t, 0, strlen($t)-4);
  $t = trim ($t);
  if (substr ($t, 0, 4) == "<br>") $t = substr ($t, 4);
  if (substr ($t, strlen($t)-4) == "<br>") $t = substr ($t, 0, strlen($t)-4);
  $t = trim ($t);
  $t = str_replace ("  ", " &nbsp; ", $t);
  $t = str_replace ("&nbs;  &nbsp;", "&nbsp;&nbsp;&nbsp;&nbsp;", $t);
  return $t;
}

function DIFF ($t1, $t2, $sourcecode=false)
{
  $key = DFC_Key ("DIFF ($t1, $t2) v9");
  if (DFC_Exists($key)) return DFC_Read ($key);
  global $myconfig;
  $f1 = $myconfig["tmp"]."comp_".N_Random();  
  $f2 = $myconfig["tmp"]."comp_".N_Random();
  if ($sourcecode) {
    N_WriteFile ($f1, $t1);
    N_WriteFile ($f2, $t2);
  } else {
    N_WriteFile ($f1, DIFF_Text2Lines($t1));
    N_WriteFile ($f2, DIFF_Text2Lines($t2));
  }
  $diff = $myconfig["diff"];
  $result = `$diff -b -B $f1 $f2`; 
  $result = str_replace (chr(13).chr(10), chr(10), $result);
  $result = str_replace (chr(10).chr(13), chr(10), $result);
  $result = str_replace (chr(10).chr(10), chr(10), $result);
  unlink ($f1);
  unlink ($f2);
  $list = explode (chr(10), $result.chr(10)."0");
  $ctr=0;
  if ($result) {
    reset($list);
    while (list(,$line)=each($list)) {
      if (strpos (" 012346789", substr ($line, 0, 1))) {
        if ($old && $new) {
          if (trim($old)!=trim($new)) {
            $ctr++;
            $res[$ctr]["action"] = "change";
            $res[$ctr]["old"] = $old;
            $res[$ctr]["new"] = $new;
          }
        } else if ($old) {
          $ctr++;
          $res[$ctr]["action"] = "delete";
          $res[$ctr]["old"] = DIFF_Trim ($old, $sourcecode);
        } else if ($new) {
          $ctr++;
          $res[$ctr]["action"] = "insert";
          $res[$ctr]["new"] = DIFF_Trim ($new, $sourcecode);
        }
        $old = "";
        $new = "";
      } else if (substr ($line, 0, 1)==">") {
        $new = $new." ".substr ($line, 2);
      } else if (substr ($line, 0, 1)=="<") {
        $old = $old." ".substr ($line, 2);
      }
    }    
    for ($i=1; $i<=$ctr; $i++)
    {
      if ($res[$i]["action"]=="change") {
        $res[$i]["old"] = DIFF_Trim ($res[$i]["old"], $sourcecode);
        $res[$i]["new"] = DIFF_Trim ($res[$i]["new"], $sourcecode);
        list($res[$i]["old"], $res[$i]["new"]) = DIFF_Mini ($res[$i]["old"], $res[$i]["new"], $sourcecode);
        if (!$res[$i]["old"]) $res[$i]["action"] = "insert";
        if (!$res[$i]["new"]) $res[$i]["action"] = "delete";
      }
    }     
    return DFC_Write ($key, $res);
  } else {
    return DFC_Write ($key, array());
  }
}

function DIFF_Test ()
{
  $text1 = '
De voordelen van Open Source. VERPLAATSTE ZIN.

Door de vrije beschikbaarheid van de broncode heeft elke gebruiker de vrijheid om deze software te kopiëren, te wijzigen en te exploiteren. Dit in tegenstelling tot de proprietary (gesloten) VERWIJDERDETEKST software van bedrijven als Microsoft, Oracle, Sun, IBM en andere, waarvan de licenties sterke beperkingen opleggen aan het gebruik en de verdere verspreiding ervan.

De aan Open Source verbonden vrijheid heeft geleid tot een aantal interessante effecten: 

Betere kwaliteit: aangezien iedere gebruiker kan beschikken over de broncode, kan ook iedereen de kwaliteit daarvan toetsen. Open Source wordt meestal ontwikkeld en verbeterd door netwerken van zeer veel programmeurs die samenwerken via het Internet. Dit betekent dat de software zeer snel evolueert tot robuuste, stabiele en onderhoudsvrije software. Bovendien worden eventuele fouten snel opgespoord en in een hoog tempo verholpen. Dit ontwikkelingsmodel voor software zorgt ervoor dat het resulterende product beter van kwaliteit is dan een vergelijkbaar product gemaakt door over het algemeen een veel kleinere groep programmeurs binnen één bedrijf.

HELE LANGE ZIN WAAR STRAKS EEN STUK VAN VERWIJDERD WORD OM TE KIJKEN OF DAT DAN WEL GOED WORDT AFGEHANDELD.

Hoge innovatiesnelheid: door het publiceren van oplossingen voor problemen binnen de kennisnetwerken van programmeurs over het Internet is de Open Source gemeenschap een grote motor achter de ontwikkeling van nieuwe software technieken.  Dit heeft er bijvoorbeeld al voor gezorgd dat bedrijven als IBM en HP hun eigen UNIX varianten steeds meer laten varen en zich meer en meer richten op Linux. Ook een bedrijf als Oracle adviseert voor zijn database tegenwoordig vaak geen Windows of Solaris meer maar Linux als operatingsysteem.


COMPLEET VERWIJDERDE ZIN.

Betere prijs/prestatie verhouding: Open Source oplossingen zijn niet gratis, hoewel men dit vaak denkt. Een oplossing bestaat immers uit meer dan alleen software. Zo zal er bijvoorbeeld altijd expertise, ondersteuning en ook hardware nodig zijn. Open Source veroorzaakt een verschuiving in het businessmodel, van licentie gebaseerd naar een diensten model. Zo richt OpenSesame ICT zich op het leveren van totaal oplossingen, en 

is het aloude "dozen schuiven" daar in feite geen onderdeel meer van. Ook  gaat Open Source over het algemeen veel economischer om met systeemeisen, waardoor de rat race van telkenmale de aanschaf van nieuwe hardware & nieuwe besturingssoftware wordt doorbroken. De betere prijs/prestatie verhouding is niet alleen gunstig voor bedrijven (verminderen van de automatiseringskosten, in vakjargon TCO) maar ook  voor publieke sectoren als het onderwijs en de overheid. 

Gedreven door eindgebruikers: de ontwikkeling van Open Source wordt hoofdzakelijk gedreven door de wensen van de gebruiker. Gebruikers hebben veelal direct contact met ontwikkelaars van de software. Door de gebruiker gewenste uitbreidingen worden bij voldoende draagvlak in het product verwerkt. Vaak is hier dan sprake van een toename in de VERANDERDETEKSTVOOR arbeidsproductiviteit. Indien VERANDERDETEKSTVOOR voldoende draagvlak ontbreekt heeft de gebruiker de vrijheid om gewenste functies zelf aan de software toe te voegen of te laten toevoegen door bedrijven als OpenSesame ICT, die hierin zijn gespecialiseerd. Ook dan is toename van de arbeidsproductiviteit te bereiken.

Copyright © 2002 OpenSesame ICT. All Rights Reserved.
  ';
  $text2 = '
De voordelen van Open Source.

Door de vrije beschikbaarheid van de broncode heeft elke gebruiker de vrijheid om deze software te kopiëren, te wijzigen en te exploiteren. Dit in tegenstelling tot de proprietary (gesloten) software van bedrijven als Microsoft, Oracle, Sun, IBM en andere, waarvan de licenties sterke beperkingen opleggen aan het gebruik en de verdere verspreiding ervan.

De aan Open Source verbonden vrijheid heeft geleid tot een aantal interessante effecten: 

Betere kwaliteit: aangezien iedere gebruiker kan beschikken over de broncode, kan ook iedereen de kwaliteit daarvan toetsen. Open Source wordt meestal ontwikkeld en verbeterd door netwerken van zeer veel programmeurs die samenwerken via het Internet. Dit betekent dat de software zeer snel evolueert tot robuuste, stabiele en onderhoudsvrije software. Bovendien worden eventuele fouten snel opgespoord en in een hoog tempo verholpen. Dit ontwikkelingsmodel voor software zorgt ervoor dat het resulterende product beter van kwaliteit is dan een vergelijkbaar product gemaakt door over het algemeen een veel kleinere groep programmeurs binnen één bedrijf.

HELE LANGE ZIN.

Hoge innovatiesnelheid: door het publiceren van oplossingen voor problemen binnen de kennisnetwerken van programmeurs over het Internet is de Open Source gemeenschap een grote motor achter de ontwikkeling van nieuwe software technieken.  Dit heeft er bijvoorbeeld al voor gezorgd dat bedrijven als IBM en HP hun eigen TOEGEVOEGDETEKST UNIX varianten steeds meer laten varen en zich meer en meer richten op Linux. Ook een bedrijf als Oracle adviseert voor zijn database tegenwoordig vaak geen Windows of Solaris meer maar Linux als operatingsysteem.

Betere prijs/prestatie verhouding: Open Source oplossingen zijn niet gratis, hoewel men dit vaak denkt. Een oplossing bestaat immers uit meer dan alleen software. Zo zal er bijvoorbeeld altijd expertise, ondersteuning en ook hardware nodig zijn. Open Source veroorzaakt een verschuiving in het businessmodel, van licentie gebaseerd naar een diensten model. Zo richt OpenSesame ICT zich op het leveren van totaal oplossingen, en 

is het aloude "dozen schuiven" daar in feite geen onderdeel meer van. Ook  gaat Open Source over het algemeen veel economischer om met systeemeisen, waardoor de rat race van telkenmale de aanschaf van nieuwe hardware & nieuwe besturingssoftware wordt doorbroken. De betere prijs/prestatie verhouding is niet alleen gunstig voor bedrijven (verminderen van de automatiseringskosten, in vakjargon TCO) maar ook  voor publieke sectoren als het onderwijs en de overheid. 

Gedreven door eindgebruikers: de ontwikkeling van Open Source wordt hoofdzakelijk gedreven door de wensen van de gebruiker. Gebruikers hebben veelal direct contact met ontwikkelaars van de software. Door de gebruiker gewenste uitbreidingen worden bij voldoende draagvlak in het product verwerkt. Vaak is hier dan sprake van een toename in de VERANDERDETEKSTNA arbeidsproductiviteit. Indien VERANDERDETEKSTNA voldoende draagvlak ontbreekt heeft de gebruiker de vrijheid om gewenste functies zelf aan de software toe te voegen of te laten toevoegen door bedrijven als OpenSesame ICT, die hierin zijn gespecialiseerd. Ook dan is toename van de arbeidsproductiviteit te bereiken.

COMPLEET NIEUWE ZIN.

Copyright © 2002 OpenSesame ICT. All Rights Reserved.  VERPLAATSTE ZIN.
  ';
  N_EO (DIFF ($text1, $text2));
}

?>