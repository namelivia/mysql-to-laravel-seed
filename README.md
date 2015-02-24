# mysql-to-laravel-seed
This is a php script for creating a Laravel seed files from an existing MySQL database.
Helps solving the problem of having an outdated mysql database using an old schema version
that contains valuable data that needs to be ported to a newer schema.

It needs a schema file with the new database schema, and the source MySQL database.
The schema file contains a PHP associative array keys as table names and arrays of strings
for the columns, there is the special case of columns named "timestamps" that will be
converted to Laravel's format for timestamps. There is an example schema file included.

When a column from the source database is not present on the schema provided, the column and
its values are ignored. When the schema has columns that are not present in the source database
those columns will be included in the migration files, but containing empty values, you should
fill in those values if you want with your favorite text editor.

The command-line usage is:<br />
./database-to-seed -h hostname -u username -p password -d database -s schema

hostname -> Hostname of the MySQL server containg the source database.<br />
username -> Username for the MySQL source database.<br />
password -> Password for the MySQL source database.<br />
database -> Source database name.<br />
schema -> Relative path for the schema file.<br />

Eg. ./database-to-seed -h localhost -u someUser -p somePassword -d someDatabase -s ./schema.php
