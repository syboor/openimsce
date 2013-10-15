<?
   $myconfig["ce_sites"]["multifile"] = "yes";
   $myconfig["ce_sites"]["versioning"] = "yes";
   $myconfig["ce_sites"]["customversions"] = '
     $version = "$major";
     if ($minor) $version  .= "." . $minor;
     $version .= " ";    
   ';

   $myconfig["ce_sites"]["cookielogin"] = "yes";
   $myconfig["ce_sites"]["cookieloginsettings"]["logintemplate"] = "ff2459134c9b58e847ecac2758b38447";

   $myconfig["ce_sites"]["tinymcedmsroot"] = "866c7266e324f42adc38971ea26f6c61";
  ?>
