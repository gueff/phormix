# Phormix

## Overview
- [Deutsch, DE](#DE)
- [English, EN](#EN)

<a id="DE"></a>[DE]

**HTML-Formulare checken mit Phormix**

## Installation

Erstelle die Datei comnposer.json mit folgendem Inhalt:
~~~
{
    "require": {
        "gueff/mymvc":"dev-master"
    }
}
~~~
Führe Installation durch
~~~
$ composer install
~~~

## Ablauf

- Man legt eine Konfiguration (JSON) an, in der das spätere HTML-Formular beschrieben wird. 
- HTML-Formular bauen
- Im Backend* wird das gesendete HTML-Formular mitels Phormix nun gegen die Konfiguration gecheckt.

_\*Entgegennehmende Stelle (Crontroller, PHP-Script o.a.)_


<a id="EN"></a>[EN]

**Check HTML-Forms with Phormix**

## Installation
create the comnposer.json file with following content:
~~~
{
    "require": {
        "gueff/mymvc":"dev-master"
    }
}
~~~
run installation
~~~
$ composer install
~~~

## Process
- you create a configuration file (JSON), which describes the later HTML form
- Build the HTML Form
- At backend Phormix will check the transmitted data from the HTML form against the configuration

_\*Receiving Unit (Crontroller, PHP-Script o.a.)_
