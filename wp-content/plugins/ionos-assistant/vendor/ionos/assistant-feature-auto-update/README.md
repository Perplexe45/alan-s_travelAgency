# Feature: Force Auto Updates
This feature enables the updates for plugins and themes and modifies the update hints. 

## Running Behat Tests
docker pull https://gitlab.com/behat-chrome/docker-chrome-headless.git
docker run -it --add-host=host.docker.internal:host-gateway --rm -v $(pwd):/code -e DOCROOT=/code/web https://gitlab.com/behat-chrome/docker-chrome-headless.git bash