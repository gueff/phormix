# Phormix

## Overview
- [English, EN](#EN)
- [Deutsch, DE](#DE)

<a id="EN"></a>[EN]

**Check HTML-Forms with Phormix**

## Installation
create the composer.json file with following content:
~~~
{
    "require": {
        "gueff/phormix":"dev-master"
    }
}
~~~
run installation
~~~
$ composer install
~~~

## Process
- create a configuration file (JSON), which describes the later HTML form
- Build the HTML Form
- At backend* Phormix will check the transmitted data from the HTML form against the configuration

_\*Receiving Unit (Crontroller, PHP-Script o.a.)_


## Usage
Examples:

~~~php
// instantiate, 
// load config, run
$oPhormix = new \Phormix();
$oPhormix->init('/var/www/App/formular.json')->run();

// instantiate, 
// set different session prefix,
// load config, run
$oPhormix = new \Phormix();
$oPhormix->setSessionPrefix('myPhormixCheck')
->init('/var/www/App/formular.json')
->run();

// instantiate, 
// load config,
// set a certain identifier
// run
$oPhormix = new \Phormix();
$oPhormix->setConfigArrayFromJsonFile($sAbsPathToConfigFile)
->setIdentifier($sIdentifier)
->run();

// instantiate, 
// set a proper array as config,
// set a certain identifier
// run
$oPhormix = new \Phormix();
$oPhormix->setConfigArray($aArray)
->setIdentifier($sIdentifier)
->run();

// instantiate, 
// set different session prefix,
// set a proper array as config,
// set a certain identifier
// run
$oPhormix = new \Phormix();
$oPhormix->setSessionPrefix('myPhormixCheck')
->setConfigArray($sAbsPathToConfigFile)
->setIdentifier($sIdentifier)
->run();
~~~


___

<a id="DE"></a>[DE]

**HTML-Formulare checken mit Phormix**

## Installation

Erstelle die Datei composer.json mit folgendem Inhalt:
~~~
{
    "require": {
        "gueff/phormix":"dev-master"
    }
}
~~~
Führe Installation durch
~~~
$ composer install
~~~

## Ablauf

- Eine Konfiguration (JSON) anlegen, in der das spätere HTML-Formular beschrieben wird
- HTML-Formular bauen
- Im Backend* wird das gesendete HTML-Formular mitels Phormix nun gegen die Konfiguration gecheckt.

_\*Entgegennehmende Stelle (Crontroller, PHP-Script o.a.)_
