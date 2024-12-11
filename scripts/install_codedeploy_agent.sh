#!/bin/bash

# Update the system before installing dependencies
yum update -y

# Install required dependencies for CodeDeploy Agent
yum install -y ruby wget

# Install the CodeDeploy agent from Amazon Linux repositories
# You can use the following command to install the agent if it's not already installed
if ! command -v codedeploy-agent &>/dev/null; then
  echo "Installing CodeDeploy agent..."
  yum install -y codedeploy-agent
else
  echo "CodeDeploy agent is already installed."
fi

# Ensure the agent is running
systemctl enable codedeploy-agent
systemctl start codedeploy-agent

# Check the status of the agent to ensure it's running
if systemctl is-active --quiet codedeploy-agent; then
  echo "CodeDeploy agent is running."
else
  echo "CodeDeploy agent failed to start."
  exit 1
fi
