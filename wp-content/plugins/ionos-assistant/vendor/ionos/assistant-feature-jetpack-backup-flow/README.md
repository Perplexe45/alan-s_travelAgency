# Plugin Feature Boilerplate
This repository can be used as boilerplate for building new plugin feature e. g. in Assistant. 

## Running Behat Tests
docker pull https://gitlab.com/behat-chrome/docker-chrome-headless.git
docker run -it --add-host=host.docker.internal:host-gateway --rm -v $(pwd):/code -e DOCROOT=/code/web https://gitlab.com/behat-chrome/docker-chrome-headless.git bash