# Converted from EC2InstanceSample.template located at:
# http://aws.amazon.com/cloudformation/aws-cloudformation-templates/

from troposphere import Parameter, Ref, Template, GetAtt, Base64, Join
import troposphere.ec2 as ec2
import troposphere.elasticache as elasticache
import troposphere.rds as rds
import troposphere.sqs as sqs
import troposphere.s3 as s3
import troposphere.elasticloadbalancingv2 as elb
import troposphere.ecs as ecs
import troposphere.ecr as ecr
import troposphere.autoscaling as autoscaling
import troposphere.iam as iam

template = Template('Create the infrastructure needed to run the Book A Test web app')
template.add_version('2010-09-09')

# ==================================================
# Parameters.
# ==================================================

subnets = template.add_parameter(Parameter(
    'Subnets',
    Type='CommaDelimitedList',
    Description='The list of subnet IDs, for at least two Availability Zones in the region in your Virtual Private '
                'Cloud (VPC) '
))

database_name = template.add_parameter(
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

database_username = template.add_parameter(
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

database_password = template.add_parameter(
    Parameter(
        'DatabasePassword',
        Description='The database admin password',
        NoEcho=True,
        Type='String',
        MinLength='8',
        MaxLength='41',
        AllowedPattern='[a-zA-Z0-9]*',
        ConstraintDescription='Must only contain alphanumeric characters.'
    )
)

database_class = template.add_parameter(
    Parameter(
        'DatabaseClass',
        Description='The database instance class',
        Type='String',
        Default='db.t2.micro',
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

database_allocated_storage = template.add_parameter(
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

redis_node_class = template.add_parameter(
    Parameter(
        'RedisNodeClass',
        Description='The Redis node class',
        Type='String',
        Default='cache.t2.micro',
        AllowedValues=[
            'cache.t2.micro',
            'cache.m1.small',
            'cache.m1.large',
            'cache.m1.xlarge',
            'cache.m2.xlarge',
            'cache.m2.2xlarge',
            'cache.m2.4xlarge',
            'cache.c1.xlarge'
        ],
        ConstraintDescription='Must select a valid Redis node type.'
    )
)

redis_nodes_count = template.add_parameter(
    Parameter(
        'RedisNodesCount',
        Description='The number of Redis nodes to have in the cluster',
        Default='1',
        Type='Number',
        MinValue='1',
        ConstraintDescription='Must be 1 or more.'
    )
)

sqs_default_queue_name = template.add_parameter(
    Parameter(
        'SqsDefaultQueueName',
        Description='The default queue name',
        Default='default',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens). '
    )
)

sqs_notifications_queue_name = template.add_parameter(
    Parameter(
        'SqsNotificationsQueueName',
        Description='The notifications queue name',
        Default='notifications',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens). '
    )
)

s3_uploads_bucket_name = template.add_parameter(
    Parameter(
        'S3UploadsS3BucketName',
        Description='The uploads bucket name',
        Default='uploads',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens). '
    )
)

s3_frontend_bucket_name = template.add_parameter(
    Parameter(
        'S3FrontendS3BucketName',
        Description='The frontend bucket name',
        Default='frontend',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens). '
    )
)

s3_backend_bucket_name = template.add_parameter(
    Parameter(
        'S3BackendS3BucketName',
        Description='The backend bucket name',
        Default='backend',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens). '
    )
)

api_instance_class = template.add_parameter(
    Parameter(
        'ApiInstanceClass',
        Description='The API EC2 instance class',
        Type='String',
        Default='t2.micro',
        AllowedValues=[
            't2.micro',
            'm1.small',
            'm1.large',
            'm1.xlarge',
            'm2.xlarge',
            'm2.2xlarge',
            'm2.4xlarge',
            'c1.xlarge'
        ],
        ConstraintDescription='Must select a valid API instance type.'
    )
)

api_instance_count = template.add_parameter(
    Parameter(
        'ApiInstanceCount',
        Description='The number of API EC2 instances to load balance between',
        Type='Number',
        Default='2',
        MinValue='1',
        ConstraintDescription='Must be 1 or more.'
    )
)

# ==================================================
# Resources.
# ==================================================

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
        DBSubnetGroupDescription='Subnets available for the RDS instance',
        SubnetIds=Ref(subnets)
    )
)

