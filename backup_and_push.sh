#!/bin/bash

# --- CONFIGURATION ---
CONTAINER_NAME="db"
DB_USER="smartduuka"
BACKUP_DIR="$HOME/backups"

# Destination VPS Info
DEST_USER="deploy"
DEST_IP="161.97.184.49"
DEST_PORT="7589"
KEY_PATH="$HOME/.ssh/erudite"

# File Naming
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
FILENAME="full_backup_$TIMESTAMP.sql.gz"

echo "[$TIMESTAMP] Starting Backup..."

# 1. Run the exact command you verified
# We remove -t for cron/scripts to avoid "terminals not a tty" errors
#docker exec $CONTAINER_NAME pg_dumpall -U $DB_USER | gzip > "$BACKUP_DIR/$FILENAME"
docker exec $CONTAINER_NAME pg_dumpall -U $DB_USER --clean --if-exists | gzip > "$BACKUP_DIR/$FILENAME"

# 2. Check if backup was successful
if [ -s "$BACKUP_DIR/$FILENAME" ]; then
    echo "Backup successful: $FILENAME"

    echo "Pushing to Destination VPS..."
    # 3. Securely push to the other VPS
    scp -P $DEST_PORT -i $KEY_PATH "$BACKUP_DIR/$FILENAME" $DEST_USER@$DEST_IP:~/backups/

    if [ $? -eq 0 ]; then
        echo "Transfer complete."
        # Keep local storage clean: delete backups older than 3 days
        find $BACKUP_DIR -type f -name "full_backup_*.sql.gz" -mtime +3 -delete
    else
        echo "Transfer FAILED."
        exit 1
    fi
else
    echo "Backup FAILED."
    exit 1
fi