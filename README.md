# Hello. Welcome to Grow Agric's API.

Watch me build great stuff.

## API cababilities
* Sign up a farmer.
* Login a farmer.
* A whole lot more ...


## TODOs
* we should send an email for people that joined waiting list
* include a repititions in a file and include it, eg. when we set header config for api endpoints

* check that phone number or email on sign up is not from delete account
* make sure we have these checks: if ($_SERVER["REQUEST_METHOD"] == "POST") and for other http methods too ... confirm what http method calls them in the frontend
* We should have if checks for POST and GET request to make sure we have all the data we need
* Use php fun. to output name of file and fun. an error occurred in.


## Pitfalls
* Never use var_dump() ... it's like an api output
* To enable extenstion in heroku https://devcenter.heroku.com/changelog-items/514
* Maybe sth to look at https://packagist.org/packages/nicolab/php-ftp-client


## Some code snippets
```sendmail_path = /usr/sbin/sendmail -t -i```

## Rules,

Don't pick out farms that have been deleted
print_r() must always have the TRUE argument


## Fixes
* For the jobs for [sending bulk emails](https://github.com/PHPMailer/PHPMailer/blob/master/examples/mailing_list.phps)