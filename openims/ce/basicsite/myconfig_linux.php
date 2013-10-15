<?

global $myconfig;

$myconfig["windows"] = "no";
$myconfig["linux"] = "yes";

$myconfig["tmp"] = "/tmp/";
$myconfig["hasgzlib"] = "yes"; // use gzip which is build into PHP
$myconfig["hasgzcompress"] = "yes"; // gzcompress and gzuncompress functions
$myconfig["gzipcommand"] = "/bin/gzip";
$myconfig["gunzipcommand"] = "/bin/gunzip";
$myconfig["tarcommand"] = "/bin/tar"; // path to tar executable
$myconfig["localsendmail"] = "yes"; // use local sendmail or relay to other server

$myconfig["diff"] = "/usr/bin/diff";
$myconfig["unzip"] = "/usr/bin/unzip";
$myconfig["zip"] = "/usr/bin/zip";


?>
