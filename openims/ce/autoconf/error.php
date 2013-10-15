<html>
<p>You can not access the configuration tool because it is protected against unauthorized access.</>

<p>To gain access, please login to your server, open the file <b><?= $_SERVER['DOCUMENT_ROOT']; ?>/openims/ce/autoconf/.htaccess</b>, and include your IP address in the file.</p>

<p>Your IP address is <b><?= $_SERVER['REMOTE_ADDR']; ?></b>.<p>

<p><font color="red"><b>After using the configuration tool, remove your IP address from <?= $_SERVER['DOCUMENT_ROOT']; ?>/openims/ce/autoconf/.htaccess immediately.</b></font></p>

</html>
