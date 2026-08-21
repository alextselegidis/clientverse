{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */
--}}

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<base href="{{url('')}}/">

<title>@yield('pageTitle') | Clientverse</title>
<meta name="description" content="Clientverse is a self-hosted CRM for managing customers, contacts, projects, sales and contracts in one place.">
<meta name="theme-color" content="#33507a">

<link rel="icon" href="favicon.ico" type="image/x-icon"/>
<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
<link rel="manifest" href="manifest.json">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Clientverse">

<link rel="stylesheet" href="vendor/bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="vendor/bootstrap-icons/bootstrap-icons.min.css">
<link rel="stylesheet" href="vendor/pace-js/pace-theme-flat-top.tmpl.css">
<link rel="stylesheet" href="styles/clientverse.css?{{config('app.version')}}">
