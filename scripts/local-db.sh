#!/bin/sh
# Development-only lifecycle helper for this project's isolated MariaDB data.
set -eu
project=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
cd "$project"
data="$project/.local/mysql"
socket="$data/mysql.sock"
server=$(command -v mariadbd || true)
if [ -z "$server" ]; then
    echo 'MariaDB is not installed. Configure your own MySQL server in .env instead.' >&2
    exit 1
fi
server=$(php -r 'echo realpath($argv[1]);' "$server")
bindir=$(dirname "$server")
basedir=$(dirname "$bindir")
service="local.ughi.mysql.$(php -r 'echo substr(hash("sha256", $argv[1]), 0, 12);' "$project")"
if [ ! -d "$data/mysql" ]; then
    echo 'Project-local database data is missing. Configure your own MySQL server; see README.md.' >&2
    exit 1
fi
running() {
    php -r 'require "vendor/autoload.php"; $v = Dotenv\Dotenv::parse(file_get_contents(".env")); $db = new PDO("mysql:unix_socket=".$argv[1].";dbname=".$v["DB_DATABASE"], $v["DB_USERNAME"], $v["DB_PASSWORD"], [PDO::ATTR_TIMEOUT => 2]); $db->query("SELECT 1");' "$socket" >/dev/null 2>&1
}
remove_service() {
    if launchctl list "$service" >/dev/null 2>&1; then
        launchctl remove "$service"
        # Removal is asynchronous; do not submit a replacement during teardown.
        attempt=0
        while launchctl list "$service" >/dev/null 2>&1; do
            attempt=$((attempt + 1))
            if [ "$attempt" -ge 40 ]; then
                echo 'Project database job is still shutting down. Retry shortly.' >&2
                exit 1
            fi
            sleep 0.25
        done
    fi
}
case "${1:-status}" in
    start)
        port=$(php -r 'require "vendor/autoload.php"; $v = Dotenv\Dotenv::parse(file_get_contents(".env")); if (($v["APP_ENV"] ?? "") !== "local" || ($v["DB_HOST"] ?? "") !== "127.0.0.1" || ($v["DB_PORT"] ?? "") !== "33307") { fwrite(STDERR, "This helper requires APP_ENV=local, DB_HOST=127.0.0.1 and DB_PORT=33307.\n"); exit(1); } echo $v["DB_PORT"];')
        if running; then echo "Project database is already running on port $port."; exit 0; fi
        if [ "$(uname -s)" = Darwin ]; then
            # launchd owns the process, so closing a terminal or task cannot reap it.
            # The label belongs only to this checkout; no login service is installed.
            remove_service
            launchctl submit -l "$service" -o "$data/launcher.log" -e "$data/launcher.log" -- \
                "$server" --no-defaults --basedir="$basedir" --datadir="$data" \
                --socket="$socket" --port="$port" --bind-address=127.0.0.1 \
                --pid-file="$data/mysql.pid" --log-error="$data/mysql.log" \
                --skip-log-bin --performance-schema=OFF --innodb-buffer-pool-size=32M
        else
            nohup "$server" --no-defaults --basedir="$basedir" --datadir="$data" \
                --socket="$socket" --port="$port" --bind-address=127.0.0.1 \
                --pid-file="$data/mysql.pid" --log-error="$data/mysql.log" \
                --skip-log-bin --performance-schema=OFF --innodb-buffer-pool-size=32M \
                </dev/null >/dev/null 2>&1 &
        fi
        attempt=0
        while [ "$attempt" -lt 40 ]; do
            if running; then echo "Project database is running on 127.0.0.1:$port."; exit 0; fi
            attempt=$((attempt + 1))
            sleep 0.25
        done
        echo "Database did not become ready. See $data/mysql.log." >&2
        exit 1
        ;;
    stop)
        if running; then
            pid=$(cat "$data/mysql.pid")
            case "$pid" in ''|*[!0-9]*) echo 'Invalid project database PID.' >&2; exit 1 ;; esac
            owner=$(ps -p "$pid" -o uid= | tr -d ' ')
            args=$(ps -p "$pid" -o args=)
            if [ "$owner" != "$(id -u)" ]; then echo 'Database process owner does not match.' >&2; exit 1; fi
            case "$args " in *"--datadir=$data "*) ;; *) echo 'PID does not belong to the project database.' >&2; exit 1 ;; esac
            kill -TERM "$pid"
            attempt=0
            while [ -e "$data/mysql.pid" ] && [ "$attempt" -lt 40 ]; do
                attempt=$((attempt + 1))
                sleep 0.25
            done
            if [ -e "$data/mysql.pid" ]; then echo 'Database is still shutting down.' >&2; exit 1; fi
            if [ "$(uname -s)" = Darwin ]; then remove_service; fi
            echo 'Project database stopped.'
        else echo 'Project database is not running.'; fi
        ;;
    status)
        if running; then echo 'Project database is running.'
        else echo 'Project database is not running.'; exit 1; fi
        ;;
    *) echo 'Usage: npm run db:start | db:stop | db:status' >&2; exit 1 ;;
esac
