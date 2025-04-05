#!/bin/bash

# Default values
USER=""
NUMPROCS=8
DRIVER="sqs"
AUTO_YES=0
REMOVE_EXISTING=0

# Parse arguments
while [ "$#" -gt 0 ]; do
    case "$1" in
    -y)
        AUTO_YES=1
        shift
        ;;
    -r)
        REMOVE_EXISTING=1
        shift
        ;;
    *)
        if [ -z "$USER" ]; then
            USER="$1"
        elif [ -z "$NUMPROCS_SET" ]; then
            NUMPROCS="$1"
            NUMPROCS_SET=1
        elif [ -z "$DRIVER_SET" ]; then
            DRIVER="$1"
            DRIVER_SET=1
        else
            echo "Error: Too many arguments provided."
            echo "Usage: $0 [-y] [-r] <user> [numprocs] [driver]"
            exit 1
        fi
        shift
        ;;
    esac
done

# Check if user argument is provided
if [ -z "$USER" ]; then
    echo "Error: User argument is required."
    echo "Usage: $0 [-y] [-r] <user> [numprocs] [driver]"
    exit 1
fi

# Validate numprocs is a positive integer
if ! [[ "$NUMPROCS" =~ ^[0-9]+$ ]] || [ "$NUMPROCS" -lt 1 ]; then
    echo "Error: numprocs must be a positive integer. Got: $NUMPROCS"
    exit 1
fi

# Validate driver (optional, could add more checks if needed)
if [ -z "$DRIVER" ]; then
    echo "Error: Driver cannot be empty."
    exit 1
fi

# Get the current directory
CURRENT_DIR=$(pwd)

# Define the app directory based on current directory
APP_DIR="$CURRENT_DIR"

# Default program name
PROGRAM_NAME="multiverse-worker"

# Check if .env file exists in the current directory
ENV_FILE="$CURRENT_DIR/.env"
if [ -f "$ENV_FILE" ]; then
    # Extract APP_NAME from .env file
    APP_NAME=$(grep -E '^APP_NAME=' "$ENV_FILE" | cut -d'=' -f2- | tr -d '[:space:]' | tr -d '"')

    # If APP_NAME is found and not empty, sanitize it
    if [ -n "$APP_NAME" ]; then
        # Replace non-alphanumeric characters (except hyphen) with hyphen, remove multiple hyphens
        SAFE_APP_NAME=$(echo "$APP_NAME" | tr -dc '[:alnum:]-' | tr '[:upper:]' '[:lower:]' | sed 's/-\+/-/g' | sed 's/^-//;s/-$//')
        PROGRAM_NAME="$SAFE_APP_NAME-worker"
    fi
fi

# Define the output file name in the current directory
CONFIG_FILE="$CURRENT_DIR/$PROGRAM_NAME.conf"

# Write the Supervisor configuration to the file
cat <<EOF >"$CONFIG_FILE"
[program:$PROGRAM_NAME]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work $DRIVER --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$USER
numprocs=$NUMPROCS
redirect_stderr=true
stdout_logfile=$APP_DIR/worker.log
stopwaitsecs=3600
EOF

# Confirm the file was created
if [ $? -eq 0 ]; then
    echo "Supervisor config file created successfully at: $CONFIG_FILE"
else
    echo "Error: Failed to create the config file."
    exit 1
fi

# Check if the file exists in /etc/supervisor/conf.d/ and handle copying/removal
SUPERVISOR_DIR="/etc/supervisor/conf.d"
SUPERVISOR_FILE="$SUPERVISOR_DIR/$PROGRAM_NAME.conf"

if [ -d "$SUPERVISOR_DIR" ]; then
    if [ -f "$SUPERVISOR_FILE" ] && [ "$REMOVE_EXISTING" -eq 1 ]; then
        if sudo rm "$SUPERVISOR_FILE"; then
            echo "Existing config removed from $SUPERVISOR_FILE"
        else
            echo "Error: Failed to remove existing config at $SUPERVISOR_FILE. Check permissions."
            exit 1
        fi
    fi

    if [ ! -f "$SUPERVISOR_FILE" ]; then
        if [ "$AUTO_YES" -eq 1 ]; then
            COPY_CONFIRM="y"
        else
            echo "Do you want to copy $CONFIG_FILE to $SUPERVISOR_FILE? (y/n)"
            read -r COPY_CONFIRM
        fi

        if [ "$COPY_CONFIRM" = "y" ] || [ "$COPY_CONFIRM" = "Y" ]; then
            # Copy the file (requires sudo if not root)
            if sudo cp "$CONFIG_FILE" "$SUPERVISOR_FILE"; then
                echo "File copied successfully to $SUPERVISOR_FILE"
                echo "Run 'sudo supervisorctl reread && sudo supervisorctl update' to apply changes."
                echo "You may/should also run 'sudo supervisorctl start \"$PROGRAM_NAME:*\"' to start the worker."
            else
                echo "Error: Failed to copy the file to $SUPERVISOR_FILE. Check permissions."
                exit 1
            fi
        else
            echo "File not copied. You can manually move it to $SUPERVISOR_DIR if needed."
        fi
    else
        if [ "$REMOVE_EXISTING" -eq 0 ]; then
            echo "File $SUPERVISOR_FILE already exists and -r flag not provided. Skipping copy."
        fi
    fi
else
    echo "Supervisor directory $SUPERVISOR_DIR does not exist. File not copied."
fi
