#%powershell1.0%
#
# File: pgo_controller.ps1
# Description: PGO automation.
# 	- Automate the download and deployment of PGI builds
#	- Set up IIS and Apache with PGI builds of PHP
#	- Run pgo.php to create profiling data
#	- Collect .pgc files and push to a remote location
#

## Example: pgo_controller.ps1 -PHP1 "5.3.8" -PHP1URL ""
## 
## PHP*URL must either be "5.3" or "5.4" indicating a snapshot build, or a comma-separated list of complete URLs to the
## TS and NTS .zip files.  This is because we can use the json files to scan for the latest revision and download the TS and NTS
## builds, but doing so for Release or Q/A builds is more difficult.
Param( $PHP="", $PHPURL="" )

Set-Location c:\php_pgo
$SERVER = "php-web02"
$LOCPHP = "C:\php\php-5.3.8-nts-Win32-VC9-x86\php.exe"  ## Used for pgo.php

$REVISION = ""
$PHPBuildDir = @()
$PHP2BuildDir = @()
$BaseBuildDir = "c:/php-pgo/phpbuilds"
$WebSvrPHPLoc = "\\php-web02\php"
$WebSvrApacheLoc = "\\php-web02\apache"

## Import needed functions
. .\web-utils.ps1
. .\setup-utils.ps1

## Simple logging function.
Function logger ( $msg )  {
	$logfile = "c:/php-pgo/log.txt"
	$msg = (get-date -format "yyyy-MM-dd HH:mm:ss")+" $msg"
	$msg | Out-File -Encoding ASCII -Append $logfile
}

## Stop all web services on the server
$( winrs -r:$SERVER net stop Apache2.2 )
$( winrs -r:$SERVER net stop w3svc )

## Download the PGI PHP builds.  We attempt to download three times, and exit if we fail to download any build.
## PHP*URL must either be "5.3" or "5.4" indicating a snapshot build, or a comma-separated list of
## complete URLs to the TS and NTS .zip files.
$status = ""
$loop = 0
do {
	switch ( $PHPURL )  {
		{ $PHPURL -notmatch "," }	{  ## Snapshot build
			$PHPBuildDir = php-getsnapbuilds( $PHPURL )
			if ( $PHPBuildDir -eq $false )  {
				logger "PGO Controller: php-getsnapbuilds() returned false, URL=$PHPURL."
				$loop++
				start-sleep -s 10
				continue
			}
			if ( $PHPBuildDir[0] -match "r\d{6}" )  {
				$PHP = $PHP+$matches[0]
			}
		}

		{ ($PHPURL -match "http\:\/\/") -and ($PHPURL -match ",") }  {  ## Links to specific builds.  Needed for Release and QA builds.
			$URIS = $PHPURL.Split(",")
			$status = download-build( $URIS[0] )
			$PHPBuildDir += $status
			if ( $status -eq $false )  {
				logger "PGO Controller: download-build() returned false, URL=$PHPURL[0]."
				$loop++
				start-sleep -s 10
				continue
			}
			$status = download-build( $URIS[1] )
			$PHPBuildDir += $status
			if ( $status -eq $false )  {
				logger "PGO Controller: download-build() returned false, URL=$PHPURL[1]."
				$loop++
				start-sleep -s 10
				continue
			}
		}

		default  {
			write-host "Syntax error in PHPURL parameter."
			exit
		}
	}
}  while ( ($status -eq $false) -and ($loop -lt 3) )  ## End Loop
if ( $status -eq $false )  {  exit  }


###################################################################################
## Setup and run the profiling tools.
##
$exts = "c:/php-pgo/conf/exts"
$tsbuild = ""
$ntsbuild = ""
$basever=""
foreach ( $build in $PHPBuildDir )  {
	$build = $build.split('/')
	$build = [string]$build[($build.length-1)]
	$build = $build -ireplace "\.zip", ""
	if ( $build -match 'nts' )  {  $ntsbuild = $build  }
	else  {  $tsbuild = $build  }
}
switch ( $tsbuild )  {
	{ $_ -match "php\-5\.3" }  { $basever = "5.3" }
	{ $_ -match "php\-5\.4" }  { $basever = "5.4" }
	default  { $basever = "5.4" }
}

