# This stack creates the infrastructure.

from troposphere import Parameter, Ref, Template, GetAtt, Base64, Join, Sub, Output
import troposphere.ec2 as ec2
import troposphere.elasticache as elasticache
import troposphere.rds as rds
import troposphere.sqs as sqs
import troposphere.s3 as s3
import troposphere.elasticloadbalancingv2 as elb
import troposphere.ecs as ecs
import troposphere.autoscaling as autoscaling
import troposphere.iam as iam
import troposphere.logs as logs
import troposphere.ecr as ecr
import uuid

suffix = str(uuid.uuid4())

template = Template('Create the infrastructure needed to run the Book A Test web app')
template.add_version('2010-09-09')

# ==================================================
# Parameters.
# ==================================================

certificate_arn = template.add_parameter(Parameter(
    'CertificateArn',
    Type='String',
    Description='The ARN for the SSL certificate for the load balancer.'
))

vpc = template.add_parameter(Parameter(
    'VPC',
    Type='AWS::EC2::VPC::Id',
    Description='The Virtual Private Cloud (VPC) to launch the stack in.'
))

subnets = template.add_parameter(Parameter(
    'Subnets',
    Type='List<AWS::EC2::Subnet::Id>',
    Description='The list of subnet IDs, for at least two Availability Zones in the region in your Virtual Private '
                'Cloud (VPC).'
))

api_user_name = template.add_parameter(Parameter(
    'ApiUserName',
    Type='String',
    Description='The name of the API user.',
    Default='api',
    MinLength='1',
    MaxLength='64',
    AllowedPattern='[a-zA-Z][a-zA-Z0-9_+=,.@-]*',
    ConstraintDescription='Must only use alphanumeric characters (including these special characters: _+=,.@-)'
))

ci_user_name = template.add_parameter(Parameter(
    'CiUserName',
    Type='String',
    Description='The name of the CI user.',
    Default='ci',
    MinLength='1',
    MaxLength='64',
    AllowedPattern='[a-zA-Z][a-zA-Z0-9_+=,.@-]*',
    ConstraintDescription='Must only use alphanumeric characters (including these special characters: _+=,.@-)'
))

database_name = template.add_parameter(
    Parameter(
        'DatabaseName',
        Description='The database name.',
        Default='book_a_test',
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9_]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'underscores).'
    )
)

database_username = template.add_parameter(
    Parameter(
        'DatabaseUser',
        Description='The database admin username.',
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
        Description='The database admin password.',
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
        Description='The database instance class.',
        Type='String',
        Default='db.t2.micro',
        AllowedValues=[
            'db.t2.micro',
            'db.t2.small',
            'db.t2.medium',
            'db.t2.large',
            'db.t2.xlarge',
            'db.t2.2xlarge'
        ],
        ConstraintDescription='Must select a valid database instance type.'
    )
)

database_allocated_storage = template.add_parameter(
    Parameter(
        'DatabaseAllocatedStorage',
        Description='The size of the database (GiB).',
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
        Description='The Redis node class.',
        Type='String',
        Default='cache.t2.micro',
        AllowedValues=[
            'cache.t2.micro',
            'cache.t2.small',
            'cache.t2.medium'
        ],
        ConstraintDescription='Must select a valid Redis node type.'
    )
)

redis_nodes_count = template.add_parameter(
    Parameter(
        'RedisNodesCount',
        Description='The number of Redis nodes to have in the cluster.',
        Default='1',
        Type='Number',
        MinValue='1',
        ConstraintDescription='Must be 1 or more.'
    )
)

sqs_default_queue_name = template.add_parameter(
    Parameter(
        'SqsDefaultQueueName',
        Description='The default queue name.',
        Default='default-' + suffix,
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens).'
    )
)

sqs_notifications_queue_name = template.add_parameter(
    Parameter(
        'SqsNotificationsQueueName',
        Description='The notifications queue name.',
        Default='notifications-' + suffix,
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens).'
    )
)

