IMAGE := feature-endpoint
APP := feature-api
NETWORK := facs-network

docker-login:
	aws ecr get-login-password --region us-gov-west-1 | docker login --username AWS --password-stdin 357249423005.dkr.ecr.us-gov-west-1.amazonaws.com

build:
	docker build -f Dockerfile -t $(IMAGE) .

build-bin:
	docker build -f bin/Dockerfile -t facs-bin .

network:
	docker network inspect $(NETWORK) >/dev/null 2>&1 || docker network create $(NETWORK)

db: network
	docker run --name $(APP)-db --network=$(NETWORK) -e MYSQL_ROOT_PASSWORD=pw -d mysql

bootstrap:
	docker run -it --rm --network facs-network -e PROC=bootstrap facs-bin

run: network build
	docker run -d --name $(APP) --network=$(NETWORK) --env-file runtime.env -p 8020:8020 $(IMAGE)

run-dev: network build
	docker run -d --name $(APP) --network=$(NETWORK) --env-file dev.env -p 8020:8020 $(IMAGE)

clean-app:
	docker rm -fv $(APP)

make clean-db:
	docker rm -fv $(APP)-db

