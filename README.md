# Phormix

## Overview
- [Deutsch, DE](#DE)
- [English, EN](#EN)

<a id="DE"></a>[DE]

**Formulare checken mit Phormix**

## Installation

~~~
{
    "require": {
        "gueff/mymvc":"dev-master"
    }
}
~~~

~~~
$ composer install
~~~

## Ablauf

- Man legt eine Konfiguration (JSON) an, in der das spätere HTML-Formular beschrieben wird. 
- HTML-Formular bauen
- Im Backend* wird das gesendete HTML-Formular mitels Phormix nun gegen die Konfiguration gecheckt.

_\*Entgegennehmende Stelle (Crontroller, PHP-Script o.a.)_


<a id="EN"></a>[EN]

**Check Formulars with Phormix**

## Installation

~~~
{
    "require": {
        "gueff/mymvc":"dev-master"
    }
}
~~~

~~~
$ composer install
~~~

## Process
- you create a configuration file (JSON), which describes the later HTML form
- Build the HTML Form
- At backend Phormix will check the transmitted data from the HTML form against the configuration

_\*Receiving Unit (Crontroller, PHP-Script o.a.)_