database = template.add_resource(
    rds.DBInstance(
        'Database',
        DBName=Ref(database_name),
        AllocatedStorage=Ref(database_allocated_storage),
        DBInstanceClass=Ref(database_class),
        Engine='MySQL',
        EngineVersion='5.7',
        MasterUsername=Ref(database_username),
        MasterUserPassword=Ref(database_password),
        VPCSecurityGroups=[GetAtt(database_security_group, 'GroupId')],
        DBSubnetGroupName=Ref(database_subnet_group),
        PubliclyAccessible=False
    )
)

# Create the Redis cluster.
redis_subnet_group = template.add_resource(
    elasticache.SubnetGroup(
        'RedisSubnetGroup',
        Description='Subnets available for the Redis cluster',
        SubnetIds=Ref(subnets)
    )
)

redis = template.add_resource(
    elasticache.CacheCluster(
        'Redis',
        Engine='redis',
        EngineVersion='4.0',
        CacheNodeType=Ref(redis_node_class),
        NumCacheNodes=Ref(redis_nodes_count),
        VpcSecurityGroupIds=[GetAtt(redis_security_group, 'GroupId')],
        CacheSubnetGroupName=Ref(redis_subnet_group)
    )
)

# Create the SQS queues.
default_queue = template.add_resource(
    sqs.Queue(
        'DefaultQueue',
        QueueName=Ref(sqs_default_queue_name)
    )
)

notifications_queue = template.add_resource(
    sqs.Queue(
        'NotificationsQueue',
        QueueName=Ref(sqs_notifications_queue_name)
    )
)

# Create the S3 buckets.
uploads_bucket = template.add_resource(
    s3.Bucket(
        'UploadsBucket',
        BucketName=Ref(s3_uploads_bucket_name),
        AccessControl='Private'
    )
)

frontend_bucket = template.add_resource(
    s3.Bucket(
        'FrontendBucket',
        BucketName=Ref(s3_frontend_bucket_name),
        AccessControl='PublicRead'
    )
)

backend_bucket = template.add_resource(
    s3.Bucket(
        'BackendBucket',
        BucketName=Ref(s3_backend_bucket_name),
        AccessControl='PublicRead'
    )
)

# Create the load balancer.
load_balancer = template.add_resource(
    elb.LoadBalancer(
        'LoadBalancer',
        Scheme='internet-facing',
        SecurityGroups=[GetAtt(load_balancer_security_group, 'GroupId')],
        Subnets=Ref(subnets)
    )
)

# Create ECS cluster.
ecs_cluster_role = template.add_resource(
    iam.Role(
        'EcsClusterRole',
        ManagedPolicyArns=['arn:aws:iam::aws:policy/service-role/AmazonEC2ContainerServiceforEC2Role'],
        AssumeRolePolicyDocument={
            'Version': '2012-10-17',
            'Statement': [
                {
                    'Action': 'sts:AssumeRole',
                    'Principal': {
                        'Service': 'ec2.amazonaws.com'
                    },
                    'Effect': 'Allow'
                }
            ]
        }
    )
)

ec2_instance_profile = template.add_resource(
    iam.InstanceProfile(
        'EC2InstanceProfile',
        Roles=[Ref(ecs_cluster_role)]
    )
)

docker_repository = template.add_resource(
    ecr.Repository(
        'DockerRepository',
        RepositoryName='api'
    )
)

ecs_cluster = template.add_resource(
    ecs.Cluster(
        'ApiCluster'
    )
)

launch_template = template.add_resource(
    ec2.LaunchTemplate(
        'LaunchTemplate',
        LaunchTemplateName='ApiLaunchTemplate',
        LaunchTemplateData=ec2.LaunchTemplateData(
            ImageId='ami-066826c6a40879d75',
            InstanceType=Ref(api_instance_class),
            IamInstanceProfile=ec2.IamInstanceProfile(
                Arn=GetAtt(ec2_instance_profile, 'Arn')
            ),
            InstanceInitiatedShutdownBehavior='terminate',
            Monitoring=ec2.Monitoring(Enabled=True),
            SecurityGroups=[Ref(api_security_group)],
            BlockDeviceMappings=[
                ec2.BlockDeviceMapping(
                    DeviceName='/dev/xvdcz',
                    Ebs=ec2.EBSBlockDevice(
                        DeleteOnTermination=True,
                        VolumeSize=22,
                        VolumeType='gp2'
                    )
                )
            ],
            UserData=Base64(
                Join('', [
                    '#!/bin/bash\n',
                    'echo ECS_CLUSTER=',
                    Ref(ecs_cluster),
                    ' >> /etc/ecs/ecs.config;echo ECS_BACKEND_HOST= >> /etc/ecs/ecs.config;'
                ])
            )
        )
    )
)

