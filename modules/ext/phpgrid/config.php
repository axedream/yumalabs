<?php
// PHP Grid database connection settings
define("PHPGRID_DBTYPE","mysqli"); // or mysqli
define("PHPGRID_DBHOST","10.1.9.46");
define("PHPGRID_DBUSER","user_remote");
define("PHPGRID_DBPASS","k8LDlv80XWNKYki5pofG1yM64rk0ZYfS8LampMTPwHsnk16ibWVUY2v2Iz3f62Vz");
define("PHPGRID_DBNAME","bi.etagi.com");

// Automatically make db connection inside lib
define("PHPGRID_AUTOCONNECT",0);

// Basepath for lib
define("PHPGRID_LIBPATH",dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR);
