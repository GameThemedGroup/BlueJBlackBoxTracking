# README #

##Structure of Project
There are two major folders in this project containing files which pertains to each folder.

1. Documentations
2. Implementations

### Documentations

Contains documents generated throughout this Capstone project: powerpoint, project report, and instructions on setting up a machine for BlueJ data collection.

1. BlueJ Data Collection Instruction
   * Detailed steps with images to setup BlueJ data collection in a docx file
2. Powerpoint 
   * Contains to formats of the Capstone defense presentation: one in Google Slides and one in Microsoft Powerpoint
3. Report 
   * Contains final project report in PDF and a zip with LaTeX
4. Local Database Backup
   * A backup .sql file for "slice" of data download to analyze

### Implementations

Contains source code / scripts for data transfer, data analysis, and data visualization.

1. common
   * Script containing common functions shared by researchQuestions scripts and visualization. Functions like connecting to database (remote and local), data handling, etc.
2. dataTransfer 
   * Script to download our "slice" of data from Blackbox. ATTENTION: must have folders "checkpoints", "csv", and both emptied in order for the download to complete when running it for the first time.
3. researchQuestions
   * Scripts for each of the 7 research questions posted by our professor. Each of them polls data from local database with visualization
4. tests
   * Script that was used to UnitTest from functionalities
5. visualization
   * Libraries and scripts required for generating graph onto the guiPage.php

##Required setup to run scripts##

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