autoscaling_group = template.add_resource(
    autoscaling.AutoScalingGroup(
        'AutoScalingGroup',
        DesiredCapacity=Ref(api_instance_count),
        MinSize=Ref(api_instance_count),
        MaxSize=Ref(api_instance_count),
        LaunchTemplate=autoscaling.LaunchTemplateSpecification(
            LaunchTemplateId=Ref(launch_template),
            Version=GetAtt(launch_template, 'LatestVersionNumber')
        ),
        AvailabilityZones=['eu-west-1a', 'eu-west-1b', 'eu-west-1c']
    )
)

# Create the ECS task definitions.
api_task_definition = template.add_resource(
    ecs.TaskDefinition(
        'ApiTaskDefinition',
        Family='api',
        NetworkMode='bridge',
        RequiresCompatibilities=['EC2'],
        Cpu='256',
        Memory='512',
        ContainerDefinitions=[ecs.ContainerDefinition(
            Name='api',
            Image=Join('.', [
                Ref('AWS::AccountId'),
                'dkr.ecr',
                Ref('AWS::Region'),
                Join('/', [
                    'amazonaws.com',
                    Ref(docker_repository)
                ])
            ]),
            MemoryReservation='256',
            PortMappings=[ecs.PortMapping(
                HostPort='0',
                ContainerPort='80',
                Protocol='tcp'
            )],
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': '/ecs/api',
                    'awslogs-region': Ref('AWS::Region'),
                    'awslogs-stream-prefix': 'ecs'
                }
            )
        )]
    )
)

queue_worker_task_definition = template.add_resource(
    ecs.TaskDefinition(
        'QueueWorkerTaskDefinition',
        Family='queue-worker',
        NetworkMode='bridge',
        RequiresCompatibilities=['EC2'],
        Cpu='256',
        Memory='512',
        ContainerDefinitions=[ecs.ContainerDefinition(
            Name='api',
            Image=Join('.', [
                Ref('AWS::AccountId'),
                'dkr.ecr',
                Ref('AWS::Region'),
                Join('/', [
                    'amazonaws.com',
                    Ref(docker_repository)
                ])
            ]),
            MemoryReservation='256',
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': '/ecs/queue-worker',
                    'awslogs-region': Ref('AWS::Region'),
                    'awslogs-stream-prefix': 'ecs'
                }
            ),
            Command=[
                'php',
                'artisan',
                'queue:work',
                '--tries=1'
            ],
            WorkingDirectory='/var/www/html',
            HealthCheck=ecs.HealthCheck(
                Command=[
                    'CMD-SHELL',
                    'php -v || exit 1'
                ],
                Interval=30,
                Retries=3,
                Timeout=5
            )
        )]
    )
)

scheduler_task_definition = template.add_resource(
    ecs.TaskDefinition(
        'SchedulerTaskDefinition',
        Family='scheduler',
        NetworkMode='bridge',
        RequiresCompatibilities=['EC2'],
        Cpu='256',
        Memory='512',
        ContainerDefinitions=[ecs.ContainerDefinition(
            Name='api',
            Image=Join('.', [
                Ref('AWS::AccountId'),
                'dkr.ecr',
                Ref('AWS::Region'),
                Join('/', [
                    'amazonaws.com',
                    Ref(docker_repository)
                ])
            ]),
            MemoryReservation='256',
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': '/ecs/scheduler',
                    'awslogs-region': Ref('AWS::Region'),
                    'awslogs-stream-prefix': 'ecs'
                }
            ),
            Command=[
                'php',
                'artisan',
                'schedule:loop'
            ],
            WorkingDirectory='/var/www/html',
            HealthCheck=ecs.HealthCheck(
                Command=[
                    'CMD-SHELL',
                    'php -v || exit 1'
                ],
                Interval=30,
                Retries=3,
                Timeout=5
            )
        )]
    )
)

print(template.to_json())
