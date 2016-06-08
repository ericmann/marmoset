#!/bin/bash

docker build -t marmoset .

docker run -it --rm \
  -v ${PWD}:/usr/src/marmoset \
  --name marmoset \
  marmoset