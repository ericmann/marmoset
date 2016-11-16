docker run --rm -t^
  -v %cd%:/var/www/html"^
  -c 2^
  --cpuset-cpus="0-1"^
  -m 2g^
  ericmann/php:7.0ts^
  /var/www/html/bin/marmoset.sh run
