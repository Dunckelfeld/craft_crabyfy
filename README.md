# CraByFy plugin for Craft CMS 3.x

Deploys craft fed gatsby frontend to netlify

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require Dunckelfeld/cra-by-fy

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for CraByFy.

## CraByFy Overview

Use craft as a headless CMS with deployment (via gatsby) to Netlify

## Configuring CraByFy

goto /admin/settings/plugins/cra-by-fy and set up urls and hooks for communication with netlify (triggers and status)

## Using CraByFy

Find a control panel page with buttons to deploy to preview and live netlify /admin/actions/cra-by-fy/deploy
Find a button on entry page to go to netlify preview

## CraByFy Roadmap

Some things to do, and ideas for potential features:

* conditional when settings are set: satus leds and index route buttons
* subnav item with deploy live button
* subnav item with preview site link
* clean up (Service, Controller, Main Controller)

Brought to you by [Dunckelfeld](dunckelfeld.de)
