"""Run real dashboard JS in a local synthetic browser fixture, without ERP login.

Uses an explicitly supplied Chromium executable; no browser package required.
python bi/tests/directors_ui.py --chrome <path-to-chrome> [--no-sandbox]
"""
from pathlib import Path
import argparse
import html
import http.server
import re
import subprocess
import tempfile
import threading

ROOT = Path(__file__).resolve().parents[2]
parser = argparse.ArgumentParser()
parser.add_argument('--chrome', required=True)
parser.add_argument('--no-sandbox', action='store_true', help='For restricted local test environments only')
args = parser.parse_args()
page = (ROOT / 'v2/bi/directors.php').read_text(encoding='utf-8')
body = re.search(r'(<div class="content-wrapper directors-page"[\s\S]+?)\s*<script>', page).group(1)
body = re.sub(r'<\?php[\s\S]*?\?>', '/sahamid/', body)
css = (ROOT / 'v2/bi/directors.css').read_text(encoding='utf-8')
mock = (ROOT / 'bi/tests/fixtures/directors_ui_mock.js').read_text(encoding='utf-8')
source = (ROOT / 'v2/bi/directors.js').read_text(encoding='utf-8')
checks = (ROOT / 'bi/tests/fixtures/directors_ui_checks.js').read_text(encoding='utf-8')
payload = ('<!doctype html><html><head><meta charset="utf-8"><title>Local synthetic dashboard test</title>'
           '<style>' + css + '</style></head><body>' + body + '<script>' + mock + '</script><script>'
           + source + '</script><script>' + checks + '</script></body></html>').encode('utf-8')

class Handler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header('Content-Type', 'text/html; charset=utf-8')
        self.send_header('Content-Length', str(len(payload)))
        self.end_headers()
        self.wfile.write(payload)
    def log_message(self, *values):
        pass

server = http.server.ThreadingHTTPServer(('127.0.0.1', 0), Handler)
threading.Thread(target=server.serve_forever, daemon=True).start()
try:
    for width in (1440, 390):
        with tempfile.TemporaryDirectory(prefix='directors-browser-') as profile:
            command = [args.chrome, '--headless', '--disable-gpu', '--disable-background-networking', '--no-first-run',
                       '--user-data-dir=' + profile, '--window-size=' + str(width) + ',1200', '--dump-dom',
                       '--virtual-time-budget=3000']
            if args.no_sandbox:
                command.append('--no-sandbox')
            command.append('http://127.0.0.1:' + str(server.server_port) + '/sahamid/v2/bi/directors.php')
            result = subprocess.run(command, capture_output=True, text=True, encoding='utf-8', errors='replace', timeout=45)
            match = re.search(r'<pre id="test-results">([\s\S]*?)</pre>', result.stdout)
            if not match:
                raise RuntimeError('Browser test did not finish: ' + result.stderr[-1000:])
            output = html.unescape(match.group(1))
            print(str(width) + 'px: ' + output)
            if result.returncode or 'FAIL ' in output or 'ERROR ' in output:
                raise RuntimeError('Browser checks failed')
finally:
    server.shutdown()
