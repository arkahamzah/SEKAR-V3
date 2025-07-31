<?php
if (extension_loaded('ldap')) {
    echo "LDAP extension is loaded!";
    echo "<br>LDAP version: " . ldap_8859_to_t61("test");
} else {
    echo "LDAP extension is NOT loaded!";
}