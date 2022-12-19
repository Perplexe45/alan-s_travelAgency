Running behat tests.

docker pull https://gitlab.com/behat-chrome/docker-chrome-headless.git
docker run -it --add-host=host.docker.internal:host-gateway --rm -v $(pwd):/code -e DOCROOT=/code/web https://gitlab.com/behat-chrome/docker-chrome-headless.git bash

## Usage as standalone
- change config-URL to v8 in `vendor/ionos/ionos-library/src/data-providers/cloud.php`
- let function get_environment() in `vendor/ionos/ionos-library/src/data-providers/cloud.php` return 'test'
- to test: delete transient _ionos_assistant_config_ and delete option _ionos_tariff_
