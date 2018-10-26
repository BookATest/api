# This stack creates the IAM users and the ECR repository.

from troposphere import Parameter, Ref, Template, GetAtt, Base64, Join
import troposphere.ecr as ecr

template = Template('Create the users and Docker repository needed to run the Book A Test web app')
template.add_version('2010-09-09')

# ==================================================
# Parameters.
# ==================================================

repository_name = template.add_parameter(Parameter(
    'RepositoryName',
    Type='String',
    Description='The name of the Docker repository.',
    Default='api',
    MinLength='1',
    MaxLength='64',
    AllowedPattern='(?:[a-z0-9]+(?:[._-][a-z0-9]+)*/)*[a-z0-9]+(?:[._-][a-z0-9]+)*',
    ConstraintDescription='Must be a valid repository name.'
))

# ==================================================
# Resources.
# ==================================================

# Create the Docker repository.
docker_repository = template.add_resource(
    ecr.Repository(
        'DockerRepository',
        RepositoryName=Ref(repository_name)
    )
)

print(template.to_json())
