@ECHO OFF
ECHO ********************************************
ECHO * aEGiS nanoweb installer v1.0 for Windows *
ECHO ********************************************
ECHO.

SET INSTROOT=c:\nanoweb

ECHO Installing in %INSTROOT%
ECHO.

mkdir %INSTROOT%
mkdir %INSTROOT%\modules
mkdir %INSTROOT%\www
mkdir %INSTROOT%\log
mkdir %INSTROOT%\tmp

ECHO Installing nanoweb.php

copy src\nanoweb.php %INSTROOT% /Y

ECHO Installing modules

xcopy modules %INSTROOT%\modules /E /Q /Y

ECHO Installing default configuration

copy conf\nanoweb-win.conf %INSTROOT%\nanoweb.conf /-Y
copy conf\modules-win.conf %INSTROOT%\modules.conf /-Y
copy conf\vhosts-win.conf %INSTROOT%\vhosts.conf /-Y
copy conf\mime.types %INSTROOT%\mime.types /-Y
copy conf\default.theme %INSTROOT%\default.theme /-Y
copy conf\fancy.theme %INSTROOT%\fancy.theme /-Y
copy conf\nanoweb.theme %INSTROOT%\nanoweb.theme /-Y

ECHO Installing default WWW root

xcopy www %INSTROOT%\www /E /Q
copy docs\* %INSTROOT%\www\manual /Y

ECHO.

ECHO @ECHO OFF > %INSTROOT%\nanostart.bat
ECHO c:\php\php-cli.exe %INSTROOT%\nanoweb.php --config=%INSTROOT%\nanoweb.conf >> %INSTROOT%\nanostart.bat

ECHO Done
