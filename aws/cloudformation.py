# Converted from EC2InstanceSample.template located at:
# http://aws.amazon.com/cloudformation/aws-cloudformation-templates/

from troposphere import ec2, Ref, Template

template = Template('Create the infrastructure needed to run the Book A Test web app')
template.add_version('2010-09-09')

# Create the security groups.
load_balancer_security_group = ec2.SecurityGroup(
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
template.add_resource(load_balancer_security_group)

api_security_group = ec2.SecurityGroup(
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
template.add_resource(api_security_group)

database_security_group = ec2.SecurityGroup(
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
template.add_resource(database_security_group)

redis_security_group = ec2.SecurityGroup(
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
template.add_resource(redis_security_group)

print(template.to_json())
