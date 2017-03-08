set PHPDIR=C:\Program Files (x86)\Internet\Ampps\php
set PEGDIR=D:\Benutzer\Juergen\Documents\Programmieren\Git Projects\Git Hub\cops\php-peg
set PATH=%PHPDIR%;%PATH%
if "%1" == "" goto inline
php.exe .\cli.php ..\virtualLibraries\tests\%1.peg.inc >..\virtualLibraries\tests\%1.php
goto done
:inline
php.exe .\cli.php ..\virtualLibraries\virtualLibrariesParser.peg.inc >..\virtualLibraries\virtualLibrariesParser.php
:done