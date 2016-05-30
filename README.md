# README #

##Instructions to setting up scripts###

###Operating System:

The PHP scripts mainly runs on MAC OS, but running on Windows is theoretically possible though not tested. 

###Required modules:

####PHP 7.0
Uses latest PHP 7.0 on MAC. To install PHP on MAC using brew, follow the next steps.

1. Open terminal
2. Install brew, if it is not installed, by using the enter the follow command in the terminal
3. /usr/bin/ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
4. Install PHP by enter the following command once \textit{brew} has been installed
5. brew install php70

####MySQL
1. Uses Ver 14.14 Distrib 5.7.9, for osx10.9 (x86_64) for MAC OS and was installed using the guide from the following website. MySQL is required for hosting a local database to store our research data. 
2. https://coolestguidesontheplanet.com/get-apache-mysql-php-and-phpmyadmin-working-on-osx-10-11-el-capitan/

####PHPUnit
Uses PHPUnit 5.2.12 for unit testing.

1. Open terminal
2. Install brew, if it is not installed, by using the enter the follow command in the terminal
3. /usr/bin/ruby -e "\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"
4. Install PHPUnit by enter the following command once brew has been installed
5. brew install phpunit
