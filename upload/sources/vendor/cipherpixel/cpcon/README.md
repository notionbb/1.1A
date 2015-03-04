# CP-Core and Modules #

## Description ##

*Currently in development!*

A really, really basic "library" to make connecting with Google Plus, Facebook's (and more soon), OAuth logins.

## Installation ##

[Composer](https://getcomposer.org/):
```json
"require": {
	"cipherpixel/cpcon": "dev-master"
}
```

## Usage ##

To use, simply load the class as aan object. The constructor usually requries four parameters.

**type** - type of connection to create. For example, 'google' or 'facebook'

**key** - key API App key or name

**pass** - API secret password

**uri** - redirect URI

### Example ###

To echo the url to begin the OAuth flow:
```php
$google = new CipherPixel\cpcon\obj( 'google', 'GOOGLE_APP_ID', 'GOOGLE_APP_PASS', 'GOOGLE_REDIRECT_URI' );
echo $google->get_url();
```

To print the data received from the OAuth flow:
```php
if ( $google->process() )
{
	print_r( $google->data() );
}
```
