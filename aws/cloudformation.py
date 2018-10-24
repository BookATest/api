# Converted from EC2InstanceSample.template located at:
# http://aws.amazon.com/cloudformation/aws-cloudformation-templates/

from troposphere import ec2, rds, Parameter, Ref, Template

template = Template('Create the infrastructure needed to run the Book A Test web app')
template.add_version('2010-09-09')

# Parameters.
vpc_id = template.add_parameter(Parameter(
    'VpcId',
    Type='String',
    Description='VPC ID of your existing Virtual Private Cloud (VPC)'
))

subnet = template.add_parameter(Parameter(
    'Subnets',
    Type='CommaDelimitedList',
    Description='The list of SubnetIds, for at least two Availability Zones in the region in your Virtual Private '
                'Cloud (VPC) '
))

db_database = template.add_parameter(
    Parameter(
        'DatabaseName',
        Description='The database name',
        Default='book_a_test',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9_]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'underscores). '
    )
)

db_username = template.add_parameter(
    Parameter(
        'DatabaseUser',
        Description='The database admin username',
        NoEcho=True,
        Type='String',
        MinLength='1',
        MaxLength='16',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters.'
    )
)

db_password = template.add_parameter(
    Parameter(
        'DatabasePassword',
        Description='The database admin password',
        NoEcho=True,
        Type='String',
        MinLength='1',
        MaxLength='41',
        AllowedPattern='[a-zA-Z0-9]*',
        ConstraintDescription='Must only contain alphanumeric characters.'
    )
)

db_class = template.add_parameter(
    Parameter(
        'DatabaseClass',
        Description='The database instance class',
        Default='db.t2.micro',
        Type='String',
        AllowedValues=[
            "db.t2.micro",
            "db.m1.small",
            "db.m1.large",
            "db.m1.xlarge",
            "db.m2.xlarge",
            "db.m2.2xlarge",
            "db.m2.4xlarge"
        ],
        ConstraintDescription='Must select a valid database instance type.'
    )
)

db_allocated_storage = template.add_parameter(
    Parameter(
        'DatabaseAllocatedStorage',
        Description='The size of the database (GiB)',
        Default='10',
        Type='Number',
        MinValue='5',
        MaxValue='1024',
        ConstraintDescription='Must be between 5 and 1024 GiB.'
    )
)

# Create the security groups.
load_balancer_security_group = template.add_resource(
    ec2.SecurityGroup(
        'LoadBalancerSecurityGroup',
        GroupDescription='For connecting to the API load balancer',
        SecurityGroupIngress=[
            ec2.SecurityGroupRule(
                Description='HTTP access from the public',
                IpProtocol='tcp',
                FromPort='80',
                ToPort='80',
                CidrIp='0.0.0.0/0'
            ),
            ec2.SecurityGroupRule(
                Description='HTTPS access from the public',
                IpProtocol='tcp',
                FromPort='443',
                ToPort='443',
                CidrIp='0.0.0.0/0'
            )
        ]
    )
)

api_security_group = template.add_resource(
    ec2.SecurityGroup(
        'ApiSecurityGroup',
        GroupDescription='For connecting to the API containers',
        SecurityGroupIngress=[
            ec2.SecurityGroupRule(
                Description='Full access from the load balancer',
                IpProtocol='tcp',
                FromPort='0',
                ToPort='65535',
                SourceSecurityGroupName=Ref(load_balancer_security_group)
            )
        ]
    )
)

database_security_group = template.add_resource(
    ec2.SecurityGroup(
        'DatabaseSecurityGroup',
        GroupDescription='For connecting to the MySQL instance',
        SecurityGroupIngress=[
            ec2.SecurityGroupRule(
                Description='MySQL access from the API containers',
                IpProtocol='tcp',
                FromPort='3306',
                ToPort='3306',
                SourceSecurityGroupName=Ref(api_security_group)
            )
        ]
    )
)

redis_security_group = template.add_resource(
    ec2.SecurityGroup(
        'RedisSecurityGroup',
        GroupDescription='For connecting to the Redis cluster',
        SecurityGroupIngress=[
            ec2.SecurityGroupRule(
                Description='Redis access from the API containers',
                IpProtocol='tcp',
                FromPort='6379',
                ToPort='6379',
                SourceSecurityGroupName=Ref(api_security_group)
            )
        ]
    )
)

# Create the RDS instance.
database_subnet_group = template.add_resource(
    rds.DBSubnetGroup(
        'DatabaseSubnetGroup',
        DBSubnetGroupDescription='Subnets available for the RDS Instance',
        SubnetIds=Ref(subnet)
    )
)


print(template.to_json())
