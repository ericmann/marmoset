#!/bin/bash

docker run --rm -t \
  -v ${PWD}:/var/www/html \
  -c 2 \
  --cpuset-cpus="0-1" \
  -m 2g \
  -e ASYNC=1 \
  ericmann/php:7.0ts \
  /var/www/html/bin/marmoset.sh run