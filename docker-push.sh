#!/bin/bash

echo "Building Docker image for dictionary-php..."

# 构建Docker镜像
docker build -f Dockerfile.minimal -t c10h15n/dictionary-php:latest .

if [ $? -eq 0 ]; then
    echo "Build successful! Now pushing to Docker Hub..."
    
    # 推送到Docker Hub
    docker push c10h15n/dictionary-php:latest
    
    if [ $? -eq 0 ]; then
        echo "Successfully pushed to Docker Hub!"
        echo "Image: c10h15n/dictionary-php:latest"
        echo "You can now run: docker run -p 8080:80 c10h15n/dictionary-php:latest"
    else
        echo "Failed to push to Docker Hub. Please check your credentials."
    fi
else
    echo "Build failed. Please check the Dockerfile."
fi