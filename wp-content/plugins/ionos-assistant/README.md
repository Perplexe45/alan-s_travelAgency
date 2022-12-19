# WordPress Assistant

## How to build/release

### Build locally: Build assets & dependencies

#### Run Grunt Builder

- Build/Update the _*.po_ languages files
- Compile the _LESS_ styling
- Include the Composer library
- Build/Update the cache

```
$ npm run grunt build
```

### Release: Tag and build package

#### Add the release tag

To prepare a version for release, please checkout the master and add the corresponding Git tag **in the following format**:

```
$ git tag -a 5.4.0 -m "Release 5.4.0"
```

Then push it to the repository:

```
$ git push origin 5.4.0
```

The package building process will then automatically update the plugin version number according to this release tag, replacing
_8.4.2_ in the PHP head comments with the value (here _5.4.0_).

#### Build ZIP package

Jenkins Job:
https://clickandbuildjenkins01.ops.server.lan/view/WordPress/job/WordPress/job/build-wordpress-plugin/

## How to run locally in Docker

### Installation

To install a WordPress containing the plugin in the "Must Use" environment, run:

```
$ docker-compose up -d
```

Login (dummy) data are:

- Login: *admin*
- Password: *admin*

### Unit tests

To run the Assistant plugin unit tests, run the test environment:

```
$ docker-compose --file=docker-compose-test.yml up
```

To run all tests, including the JSON verification of the plugin and theme list, run:

```
$ docker-compose --file=docker-compose-test-full.yml up
```

### CLI

A container is available for WP CLI; it's also the one installing our WordPress in the first place.
You can run any command in it just by using _**docker-compose run wpcli [command]**_.

For example:

```
$ docker-compose run --rm wpcli plugin list
```

Outputs:

```
+---------+----------+-----------+---------+
| name    | status   | update    | version |
+---------+----------+-----------+---------+
| akismet | inactive | available | 4.0.8   |
| hello   | inactive | none      | 1.7     |
+---------+----------+-----------+---------+
```

### Note about e-mails

For security reasons e-mails are blocked from reaching external e-mail addresses, but WordPress **still sends them**; they simply stay in the queue.
They are visible in the UI from the corresponding Docker container, at _localhost:8282_.

## Localization

The different text translations depends on the WordPress locale (default: **en_US**) and are stored using [Portable Object](https://en.wikipedia.org/wiki/Gettext) files
(_*.po_). The corresponding binary files (_*.mo_) are generated during package building; they are ignored by Git in this project.

To test the Assistant properly in your local environment / containers, you need to build them (see above in the section **"Build"**).

Because the default fallback when the translation file is not found is **en_US**, some "soft fallback" files are provided by the build. That means, only certain files are to
be taken care of manually with new texts, while some other are copies of them to avoid falling back to US english:

| **Language**     | **Comment**                        |
|------------------|------------------------------------|
| de_DE            | Original                           |
| de_DE_formal     | Original (Formal version of de_DE) |
| *de_CH*          | *Copied from de_DE_formal*         |
| *de_CH_informal* | *Copied from de_DE*                |
| en_CA            | Original                           |
| en_GB            | Original                           |
| en_US            | Original                           |
| *en_AU*          | *Copied from en_GB*                |
| *en_NZ*          | *Copied from en_GB*                |
| es_ES            | Original                           |
| es_MX            | Original                           |
| *es_GT*          | *Copied from es_MX*                |
| *es_AR*          | *Copied from es_MX*                |
| *es_VE*          | *Copied from es_MX*                |
| *es_CO*          | *Copied from es_MX*                |
| *es_CR*          | *Copied from es_MX*                |
| *es_CL*          | *Copied from es_MX*                |
| *es_PE*          | *Copied from es_MX*                |
| fr_FR            | Original                           |
| *fr_BE*          | *Copied from fr_FR*                |
| *fr_CA*          | *Copied from fr_FR*                |

## Configuration

### Assets

The configuration of assets is stored in _sitetypes.json_ and _plugins.json_. It contains `sitetypes` sections listing each Site Use Case (Gallery, Blog...) and
their basic information and theme recommendations (translatable informations are available as keys that refers to a value in the PO files). The list of themes
simply gives the names of the WordPress themes that should appear as recommended in the _Themes_ step of the Assistant.

in _plugins.json_, the list of plugin refers to all potentially installable plugins by the Assistant. Currently, only *recommended* ones are taken into account,
and automatically installed in their designated Use Case, and eventually in their designated languages. The `any` keyword is used when the plugin is available
for all languages / all Use Cases.

```json
"plugins": {
    "affilinet-performance-module": {
        "languages": [
            "de_DE",
            "de_DE_formal",
            "de_CH",
            "de_CH_informal",
            "en_GB",
            "en_US",
            "fr_FR",
            "nl_NL",
            "nl_NL_formal"
        ],
        "category": {
            "any": "recommended"
        }
    },

    "antispam-bee": {
        "languages": "any",
        "category": {
            "gallery": "more",
            "blog": "recommended",
            "personal": "recommended",
            "business": "recommended",
            "eshop": "recommended"
        }
    },

    ...
}
```

## Features

The WordPress Assistant has now a possibility to configure features enabled on switch.

### Enabling / Disabling a feature with a cookie

The _cookies.js_ file has a function creating a cookie to locally change the status of a feature. A feature by default enabled / disabled can then be
deactivated / re-activated in your browser. Just create a **browser bookmark** with the URL:

```
javascript:switchFeature('<name_of_your_feature>')
```

It will switch the wished feature on click, off or on depending on its current status.

**Ex:**

```
javascript:switchFeature('login_redesign')
```

## Xdebug
- Add server in Settings | PHP | Servers with `localhost` as Host, `8000` as port and `Xdebug` as Debugger.
- Enable path mappings and set `/var/www/html` as server path for the `wp` directory and `/var/www/html/wp-content/mu-plugins/_pre-assistant` as the `_pre-assistant` server path.
- Hit the `Start Listening for PHP Debug Connections` in the Editor toolbar (the phone-like icon on the left of the Git icons group).
- Add a breakpoint in your code and open the website.