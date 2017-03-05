set PHPDIR=C:\Program Files (x86)\Internet\Ampps\php
set PEGDIR=D:\Benutzer\Juergen\Documents\Programmieren\Git Projects\Git Hub\cops\php-peg
set PATH=%PHPDIR%;%PATH%
php.exe .\cli.php ..\virtualLibraries\virtualLibrariesParser.peg.inc >..\virtualLibraries\virtualLibrariesParser.php
