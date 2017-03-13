set PHPDIR=C:\Program Files (x86)\Internet\Ampps\php
set PEGDIR=..\vendor\hafriedlander\php-peg
set PATH=%PHPDIR%;%PATH%
if "%1" == "" goto inline
php.exe %PEGDIR%\cli.php .\%1.peg.inc >..\VirtualLibraries\%1.php
goto done
:inline
php.exe %PEGDIR%\cli.php .\VirtualLibrariesParser.peg.inc >..\VirtualLibraries\VirtualLibrariesParser.php
:done