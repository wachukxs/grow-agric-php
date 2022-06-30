Hello. Welcome to Grow Agric's API.

Watch me build great stuff.

### API cababilities
* Sign up a farmer.
* Login a farmer.
* ...


### TODOs
0. we should send an email for people that joined waiting list
1. include a repititions in a file and include it, eg. when we set header config for api endpoints

2. check that phone number or email on sign up is not from delete account
3. make sure we have these checks: if ($_SERVER["REQUEST_METHOD"] == "POST") and for other http methods too ... confirm what http method calls them in the frontend
### Pitfalls
1. Never use var_dump() ... it's like an api output

To enable extenstion in heroku https://devcenter.heroku.com/changelog-items/514

Maybe sth to look at https://packagist.org/packages/nicolab/php-ftp-client


// 
sendmail_path = /usr/sbin/sendmail -t -i

## Rules,

Don't pick out farms that have been deleted
print_r() must always have the TRUE argument