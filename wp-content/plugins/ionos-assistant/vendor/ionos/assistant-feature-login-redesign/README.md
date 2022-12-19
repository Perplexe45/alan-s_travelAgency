# Plugin Feature Boilerplate
This repository can be used as boilerplate for building new plugin feature e. g. in Assistant. 

## Running Behat Tests
docker pull registry.gitlab.com/behat-chrome/docker-chrome-headless:7.4
docker run -it --add-host=host.docker.internal:host-gateway --rm -v $(pwd):/code -e DOCROOT=/code/web registry.gitlab.com/behat-chrome/docker-chrome-headless:7.4 bash