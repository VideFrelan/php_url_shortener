# URL Shortener

URL Shortener is a simple application that allows users to convert long URLs into shorter and shareable URLs. This app is useful in situations where a user wants to share a shorter and memorable link with others.

## Demo

You can see a demo of my URL shortener website that I have provided for you here: [https://frelan.tenazpedia.com][def]

## Feature

- Admin Dashboard: An dashboard that enables administrators to delete URLs and users.
- Registration with OTP Verification: Users are required to verify their email address during the registration process by entering a One-Time Password (OTP) sent to their registered email.
- Reset Password: Password recovery feature that allows users to reset their password if it is forgotten.
- QR Code: Users can generate a QR Code from the URL they have shortened.
- URL Shortener: Converts long URLs to shorter URLs.
- URL Customization: Users can customize the short URL they have created.
- URL Statistics: Provides usage statistics for each shortened URL, such as such as number of clicks and creation date.
- User Authentication: An authentication system that allows users to login and access available features.

## Configuration

Before using the URL Shortener application, make sure you have made the necessary configurations:

1. Create your MySQL database by importing the database.sql file provided in this repository.
2. Open the `configuration/config.php` file and adjust the following configurations:

```php
define('DB_HOST', 'localhost'); // Database hosts
define('DB_USERNAME', 'your_database_username'); // Database usernames
define('DB_PASSWORD', 'your_database_password'); // Database passwords
define('DB_NAME', 'your_database_name'); // Database name

define('BASE_URL', 'https://YOUR-DOMAIN.COM/'); // Your website domain/subdomain
```

3. Open the `configuration/mail.php` file and adjust the following configurations:
```php
define('SMTP_HOST', 'YOUR-SMTP-HOST'); // Email hosts
define('SMTP_PORT', 'ENTER_PORT_HERE'); // Email port
define('SMTP_USERNAME', 'YOUR-SMTP-USERNAME'); // Email username
define('SMTP_PASSWORD', 'YOUR-SMTP-PASSWORD'); // Email password
define('EMAIL_FROM', 'YOUR-EMAIL'); // Your website email
```

4. Make sure your web-hosting settings have ```'mod_rewrite'```, ```'allow_url_fopen'``` and ```'allow_url_include'``` enabled.

## How to use

1. Open the URL Shortener application in a browser.
2. If you don't have an account yet, register by clicking the "Register" button and filling out the registration form.
3. After registering or if you already have an account, enter using the email and password that you registered.
4. After successful login, you will be directed to the application dashboard.
5. To shorten a URL, enter the URL you want to shorten into the input box provided on the dashboard page.
6. Click the "Shorten" button to start URL shortening.
7. Copy the short URL and use it to share it with others.
8. You can also view usage statistics of the URLs you have shortened on the dashboard page.

## Contribution

If you want to contribute to the development of URL Shortener, you can fork this repository, make the desired changes, and submit a pull request. I will be happy to accept contributions that improve or extend the features of this app.

## Licence

URL Shortener is licensed under [MIT License](LICENSE).

[def]: https://frelan.tenazpedia.com