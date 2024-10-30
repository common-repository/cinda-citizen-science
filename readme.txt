=== Plugin Name ===
Contributors: iecolab,si2soluciones,arkangel,guillegarcia
Donate link: http://www.cinda.science/
Tags: citizen science, open science, volunteer network, API, Android Client, campaigns, contributions
Requires at least: 3.0.1
Tested up to: 4.5
Stable tag: 4.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Client-server platform for managing contributions of volunteers in campaigns of citizen science platform.

== Description ==

This plugin manages volunteer networks. More specifically, the contributions of these volunteers have configured your campaigns here.

It lets you create "campaigns" in which you need to collect data from different people who want to contribute to your cause. For each campaign you can define a specific data model (using data types that we have implemented, such as geo, images, dictionaries, text, numbers, dates, ...) and can receive contributions of structured data.

The plugin exposes a RESTful API in yourdomain.tld/cindaAPI/ that can be consumed by any client. We have built one for Android (especially nice;-)), but if you want you can program another.

API Methods:

- GET /cindaAPI/server/info/
Returns general server data

- GET /cindaAPI/campaigns/list/
Campaigns list

- GET /cindaAPI/campaign/([0-9]+)/
Details of a campaign.

- GET /cindaAPI/campaign/([0-9]+)/model/
Data model of contributions to a campaign

- GET /cindaAPI/campaign/([0-9]+)/listData/
List of contributions to a campaign

- POST /cindaAPI/campaign/([0-9]+)/sendData/
Send/Save a contribution

- GET /cindaAPI/campaign/([0-9]+)/listVolunteers/
List Volunteers registered on the server

- GET /cindaAPI/topVolunteers/
Get the top contributors

- POST /cindaAPI/campaign/([0-9]+)/suscribe/
Subscription to a campaign

- POST /cindaAPI/campaign/([0-9]+)/unsuscribe/
Stop following a campaign

- POST /cindaAPI/volunteer/register/
Register/login of a user on the server. Returns a token required to call protected operations.

- POST /cindaAPI/volunteer/update-endpoint/
Updates the user device endpoint for push notifications

- GET /cindaAPI/volunteer/([0-9]+)/
Data of a volunteer

- GET /cindaAPI/volunteer/([0-9]+)/listData/
List of contributions for submitted by a volunteer

- GET /cindaAPI/volunteer/activate-login/
Activate account to operate in WebAPP, it send a mail with data instructions

- GET /cindaAPI/contribution/([0-9]+)/
Details of a contribution

- GET /cindaAPI/realtime/contributions/
A special operation, designed to be called from the companion App to send data to a wearable.

- GET /cindaAPI/realtime/nearby-activity/
Wearable related stuff, in progress...

- GET /cindaAPI/realtime/watchface/
Data to paint on the watch face of and Android Wear smartwatch

- GET /cindaAPI/dictionary/([0-9]+)/
Returns values for a special type of field available on the campaigns

- GET /cindaAPI/trackings/
Returns tracks of routes recorded for a user.

- GET /cindaAPI/tracking/([0-9]+)/
Details of a track

- POST /cindaAPI/tracking/send/
Send a track

- GET /cindaAPI/opendata/campaigns/
One way of show all the info about Campaigns to expose an Open Data platform

- GET /cindaAPI/opendata/contributions/
One way of show all the info about Contributions to expose an Open Data Platform



--------

- before any POST you need to get a temporary token (nonce), here is the way you can get it:
    - Register:        /cindaAPI/nonce/volunteer_register/?token=[token]
    - update endpoint:    /cindaAPI/nonce/volunteer_update_endpoint/?token=[token]
    - Subscribe:         /cindaAPI/nonce/campaign_suscribe/?token=[token]
    - Unsubscribe:        /cindaAPI/nonce/campaign_unsuscribe/?token=[token]
    - Send contribution:     /cindaAPI/nonce/campaign_sendData/?token=[token]
    - Send tracking:      /cindaAPI/nonce/tracking_send/?token=[token]


