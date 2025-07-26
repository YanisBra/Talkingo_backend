pipeline {
    agent {
        label "${AGENT}"
    }

    stages {
        
        stage('Continuous Integration') {
            steps {
                git branch: 'main', url: 'https://github.com/YanisBra/MyBank_backend.git'
                sh 'composer install --no-scripts'
                sh 'php bin/console assets:install public'
                sh 'php bin/console importmap:install'

                // Run tests in a Docker container
                sh 'docker compose -f compose.test.yaml up -d'
                sh 'sleep 10'
                sh 'docker exec talkingo_backend_container php bin/console doctrine:migrations:migrate --env=test --no-interaction'
                sh 'docker exec talkingo_backend_container php bin/console doctrine:fixtures:load --env=test --no-interaction'
                sh 'docker exec talkingo_backend_container php bin/console lexik:jwt:generate-keypair --env=test'
                sh 'docker exec talkingo_backend_container php bin/phpunit'
                sh 'docker compose -f compose.test.yaml down --volumes'
            }
        }

        stage('Continuous Delivery') {
            steps {
                sh "docker build . -t ${DOCKERHUB_USERNAME}/talkingo_backend"
                sh "docker login -u ${DOCKERHUB_USERNAME} -p ${DOCKERHUB_PASSWORD}" 
                sh "docker push ${DOCKERHUB_USERNAME}/talkingo_backend"
            }
        }

        stage('Continuous Deployment') {
            steps{
                sh '''
                    sshpass -p ${SERVER_PSW} ssh -o StrictHostKeyChecking=no ${SERVER_USER}@${SERVER_IP} \
                    "curl -O https://raw.githubusercontent.com/YanisBra/MyBank_backend/refs/heads/main/compose.prod.yaml &&\
                    docker compose -f compose.prod.yaml down || true &&\
                    docker compose -f compose.prod.yaml up -d &&\
                    sleep 10 &&\
                    docker exec talkingo_backend_container php bin/console doctrine:migrations:migrate --no-interaction &&\
                    docker exec talkingo_backend_container php bin/console lexik:jwt:generate-keypair &&\
                    docker exec talkingo_backend_container php bin/console cache:clear"
                '''
            }
        }
    }
}