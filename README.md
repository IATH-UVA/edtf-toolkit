# edtf-toolkit
A simple php app used for validating and humanizing edtf strings. It can also tell you how much time has elapsed between two edtf dates. It runs on the php edtf library built by ProfessionalWiki:  https://github.com/ProfessionalWiki/EDTF

Once you deploy the app, edtf dates can be humanized at the path '/humanize' using the query string 'date='

For example,

http://www6vm.village.virginia.edu/edtf/humanize?date=1498-05/1504

Will return the plaintext response 'May 1498 to 1504'.

To get number of years elapsed, use the '/elapsed_years' path and include the queries 'start' and 'end'

For example,

http://www6vm.village.virginia.edu/edtf/elapsed_years?start=1124&end=1179-05?

Will return the plaintext response '55'.

Invalid edtf dates (for example, '05/1498-1504') will return a plaintext response "Invalid date".

To run locally:

cd into main application directory and execute:

php -S localhost:8888 -t public public/index.php

$ php -v
PHP 8.1.7 (cli) (built: Jun  7 2022 18:21:38) (NTS gcc x86_64)
Copyright (c) The PHP Group
Zend Engine v4.1.7, Copyright (c) Zend Technologies
with Zend OPcache v8.1.7, Copyright (c), by Zend Technologies

