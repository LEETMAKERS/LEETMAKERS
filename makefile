
####################################################################################
#                         Author: Abderrahmane Abdelouafi                          #
#                               File Name: Makefile                                #
#                      Creation Date: October 01, 2025 05:30 AM                    #
#                      Last Updated: October 04, 2025 16:00 PM                     #
#                            Source Language: makefile                             #
#                                                                                  #
#                             --- Code Description ---                             #
#         Automates Docker management for LEETMAKERS Platform with clear,          #
#       commands for building, starting, stopping, and cleaning containers.        #
#                                                                                  #
####################################################################################


# ================================================= #
#                      COLORS                       #
# ================================================= #

RESET			= \033[0m
WHITE			= \033[1;37m
GREY    		= \033[1;90m
BLACK			= \033[1;30m
BROWN			= \033[1;38;5;88m
ORANGE			= \033[1;38;5;208m
YELLOW			= \033[1;33m
RED				= \033[1;31m
BLUE			= \033[1;34m
CYAN			= \033[1;36m
GREEN			= \033[1;32m
MAGENTA			= \033[1;35m

# ================================================= #
#                     VARIABLES                     #
# ================================================= #

# Hide calls
export VERBOSE = TRUE
ifeq ($(VERBOSE),FALSE)
    HIDE =
else
    HIDE = @
endif

# Paths
DOCKER_COMPOSE_FILE := reqs/docker-compose.yml
# CONFIG_SCRIPT := ./configure.sh
ENV_FILE := reqs/env/.env
DB_DIR := reqs/db

# Commands
DC = ${HIDE}docker-compose -f $(DOCKER_COMPOSE_FILE)
PRINTF_ = ${HIDE}printf

# Load env variables from the .env file inside reqs directory
ifneq ("$(wildcard $(ENV_FILE))","")
    include $(ENV_FILE)
endif
export $(shell sed 's/=.*//' $(ENV_FILE))

# ================================================= #
#                       RULES                       #
# ================================================= #

# Default target, runs when 'make' is called without arguments
default: help

# Stop the containers
down:
	${PRINTF_} "$(YELLOW)Stopping containers...\n$(RESET)"
	$(DC) down

# Rebuild the containers
rebuild: clean build

# Restart the containers (down then build)
reboot: down build

# config:
# 	${HIDE}bash $(CONFIG_SCRIPT)

# Build the containers
build:
	${PRINTF_} "$(BLUE)Building containers...\n$(RESET)"
	$(DC) up -d --build

# Target to view logs of the chosen container
logs:
	${HIDE}printf "${CYAN}Choose a service to open a shell:${RESET}\n"
	${HIDE}printf "${GREEN}1) Apache${RESET}\n"
	${HIDE}printf "${YELLOW}2) MySQL${RESET}\n"
	${HIDE}read -p "Enter your choice (1-2): " choice; \
	case $$choice in \
		1) printf "${GREEN}Viewing logs for Apache...${RESET}\n"; \
		   docker-compose -f $(DOCKER_COMPOSE_FILE)logs -f apache ;; \
		2) printf "${YELLOW}Viewing logs for MySQL...${RESET}\n"; \
		   docker-compose -f $(DOCKER_COMPOSE_FILE) logs -f mysql ;; \
		*) printf "${RED}Invalid choice. Please select a valid option.${RESET}\n"; \
	esac

# Target to open a bash shell in the chosen container
shell:
	${HIDE}printf "${CYAN}Choose a service to open a shell:${RESET}\n"
	${HIDE}printf "${GREEN}1) Apache${RESET}\n"
	${HIDE}printf "${YELLOW}2) MySQL${RESET}\n"
	${HIDE}read -p "Enter your choice (1-2): " choice; \
	case $$choice in \
		1) printf "${GREEN}Opening shell for Apache...${RESET}\n"; \
		   docker-compose -f $(DOCKER_COMPOSE_FILE) exec apache bash ;; \
		2) printf "${YELLOW}Opening shell for MySQL...${RESET}\n"; \
		   docker-compose -f $(DOCKER_COMPOSE_FILE) exec mysql bash ;; \
		*) printf "${RED}Invalid choice. Please select a valid option.${RESET}\n"; \
	esac

start:
	${PRINTF_} "$(GREEN)Starting containers...\n$(RESET)"
	$(DC) up -d

stop:
	${PRINTF_} "$(YELLOW)Stopping containers...\n$(RESET)"
	$(DC) stop

restart: stop start

# Perform a full cleanup
clean: down
	${PRINTF_} "$(RED)Performing full cleanup...\n$(RESET)"
	${HIDE}docker ps -qa | xargs -r docker stop
	${HIDE}docker ps -qa | xargs -r docker rm
	${HIDE}docker images -qa | xargs -r docker rmi -f
	${HIDE}docker volume ls -q | xargs -r docker volume rm
	${HIDE}docker builder prune --all --force
	${PRINTF_} "$(RED)Cleaning server logs and SSL certificates...\n$(RESET)"
	${HIDE}rm -rf reqs/server/logs reqs/server/ssl 2>/dev/null
	${PRINTF_} "$(GREEN)Cleanup completed successfully.\n$(RESET)"

# Default rule
help:
	${PRINTF_} "\nUsage: make $(GREEN)[target]\033[0m\n"
	${PRINTF_} "\n"
	${PRINTF_} "Targets:\n"
	${PRINTF_} "  ${CYAN}build${RESET}         : Builds the docker images.\n"
	${PRINTF_} "  ${YELLOW}down${RESET}         : Stop and remove containers.\n"
	${PRINTF_} "  ${RED}clean${RESET}        : Stops containers and removes images, volumes, and networks.\n"
	${PRINTF_} "  ${BLUE}rebuild${RESET}      : Rebuilds the docker images.\n"
	${PRINTF_} "  ${ORANGE}reboot${RESET}       : Stops containers and rebuilds them (down + build).\n"
	${PRINTF_} "  ${GREEN}logs${RESET}          : Display logs of containers.\n"
	${HIDE}printf "  ${MAGENTA}shell${RESET}     : Open a shell for a selected container (options: Apache, MySQL)\n"
	${PRINTF_} "  ${GREY}help${RESET}          : Display this help message.\n"
	${PRINTF_} "${WHITE}_________________________________________________________\n${RESET}"

.PHONY: default down rebuild reboot build logs shell clean help
