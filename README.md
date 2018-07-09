# Book A Test


A system for organisations to manage the online bookings of appointments to check for HIV.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

* PHP
* Composer
* Vagrant

### Installing

Start by cloning the example configuration files and configuring as needed. For more information about configuring the 
`Homestead.yaml` file, please consult the [Laravel Docs](https://laravel.com/docs/5.6/homestead):

```bash
cp Homestead.yaml.example Homestead.yaml
vp .env.example .env
```

Make sure you update your hosts file to include the domain specified in the Homestead configuration:

```bash
sudo echo "192.168.10.11 api.bookatest.test" >> /etc/hosts
```

Then install all of the composer dependencies:

```bash
composer install --ignore-platform-reqs
```

You should then be able to start the VM and SSH into it:

```bash
vagrant up && vagrant ssh
cd api.bookatest.test
```

If the application key (`APP_KEY`) has not already been set in the `.env` file, then generate it:

```bash
php artisan key:generate
``` 

Install the OAuth 2.0 keys:

```bash
php artisan pasport:keys
```

Ensure any API clients have been created:

```bash
php artisan passport:client --password --name="Name of Application"
```

## Running the tests

To run the PHPUnit tests:
 
```bash
php vendor/bin/phpunit
```

To run the code style tests:

```bash
php vendor/bind/phpcs
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

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/BookATest/api/tags). 

## Authors

* [Ayup Digital](https://ayup.agency/)

See also the list of [contributors](https://github.com/BookATest/api/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
