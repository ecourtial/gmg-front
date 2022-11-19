# GMG-FRONT (Give Me a Game, Front App)
[![CircleCI](https://circleci.com/gh/ecourtial/gmg-front/tree/master.svg?style=svg)](https://circleci.com/gh/ecourtial/gmg-front/tree/master) [![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/ecourtial/gmg-front/graphs/commit-activity) [![Ask Me Anything !](https://img.shields.io/badge/Ask%20me-anything-1abc9c.svg)](https://GitHub.com/ecourtial/gmg-front) [![GitHub license](https://img.shields.io/github/license/Naereen/StrapDown.js.svg)](https://github.com/ecourtial/gmg/blob/master/LICENSE)

A front-end implementation for the project [GMG](https://github.com/ecourtial/gmg). It covers most of the features.

## Installation
* Upload the code on your server.
* Copy the _.env_ file to a _.env.local_ one and fill it with your values.
* Run the _composer install_ command.

## Adding extra features
* If you want to add extra features, just open an issue here in this repo.

## Stack
* PHP 8.1
* Symfony 6.0

## Licence
Provided under the MIT licence.

## Screenshots

![Platform list](docs/platforms.jpg "Platform list")

![Version list](docs/versions.jpg "Version list")

## Changelog

### v1.3.0
* Updated installation doc.
* We display on the homepage the count of versions for which we have at least one original copy.

### v1.2.1
* Fixed a bug when counting the versions in the todo list.

### v1.2.0
* Added a separation for ranking sequences (1-10, 11-20...) in the todo list.
* The API error message shows the URL that was requested.

### v1.1.0 : 
* Follow the 4.1 version of the back (support of ROM copy type and Plastic tube casing type).
* In the stories screen, instead of showing the story id, we show the story position.
