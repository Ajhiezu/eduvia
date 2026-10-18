#!/bin/bash
set -e

echo "===================================================================="
echo "EDUVIA DATABASE BACKUP & RESTORE INTEGRITY VERIFICATION SUITE"
echo "===================================================================="

DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-eduvia_dev}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD:-}"
TEST_RESTORE_DB="eduvia_restore_verify_test"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="storage/backups"
BACKUP_FILE="${BACKUP_DIR}/eduvia_backup_${TIMESTAMP}.sql"

mkdir -p "${BACKUP_DIR}"

echo "[1/4] Creating full MySQL database dump..."
echo "Target: ${BACKUP_FILE}"
if [ -z "$DB_PASS" ]; then
    mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" "${DB_NAME}" > "${BACKUP_FILE}"
else
    mysqldump -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" > "${BACKUP_FILE}"
fi

echo "[OK] Dump created. Size: $(du -h "${BACKUP_FILE}" | cut -f1)"

echo "[2/4] Initializing temporary restore verification database: ${TEST_RESTORE_DB}..."
MYSQL_CMD="mysql -h ${DB_HOST} -P ${DB_PORT} -u ${DB_USER}"
if [ -n "$DB_PASS" ]; then
    MYSQL_CMD="${MYSQL_CMD} -p${DB_PASS}"
fi

${MYSQL_CMD} -e "DROP DATABASE IF EXISTS ${TEST_RESTORE_DB}; CREATE DATABASE ${TEST_RESTORE_DB} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "[3/4] Restoring dump into ${TEST_RESTORE_DB} to verify integrity..."
${MYSQL_CMD} "${TEST_RESTORE_DB}" < "${BACKUP_FILE}"

echo "[4/4] Verifying critical table row counts in restored database..."
${MYSQL_CMD} "${TEST_RESTORE_DB}" -e "SELECT 'schools' as tbl, count(*) as row_count FROM schools UNION ALL SELECT 'users', count(*) FROM users UNION ALL SELECT 'invoices', count(*) FROM invoices UNION ALL SELECT 'audit_logs', count(*) FROM audit_logs;"

echo "Cleaning up temporary verification database..."
${MYSQL_CMD} -e "DROP DATABASE IF EXISTS ${TEST_RESTORE_DB};"

echo "===================================================================="
echo "[SUCCESS] Backup and Restore Verification COMPLETED SUCCESSFULLY!"
echo "Backup archived at: ${BACKUP_FILE}"
echo "===================================================================="
