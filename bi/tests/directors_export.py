"""Validate the established XLSX writer used by the directors endpoint."""
import argparse
from pathlib import Path
import subprocess
import tempfile
import xml.etree.ElementTree as ET
import zipfile

parser = argparse.ArgumentParser()
parser.add_argument('--php', required=True)
parser.add_argument('--ext-dir', help='Optional PHP extension directory for portable test runtimes')
args = parser.parse_args()
root = Path(__file__).resolve().parents[2]
code = """require 'bi/bootstrap.php';
(new SAHamid\\BI\\Export\\XlsxExporter())->download('directors-fixture','Synthetic export fixture',
['Period'=>'2024-01-01 through 2024-01-31'],['reference'=>'Reference','amount'=>'Amount'],
[['reference'=>'=SUM(1,2)','amount'=>123.45],['reference'=>'<source>','amount'=>null]]);"""
command = [args.php, '-d', 'display_errors=stderr', '-d', 'error_reporting=22527']
if args.ext_dir:
    command += ['-d', 'extension_dir=' + args.ext_dir, '-d', 'extension=zip', '-d', 'extension=mbstring']
command += ['-r', code]
with tempfile.TemporaryDirectory(prefix='directors-xlsx-') as directory:
    workbook = Path(directory) / 'fixture.xlsx'
    with workbook.open('wb') as output:
        result = subprocess.run(command, cwd=root, stdout=output, stderr=subprocess.PIPE, timeout=30)
    if result.returncode:
        raise RuntimeError(result.stderr.decode(errors='replace'))
    with zipfile.ZipFile(workbook) as archive:
        ns = {'x': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
        sheet = ET.fromstring(archive.read('xl/worksheets/sheet1.xml'))
        assert not sheet.findall('.//x:f', ns), 'Source text became an executable formula'
        assert b'=SUM(1,2)' in archive.read('xl/sharedStrings.xml'), 'Literal reference lost'
        assert any(v.text == '123.45' for v in sheet.findall('.//x:v', ns)), 'Numeric amount changed'
print('PASS: genuine XLSX, literal formula-like text, numeric values and empty amounts')
