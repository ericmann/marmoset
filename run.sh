#!/bin/bash

docker run --rm -t \
  -v ${PWD}:/var/www/html \
  ericmann/php:7.0ts \
  /var/www/html/bin/marmoset.sh run