s3_uploads_bucket_name = template.add_parameter(
    Parameter(
        'S3UploadsS3BucketName',
        Description='The uploads bucket name.',
        Default='uploads-' + suffix,
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens).'
    )
)

s3_frontend_bucket_name = template.add_parameter(
    Parameter(
        'S3FrontendS3BucketName',
        Description='The frontend bucket name.',
        Default='frontend-' + suffix,
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
        Description='The backend bucket name.',
        Default='backend-' + suffix,
        Type='String',
        MinLength='1',
        MaxLength='64',
        AllowedPattern='[a-zA-Z][a-zA-Z0-9\-]*',
        ConstraintDescription='Must begin with a letter and contain only alphanumeric characters (including '
                              'hyphens).'
    )
)

api_instance_class = template.add_parameter(
    Parameter(
        'ApiInstanceClass',
        Description='The API EC2 instance class.',
        Type='String',
        Default='t2.micro',
        AllowedValues=[
            't2.nano',
            't2.micro',
            't2.small',
            't2.medium',
            't2.large',
            't2.xlarge',
            't2.2xlarge'
        ],
        ConstraintDescription='Must select a valid API instance type.'
    )
)

api_instance_count = template.add_parameter(
    Parameter(
        'ApiInstanceCount',
        Description='The number of API EC2 instances to load balance between.',
        Type='Number',
        Default='2',
        MinValue='1',
        ConstraintDescription='Must be 1 or more.'
    )
)

api_task_count = template.add_parameter(
    Parameter(
        'ApiTaskCount',
        Description='The number of API containers to run.',
        Type='Number',
        Default='2',
        MinValue='1',
        ConstraintDescription='Must be 1 or more.'
    )
)

