# Book A Test - API

A system for organisations to manage the online bookings of appointments to check for HIV.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine 
for development and testing purposes. See deployment for notes on how to deploy the 
project on a live system.

### Prerequisites

* Docker

### Installing

Start by spinning up the docker containers using the convenience script:

```bash
# Once copied, edit this file to configure as needed.
cp .env.example .env

# Spin up the docker containers and detach so they run the background.
./develop up -d

# Install dependencies.
./develop composer install
./develop npm install

# Compile static assets.
./develop npm run dev
```

You should then be able to run the setup commands using the convenience script:

```bash
# Generate the application key.
./develop artisan key:generate

# Run the migrations and initial seeder.
./develop artisan migrate --seed

# Install the OAuth 2.0 keys.
./develop artisan pasport:keys

# Create the first Organisation Admin user (take a note of the password outputted).
./develop artisan bat:create-user <first-name> <last-name> <email> <phone-number>
```

Ensure any OAuth clients have been created to access the API (only implicit grant supported):

```bash
# Admin client must be created.
./develop artisan bat:create-oauth-client "Book A Test Admin" "https://admin.bookatest.example.com/auth/callback"

# Frontend does not need a client, as all routes used are public.

# Any other clients you may want.
./develop artisan bat:create-oauth-client <application-name> <redirect-uri>
```

## Running the tests

To run the PHPUnit tests:
 
```bash
./develop phpunit
```

To run the code style tests:

```bash
./develop phpcs
```

## Deployment

When deploying on a live environment, ensure the following `.env` variables have been set:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

## Built With

* [Laravel](https://laravel.com/docs/) - The Web Framework Used
* [Composer](https://getcomposer.org/doc/) - Dependency Management

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/BookATest/api/tags). 

## Authors

* [Ayup Digital](https://ayup.agency/)

See also the list of [contributors](https://github.com/BookATest/api/contributors) who participated in this project.

## License

This project is licensed under the GNU License - see the [LICENSE.md](LICENSE.md) file for details.