logger "PGO Controller: Starting PHP configuration."
$trans = invoke-expression -command "$LOCPHP c:\php-pgo\pgo.php printnum"
$trans = $trans -ireplace "Total Transactions: ", ""
if ( (setup-php $exts $PHPBuildDir) -eq $false )  {
	logger "PGO Controller: setup-php() returned error."
	write-output "PGO Controller: setup-php() returned error."
	exit
}
if ( (setup-apache($tsbuild)) -eq $false )  {
	logger "PGO Controller: setup-apache() returned error."
	write-output "PGO Controller: setup-apache() returned error."
	exit
}
if ( (setup-iis($ntsbuild, $trans)) -eq $false )  {
	logger "PGO Controller: setup-iis() returned error."
	write-output "PGO Controller: setup-iis() returned error."
	exit
}


	##
	## Scenario #1 - Profiling without cache
	##
	logger 'Controller: Running Scenario #1 - Nocache'

	## Apache
	$phpini = "c:/php-pgo/conf/ini/php-$basever-pgo-ts.ini"
	if ( (php-configure $tsbuild $phpini) -eq $false )  {
		logger "PGO Controller: php-configure() returned error: $tsbuild, $phpini"
		exit
	}
	$( winrs -r:$SERVER net stop w3svc )
	$( winrs -r:$SERVER net stop Apache2.2 "&" net start Apache2.2 )
	invoke-expression -command "$LOCPHP c:\php-pgo\pgo.php $SERVER"

	## IIS
	$phpini = "c:/wcat/conf/ini/php-$basever-pgo-nts.ini"
	if ( (php-configure $ntsbuild $phpini) -eq $false )  {
		logger "PGP Controller: php-configure() returned error: $ntsbuild, $phpini"
		exit
	}
	$( winrs -r:$SERVER net stop Apache2.2 )
	$( winrs -r:$SERVER net stop w3svc "&" net start w3svc )
	$( winrs -r:$SERVER C:/windows/system32/inetsrv/appcmd start site /site.name:"Default Web Site" )
	invoke-expression -command "$LOCPHP c:\php-pgo\pgo.php $SERVER"

	##
	## Scenario #2 - Cache
	##
	if ( $basever -eq "5.3")  {  ## For now must skip the cache scenarios for 5.4

	## Apache
	$phpini = "c:/php-pgo/conf/ini/php-$basever-pgo-ts-apc.ini"
	if ( (php-configure $tsbuild $phpini) -eq $false )  {  ## Apache
		logger "Controller: php-configure() returned error: $tsbuild, $phpini"
		exit
	}
	$( winrs -r:$SERVER net stop w3svc )
	$( winrs -r:$SERVER net stop Apache2.2 "&" net start Apache2.2 )
	invoke-expression -command "$LOCPHP c:\php-pgo\pgo.php $SERVER"

	##
	## Scenario #3 - Cache with igbinary (Apache)
	##
	$phpini = "c:/php-pgo/conf/ini/php-$basever-pgo-ts-apc-igbinary.ini"
	if ( (php-configure $tsbuild $phpini) -eq $false )  {  ## Apache
		logger "Controller: php-configure() returned error: $tsbuild, $phpini"
		exit
	}
	$( winrs -r:$SERVER net stop w3svc )
	$( winrs -r:$SERVER net stop Apache2.2 "&" net start Apache2.2 )
	invoke-expression -command "$LOCPHP c:\php-pgo\pgo.php $SERVER"

	}  ## end if $basever=5.4

	$( winrs -r:$SERVER net stop w3svc )
	$( winrs -r:$SERVER net stop Apache2.2 )
	Start-Sleep -s 5


##
## Collect and .zip up the .pgc files
$date = (get-date).Year.ToString('00')
$date += (get-date).Month.ToString('00')
$date += (get-date).Day.ToString('00')

$dirname = "$date-$PHP"
mkdir 'c:\php-pgo\pgc\'+$dirname
mkdir 'c:\php-pgo\pgc\'+$dirname+'ts'
mkdir 'c:\php-pgo\pgc\'+$dirname+'nts'
copy-item -Force "$tsbuild/*.pgc" -destination 'c:\php-pgo\pgc\'+$dirname+'ts' -recurse
copy-item -Force "$ntsbuild/*.pgc" -destination 'c:\php-pgo\pgc\'+$dirname+'nts' -recurse
##FIXME:  zip up .pgc files