docker_repository_name = template.add_parameter(Parameter(
    'DockerRepositoryName',
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

# Create ECS cluster.
ecs_cluster_role = template.add_resource(
    iam.Role(
        'ECSClusterRole',
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

# Create the Docker repository.
docker_repository = template.add_resource(
    ecr.Repository(
        'DockerRepository',
        RepositoryName=Ref(docker_repository_name)
    )
)

# Create the ECS task definitions.
api_log_group = template.add_resource(
    logs.LogGroup(
        'ApiLogGroup',
        LogGroupName='/ecs/api',
        RetentionInDays=7
    )
)

queue_worker_log_group = template.add_resource(
    logs.LogGroup(
        'QueueWorkerLogGroup',
        LogGroupName='/ecs/queue-worker',
        RetentionInDays=7
    )
)

scheduler_log_group = template.add_resource(
    logs.LogGroup(
        'SchedulerLogGroup',
        LogGroupName='/ecs/scheduler',
        RetentionInDays=7
    )
)

api_task_definition = template.add_resource(
    ecs.TaskDefinition(
        'ApiTaskDefinition',
        Family='api',
        NetworkMode='bridge',
        RequiresCompatibilities=['EC2'],
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
            MemoryReservation='512',
            PortMappings=[ecs.PortMapping(
                HostPort='0',
                ContainerPort='80',
                Protocol='tcp'
            )],
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': Ref(api_log_group),
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
            MemoryReservation='512',
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': Ref(queue_worker_log_group),
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
            MemoryReservation='512',
            Essential=True,
            LogConfiguration=ecs.LogConfiguration(
                LogDriver='awslogs',
                Options={
                    'awslogs-group': Ref(scheduler_log_group),
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

# Create the load balancer.
load_balancer = template.add_resource(
    elb.LoadBalancer(
        'LoadBalancer',
        Scheme='internet-facing',
        SecurityGroups=[GetAtt(load_balancer_security_group, 'GroupId')],
        Subnets=Ref(subnets),
    )
)

api_target_group = template.add_resource(
    elb.TargetGroup(
        'ApiTargetGroup',
        HealthCheckIntervalSeconds=30,
        HealthCheckPath='/',
        HealthCheckPort='traffic-port',
        HealthCheckProtocol='HTTP',
        HealthCheckTimeoutSeconds=5,
        HealthyThresholdCount=5,
        UnhealthyThresholdCount=2,
        Port=80,
        Protocol='HTTP',
        TargetType='instance',
        VpcId=Ref(vpc),
        DependsOn=[load_balancer]
    )
)

load_balancer_listener = template.add_resource(
    elb.Listener(
        'LoadBalancerListener',
        LoadBalancerArn=Ref(load_balancer),
        Port=443,
        Protocol='HTTPS',
        DefaultActions=[elb.Action(
            Type='forward',
            TargetGroupArn=Ref(api_target_group)
        )],
        Certificates=[
            elb.Certificate(
                CertificateArn=Ref(certificate_arn)
            )
        ]
    )
)

# Create the ECS services.
ecs_service_role = template.add_resource(
    iam.Role(
        'ECSServiceRole',
        AssumeRolePolicyDocument={
            'Version': '2012-10-17',
            'Statement': [
                {
                    'Action': 'sts:AssumeRole',
                    'Effect': 'Allow',
                    'Principal': {
                        'Service': 'ecs.amazonaws.com'
                    }
                }
            ]
        },
        Policies=[
            iam.Policy(
                PolicyName='ECSServiceRolePolicy',
                PolicyDocument={
                    'Statement': [
                        {
                            'Effect': 'Allow',
                            'Action': [
                                'ec2:AttachNetworkInterface',
                                'ec2:CreateNetworkInterface',
                                'ec2:CreateNetworkInterfacePermission',
                                'ec2:DeleteNetworkInterface',
                                'ec2:DeleteNetworkInterfacePermission',
                                'ec2:Describe*',
                                'ec2:DetachNetworkInterface',
                                'elasticloadbalancing:DeregisterInstancesFromLoadBalancer',
                                'elasticloadbalancing:DeregisterTargets',
                                'elasticloadbalancing:Describe*',
                                'elasticloadbalancing:RegisterInstancesWithLoadBalancer',
                                'elasticloadbalancing:RegisterTargets',
                                'route53:ChangeResourceRecordSets',
                                'route53:CreateHealthCheck',
                                'route53:DeleteHealthCheck',
                                'route53:Get*',
                                'route53:List*',
                                'route53:UpdateHealthCheck',
                                'servicediscovery:DeregisterInstance',
                                'servicediscovery:Get*',
                                'servicediscovery:List*',
                                'servicediscovery:RegisterInstance',
                                'servicediscovery:UpdateInstanceCustomHealthStatus'
                            ],
                            'Resource': '*'
                        },
                        {
                            'Effect': 'Allow',
                            'Action': [
                                'ec2:CreateTags'
                            ],
                            'Resource': 'arn:aws:ec2:*:*:network-interface/*'
                        }
                    ]
                }
            )
        ]
    )
)

api_service = template.add_resource(
    ecs.Service(
        'ApiService',
        ServiceName='api',
        Cluster=Ref(ecs_cluster),
        TaskDefinition=Ref(api_task_definition),
        DeploymentConfiguration=ecs.DeploymentConfiguration(
            MinimumHealthyPercent=100,
            MaximumPercent=200
        ),
        DesiredCount=Ref(api_task_count),
        LaunchType='EC2',
        LoadBalancers=[ecs.LoadBalancer(
            ContainerName='api',
            ContainerPort=80,
            TargetGroupArn=Ref(api_target_group)
        )],
        Role=Ref(ecs_service_role),
        DependsOn=[load_balancer_listener]
    )
)

queue_worker_service = template.add_resource(
    ecs.Service(
        'QueueWorkerService',
        ServiceName='queue-worker',
        Cluster=Ref(ecs_cluster),
        TaskDefinition=Ref(queue_worker_task_definition),
        DeploymentConfiguration=ecs.DeploymentConfiguration(
            MinimumHealthyPercent=0,
            MaximumPercent=100
        ),
        DesiredCount=1,
        LaunchType='EC2'
    )
)

scheduler_service = template.add_resource(
    ecs.Service(
        'SchedulerService',
        ServiceName='scheduler',
        Cluster=Ref(ecs_cluster),
        TaskDefinition=Ref(scheduler_task_definition),
        DeploymentConfiguration=ecs.DeploymentConfiguration(
            MinimumHealthyPercent=0,
            MaximumPercent=100
        ),
        DesiredCount=1,
        LaunchType='EC2'
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

# Create the users.
api_user = template.add_resource(
    iam.User(
        'ApiUser',
        UserName=Ref(api_user_name),
        Policies=[
            iam.Policy(
                PolicyName='ApiUserPolicy',
                PolicyDocument={
                    'Version': '2012-10-17',
                    'Statement': [
                        {
                            'Action': 's3:*',
                            'Effect': 'Allow',
                            'Resource': GetAtt(uploads_bucket, 'Arn')
                        },
                        {
                            'Action': 'sqs:*',
                            'Effect': 'Allow',
                            'Resource': GetAtt(default_queue, 'Arn')
                        },
                        {
                            'Action': 'sqs:*',
                            'Effect': 'Allow',
                            'Resource': GetAtt(notifications_queue, 'Arn')
                        }
                    ]
                }
            )
        ]
    )
)

ci_user = template.add_resource(
    iam.User(
        'CiUser',
        UserName=Ref(ci_user_name),
        Policies=[
            iam.Policy(
                PolicyName='CiUserPolicy',
                PolicyDocument={
                    'Version': '2012-10-17',
                    'Statement': [
                        {
                            'Action': 'ecr:*',
                            'Effect': 'Allow',
                            'Resource': GetAtt(docker_repository, 'Arn')
                        },
                        {
                            'Action': 'ecs:UpdateService',
                            'Effect': 'Allow',
                            'Resource': '*'
                        },
                        {
                            'Action': 's3:*',
                            'Effect': 'Allow',
                            'Resource': GetAtt(uploads_bucket, 'Arn')
                        },
                        {
                            'Action': 'secretsmanager:GetSecretValue',
                            'Effect': 'Allow',
                            'Resource': '*'
                        }
                    ]
                }
            )
        ]
    )
)

# ==================================================
# Outputs.
# ==================================================

template.add_output(
    Output(
        'LoadBalancerDNS',
        Description='The DNS name of the load balancer',
        Value=GetAtt(load_balancer, 'DNSName')
    )
)

template.add_output(
    Output(
        'DockerRepository',
        Description='The URI of the Docker repository',
        Value=Join('.', [
            Ref('AWS::AccountId'),
            'dkr.ecr',
            Ref('AWS::Region'),
            Join('/', [
                'amazonaws.com',
                Ref(docker_repository)
            ])
        ])
    )
)

template.add_output(
    Output(
        'DatabaseHost',
        Description='The URI of the RDS instance',
        Value=Join(':', [GetAtt(database, 'Endpoint.Address'), GetAtt(database, 'Endpoint.Port')])
    )
)

template.add_output(
    Output(
        'RedisHost',
        Description='The URI of the Redis instance',
        Value=Join(':', [GetAtt(redis, 'RedisEndpoint.Address'), GetAtt(redis, 'RedisEndpoint.Port')])
    )
)

template.add_output(
    Output(
        'DefaultQueue',
        Description='The URI of the default queue',
        Value=Ref(default_queue)
    )
)

template.add_output(
    Output(
        'NotificationsQueue',
        Description='The URI of the notifications queue',
        Value=Ref(notifications_queue)
    )
)

print(template.to_json())
