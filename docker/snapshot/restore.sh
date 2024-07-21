#!/bin/sh

mysql -uroot -p wordpress < /tmp/snapshot/schema.sql
