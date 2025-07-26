## Deploy locally

Install the dependencies:

```
composer install --no-scripts
php bin/console assets:install public
php bin/console importmap:install
```

Create the database and run the migrations:

```
php bin/console doctrine:database:create
php bin/console doctrine:migration:migrate
php bin/console doctrine:fixtures:load
php bin/console lexik:jwt:generate-keypair
```

Run tests

```
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --env=test
php bin/console doctrine:fixtures:load --env=test
php bin/console lexik:jwt:generate-keypair --env=test
php bin/phpunit
```

Run the project locally:

```
symfony serve
```

## Deploy with Docker

### ✅ Quick solution using Docker Compose (recommended)

Launch all containers with a single command:

```
docker compose up --build -d
```

This will:

- Create the necessary Docker network (if not already existing)
- Start both backend and database services
- Apply all environment configurations automatically

Make sure your `DATABASE_URL` in the `.env` file is set properly, e.g.:

```
mysql://root:root@database:3306/talkingo_db
```

---

### 🛠️ Manual alternative (optional)

1. Create a Docker network:

```
docker network create symfony-talkingo-network
```

2. (Optional) Start a MySQL container:

```
docker run --name talkingo-mysql_container --network symfony-talkingo-network -p 3306:3306 -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=talkingo_db -v mysql_data:/var/lib/mysql mysql
```

3. Update the database connection string in your `.env` file (line 27) to point to `talkingo-mysql_container`.

4. Build and run your Symfony backend image:

```
docker build . -t Talkingo_backend
docker run --name talkingo_backend_container --network symfony-talkingo-network -p 8082:80 Talkingo_backend
```

5. Create the database and run the migrations:

```
docker exec -it talkingo_backend_container php bin/console doctrine:database:create
docker exec -it talkingo_backend_container php bin/console doctrine:migration:migrate
```

## Deploy with Jenkins

If not already done, start a Jenkins master instance:

```
docker run --name jenkins -p <choose_a_port>:8080 jenkins/jenkins
```

Then build and start a Jenkins agent.

If you're on Windows, run the following commands from PowerShell or cmd:

```
cd Jenkins-agent
docker build -t jenkins-agent-with-docker-and-composer .
docker run --init --name jenkins_agent_composer -v /var/run/docker.sock:/var/run/docker.sock jenkins-agent-with-docker-and-composer -url http://<Jenkins_master_IP_address>:8080 <secret> <agent_name>
```
