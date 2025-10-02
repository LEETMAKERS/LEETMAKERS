####################################################################################
#                         Author: Abderrahmane Abdelouafi                          #
#                              File Name: init-db.sh                               #
#                      Creation Date: October 02, 2025 01:00 PM                    #
#                      Last Updated: October 02, 2025 01:04 PM                     #
#                           Source Language: shellscript                           #
#                                                                                  #
#                             --- Code Description ---                             #
#    Initialize MySQL database by executing the default entrypoint script for      #
#   running SQL files, ensuring proper database setup during container startup.    #
####################################################################################

#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Log the execution
echo "Starting MySQL initialization..."

# Run the default entrypoint script to execute SQL files
exec docker-entrypoint.sh "$@"