The token especified is obtained on the (POST /cindaAPI/volunteer/register/) and before you must call /cindaAPI/nonce/volunteer_register, so you have to generate your own unique "first token" for this call (ex. = device UUID)

Almost the GET operations have a (optional) token.  It is not compulsory, but necessary to consult own data.

_Client_

You can download and compile your own version of Android client https://github.com/si2info/cinda-android

We are in process of writing some documentation, please be patient :)

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to the menu "CINDA: Volunteers Network" > Configuration, and enter some data about your new server (Name, description,...)
4. Go to "CINDA: Volunteers Network" > Campaigns to create your first campaign to collect data, defining general data about the campaign, and the data model

== Frequently Asked Questions ==
= For what purpose can I use this? =

You can retrieve data from a network of collaborators in many forms (including geopositioned data, user comments, photos,...). The purposse is very wide, you can use CINDA to track bike rides, retrieve complaints of citizens... And then, you can process the data to generate reports, by example.

= Where to know more? =

Please, browse to http://cinda.science :)

== Upgrade Notice ==

= 1.3.6 =
Bug fixed: Contribution List on API response was incorrect and was causing and FC on Android App

= 1.3.5 =
Important bug fixed (some files was on an incorrect path)

= 1.3.4 =
This is the first /true/ stable version, with a brand new web admin panel.

== Screenshots ==

1. General and usage info
2. Initial configuration (CINDA Server name, Google Maps API Key, and so...)
3. Sample list of campaigns on a demo server
4. Sample campaign, showing its data model.

== Changelog ==

= 1.4.3 =
* Bug solved on send Contribution

= 1.4.2 =
* Now, on login in wordpress, login in CindaAPP if volunteer exists

= 1.4.1 =
* Set the author id for new contributions

= 1.4.0 =
* Some bugs fixed
* Add link into Volunteers and wp_users
* Add new role "Volunteer" for wp_users
* Changes in login/logout action in CindaApp
* Now you can edit Volunteer info directly from wp-admin

= 1.3.9 =
* Check required option for file inputs in WebAPP

= 1.3.8 =
* Add term description in Dictionary selection (WebApp)

= 1.3.7 =
* Bugs fixed in WebApp: Fields required, show form errors...
* Add email field of campaign administrator for receive notifications when volunter sent new contribution

= 1.3.6 =
* Bug fixed: Contribution List on API response was incorrect and was causing and FC on Android App

= 1.3.5 =
* Bug fixed: some files was incorrect path

= 1.3.4 =
* Some methods of API upgraded in ContributionList and Contribution classes
* Bug fixed on save Contribution (API)
* APP Web core upgraded
* Add 'New contribution' send action to APP Web
* Change sessions var to cookie for APP Web.
* All '_wpnonce' fields changed by 'nonce'.

= 1.3.3 =
* Bugs fixeds on campaign suscriptions/unsuscriptions
* Bug fixed in google maps API on server-info endpoint
* '_wpnonce' field deprecated, use instead 'nonce'
* Add functionality to APP Web
* Add 'About CINDA' page

= 1.3.2 =
* Bugs on install/activate fixeds

= 1.3.1 =
* Add custom CindaAPP Header and Footer (Without theme dependence)

= 1.3.0 =
* Add Web APP (List and edit own contributions in web)

= 1.2.1 =
* Solved language support
* Add template translation
* Add Spanish translation

= 1.2.0 =
* Add support to update Contribution

= 1.1.3 =
* Warning Bugs fixed
* Folders and files restructuration

= 1.1.2 =
* Some bugs has gone!

= 1.1.1 =
* Removed some unused jQuery files on /assets/js/
* Use of wp_nonces on POST calls to prevent unauthorized access.
* Disallow Direct File Access to plugin files

= 1.1.0 =
* No including own copy of a core jQuery file
* Sanitized, escaped, and validated POST calls

= 1.0 =
* First (estable) version

== More info ==

You can visit http://www.cinda.science/ for more information about this free citizen science project designed and promoted by http://iecolab.es and developed by http://si2.info
