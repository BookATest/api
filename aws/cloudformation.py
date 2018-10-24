# Converted from EC2InstanceSample.template located at:
# http://aws.amazon.com/cloudformation/aws-cloudformation-templates/

from troposphere import ec2, Template

template = Template('Create the infrastructure needed to run the Book A Test web app')
template.add_version('2010-09-09')

# Create the security groups.

load_balancer_security_group = ec2.SecurityGroup(
    "LoadBalancerSecurityGroup",
    GroupDescription='For connecting to the API load balancer',
    SecurityGroupIngress=[
        ec2.SecurityGroupRule(
            IpProtocol='tcp',
            FromPort='80',
            ToPort='80',
            CidrIp='0.0.0.0/0'
        ),
        ec2.SecurityGroupRule(
            IpProtocol='tcp',
            FromPort='443',
            ToPort='443',
            CidrIp='0.0.0.0/0'
        )
    ]
)
template.add_resource(load_balancer_security_group)

print(template.to_json())
