docker run --rm -t^
  -v %cd%:/var/www/html^
  -c 3^
  --cpuset-cpus="0-2"^
  -m 2g^
  -e ASYNC=1^
  ericmann/php:7.0ts^
  /var/www/html/bin/marmoset.sh run
