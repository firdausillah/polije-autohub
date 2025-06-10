#!/bin/bash

# Konfigurasi
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M")
BACKUP_DIR="/var/www/html/polije-autohub/backups/"
FILENAME="db-backup-$TIMESTAMP.sql"
ZIPFILE="db-backup-$TIMESTAMP.zip"
DB_USER="root"
DB_PASSWORD='M4str!pj4y4'
DB_NAME="polije_autohub"  # Ganti sesuai nama databasenya
RCLONE_REMOTE="gdrive"
RCLONE_FOLDER="backup-polijeautohub"

# 1. Buat folder backup jika belum ada
mkdir -p "$BACKUP_DIR"

# 2. Dump database
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$BACKUP_DIR/$FILENAME"

# 3. Kompres hasil dump
cd "$BACKUP_DIR"
zip "$ZIPFILE" "$FILENAME"

# 4. Upload ke Google Drive
rclone copy "$ZIPFILE" "$RCLONE_REMOTE:$RCLONE_FOLDER"

# 5. Hapus file SQL & zip lokal yang lebih dari 7 hari
find "$BACKUP_DIR" -type f -name "*.sql" -mtime +7 -delete
find "$BACKUP_DIR" -type f -name "*.zip" -mtime +7 -delete

# 6. Hapus file di Google Drive yang lebih dari 7 hari
rclone delete "$RCLONE_REMOTE:$RCLONE_FOLDER" --min-age 7d

# 7. Hapus file SQL terbaru setelah upload (opsional)
rm "$BACKUP_DIR/$FILENAME"

echo "Backup selesai: $ZIPFILE"
