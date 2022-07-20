# edtf-toolkit
A simple php app used for validating and humanizing edtf strings. It runs on the php edtf library built by ProfessionalWiki:  https://github.com/ProfessionalWiki/EDTF

Once you deploy the app, edtf dates can be humanized at the path '/humanize' using the query string 'date='

For example,

http://www6vm.village.virginia.edu/edtf/humanize?date=1498-05/1504

Will return the plaintext response 'May 1498 to 1504'.

Invalid edtf dates (for example, '05/1498-1504') will return a plaintext response "Invalid date".
