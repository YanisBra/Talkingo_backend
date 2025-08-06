pipeline {
    agent {
        label "${AGENT}"
    }

    stages {
        
        stage('Continuous Integration') {
            steps {
                git branch: 'main', url: 'https://github.com/YanisBra/Talkingo_backend.git'
                sh 'php bin/console composer install'

                // Run tests in a Docker container
                sh 'docker compose -f compose.test.yaml up -d'
                sh 'docker exec talkingo_backend_container php bin/console doctrine:migrations:migrate --env=test --no-interaction'
                sh 'docker exec talkingo_backend_container php bin/console doctrine:fixtures:load --env=test --no-interaction'
                sh 'docker exec talkingo_backend_container php bin/console lexik:jwt:generate-keypair --env=test'
                sh 'docker exec talkingo_backend_container php bin/phpunit'
                sh 'docker compose -f compose.test.yaml down --volumes'
            }
        }

        stage('Continuous Delivery') {
            steps {
                sh "docker build --platform linux/amd64 . -t yanisbra/talkingo_backend"
                sh "docker login -u ${DOCKERHUB_USERNAME} -p ${DOCKERHUB_PASSWORD}" 
                sh "docker push ${DOCKERHUB_USERNAME}/talkingo_backend"
            }
        }

        stage('Continuous Deployment') {
            steps{
                sh '''
                    sshpass -p ${SERVER_PSW} ssh -o StrictHostKeyChecking=no ${SERVER_USER}@${SERVER_IP} \
                    docker compose -f compose.prod.yaml down || true &&\
                    docker compose -f compose.prod.yaml up -d &&\
                    docker exec talkingo_backend_container php bin/console doctrine:migrations:migrate --no-interaction &&\
                '''
            }
        }
    }
}