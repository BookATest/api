# Book A Test - API

[![CircleCI](https://circleci.com/gh/BookATest/api.svg?style=svg&circle-token=9542c8afb2b07322efafc937654de87ceaadbdc5)](https://circleci.com/gh/BookATest/api)

A system for organisations to manage the online bookings of appointments to check for HIV.

## Getting started

These instructions will get you a copy of the project up and running on your local machine 
for development and testing purposes. See [deployment](#deployment) for notes on how to 
deploy the project on a live system.

### Prerequisites

* [Docker](https://www.docker.com)
* [AWS CLI (for deploying)](https://aws.amazon.com/cli)

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

This project is intended to be hosted on AWS infrastructure and includes a configurable 
CloudFormation template to spin up an identical environment to the same one which we 
have tried, tested, and use for managed deployments.

### Prerequisites

Before spinning up the CloudFormation stack, there are a few prerequisites which are
listed below:

#### URL structure

You must first decide on a URL structure for the app. We have suggested one below, 
however you are not required to use it, just be sure to update the domains referenced 
in other steps.

* Production
  * API: `api.bookatest.yourwebsite.com`
  * Admin: `admin.bookatest.yourwebsite.com`
  * Frontend: `bookatest.yourwebsite.com`
* Staging (optional)
  * API: `api.staging.bookatest.yourwebsite.com`
  * Admin: `admin.staging.bookatest.yourwebsite.com`
  * Frontend: `staging.bookatest.yourwebsite.com`
  
#### AWS account

If you do not already have an AWS account to use for the app, then you must 
[create one](https://portal.aws.amazon.com/billing/signup). The CloudFormation
template is designed to run in the `eu-west-1` (Ireland) region, so you must
use this as your own region.

We recommend using a separate AWS account for this app, rather than an existing one
used for other purposes. This is to prevent conflicts and also for added security.

#### TLS/SSL certificates

TLS/SSL certificates must be manually created in [Certificate Manager](https://aws.amazon.com/certificate-manager)
within the specific regions listed below:

* API -  `eu-west-1` (Ireland)
* Admin - `us-east-1` (N. Virginia)
* Frontend - `us-east-1` (N. Virginia)

#### Mail service

You are free to use any mail service which works out of the box with Laravel. We use 
[Mailgun](https://www.mailgun.com) for our managed deployments, but any is fine.

#### SMS service

Only [Twilio](https://www.twilio.com) is currently supported as an SMS service, however
if you have another service you want to use, then please open a feature request issue
or a pull request.

#### Geocoding service

Only [Google](https://developers.google.com/maps/documentation/geocoding/start) is 
currently supported as a geocoding service, however if you have another service you want 
to use, then please open a feature request issue or a pull request.

#### Google analytics (optional)

This is an optional integration and only used for the frontend app. If you do plan to use
Google analytics, then be sure to create a property for the frontend domain.

### Create AWS secrets (configuration files)

Shared configuration files are centrally stored as secrets on 
[AWS Secrets Manager](https://aws.amazon.com/secrets-manager). The following secrets are
used per environment:

| Secret name | Description |
| --- | --- |
| `.env` | Variables for the API. |
| `oauth-private.key` | Private OAuth signing key for the API. |
| `oauth-public.key` | Public OAuth signing key for the API. |
| `.env.frontend` | Variables for the frontend. |
| `.env.admin` | Variables for the admin. |

Since secret names must be unique within the region, we recommend appending a suffix to each
secret indicating the environment, for example:

```
.env -> .env-production
```

#### `.env`

The values stored in this file can be found within the Wiki, however the important one that
must be set at this stage is `APP_KEY`. You can generate one by running the following command:

```bash
./develop artisan key:generate --show
```

This will generate the key and output it to the command line, whilst not modifying you local
`.env` file.

#### `oauth-private.key` and `oauth-public.key`

These files are generated by running the following command:

> If you have already generated these files locally, then move the existing ones to a
temporary location before running this command.

```bash
./develop artisan passport:keys
```

#### `.env.frontend` and `.env.admin`

The values stored in these file can be found within the Wikis for each project:
* [Frontend wiki](https://github.com/BookATest/frontend/wiki)
* [Admin wiki](https://github.com/BookATest/admin/wiki)

### Generating the CloudFormation template

The first step is to generate the latest version of the CloudFormation template. You can do
this by running the following command from the project root:

```bash
./develop cfn
```

This will generate the template and output its contents to the terminal. It will also create 
a file with the contents at `aws/cloudformation.json`.

### Launch a new CloudFormation stack

You must now launch a CloudFormation stack from the template generated earlier. **You must launch
the stack in the `eu-west-1` (Ireland) region.**

You are free to configure the parameters of the stack as pleased, although the following parameters
must be set:

* `ApiTaskCount: 0`
* `QueueWorkerTaskCount: 0`
* `SchedulerTaskCount: 0`

If you plan to deploy more than one stack to your AWS account, then the following parameter values
must have a suffix appended with the environment name, as they must be unique within the account:

* `ApiLaunchTemplateName`
* `ApiLogGroupName`
* `ApiTaskDefinitionName`
* `ApiUserName`
* `CiUserName`
* `DatabaseName`
* `DockerRepositoryName`
* `QueueWorkerLogGroupName`
* `QueueWorkerTaskDefinitionName`
* `SchedulerLogGroupName`
* `SchedulerTaskDefinitionName`

Once launched, it may take some time to complete. So please wait for the stack to fully complete
before moving on to subsequent steps.

### Setup the CI user

Now that your stack is deployed, you must configure the CI user which will be used for the deployment
process. You need to go into the AWS console and find their account in IAM. You then need to create
an access key for the user and keep it safe. You'll use these credentials when deploying.

### Push your first Docker image

With the CI user configured, we can use their credentials to build and deploy your first Docker 
image. How you go about doing this is up to you, however assuming that you are manually doing 
this locally, these are the steps to follow:

#### Build the image

To build the image you must use the helper build script and pass in the variables needed:

```bash
AWS_ACCESS_KEY_ID="xxx" \ # CI user access key ID.
    AWS_SECRET_ACCESS_KEY="xxx" \ # CI user access key secret.
    AWS_DEFAULT_REGION="eu-west-1" \ # Default AWS region.
    CIRCLE_WORKING_DIRECTORY="xxx" \ # Project root path.
    CIRCLE_SHA1="xxx" \ # Full hash of commit you want to build (most likely the latest commit on master).
    REPO_URI="xxx" \ # The ECR repo URI you are going to push the image to.
    ENV_SECRET_ID="xxx" \ # The name of the API .env secret.
    PUBLIC_KEY_SECRET_ID="xxx" \ # The name of the public key secret.
    PRIVATE_KEY_SECRET_ID="xxx" \ # The name of the private key secret.
    ./docker/build
```

#### Deploy to ECR

Once the image has built, you can now push it to the ECR repository using the helper deploy script.
This script needs to be ran for each of the services listed below:

* `api`
* `queue-worker`
* `scheduler`

```bash
AWS_ACCESS_KEY_ID="xxx" \ # CI user access key ID.
    AWS_SECRET_ACCESS_KEY="xxx" \ # CI user access key secret.
    AWS_DEFAULT_REGION="eu-west-1" \ # Default AWS region.
    REPO_URI="xxx" \ # The ECR repo URI you are going to push the image to.
    CLUSTER="xxx" \ # The name of the ECS cluster you are going to deploy to.
    SERVICE="xxx" \ # The name of the ECS service you are going to deploy to (listed above).
    ./docker/deploy
```

### Create the containers

Once the three services listed above have been updated to use the pushed image, you can
now update the services to start spinning up containers. This is done by updating the
CloudFormation stack and setting the following parameters:

* `ApiTaskCount: 2 (or more)`
* `QueueWorkerTaskCount: 1 (or more)`
* `SchedulerTaskCount: 1 (always)`

### Finished

The app should now be up and running! To deploy any further updates, simply repeat the build
and deploy steps using the new commit hash you want to build from.

### Running one-off commands

If you need to run a command on a container, such as to create a user, or create an OAuth client,
you may do so in the AWS console. Head over to the ECS dashboard and start a new task with the API
task definition. You then want to specify the command override which is a JSON array:

```json
["php", "/var/www/html/artisan", "bat:create-user", "firstname", "lastname", "email", "phone"]
```

This will run the task on a new container and once completed, the container will be destroyed.

## Built with

* [Laravel](https://laravel.com/docs/) - The Web Framework Used
* [Composer](https://getcomposer.org/doc/) - Dependency Management

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the 
process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the 
[tags on this repository](https://github.com/BookATest/api/tags). 

## Authors

* [Ayup Digital](https://ayup.agency/)

See also the list of [contributors](https://github.com/BookATest/api/contributors) who 
participated in this project.

## License

This project is licensed under the GNU License - see the [LICENSE.md](LICENSE.md) file for details.
