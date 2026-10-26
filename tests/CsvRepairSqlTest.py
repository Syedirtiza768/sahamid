"""Integration tests for the repair SQL in a disposable LOCAL MariaDB schema.

Uses a disposable schema on the local Docker MariaDB at 127.0.0.1:3308.
Fixtures are tracked and synthetic; no ERP database or private backup is read.
Requires PyMySQL (pip install pymysql); the workspace's optional tmp install
is also supported. Local Docker credentials come from docker-compose.yml.
"""
import copy
import hashlib
import json
import re
import sys
import uuid
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
sys.path.insert(0,str(ROOT/'tmp/python-deps'))
import pymysql

compose=(ROOT/'docker-compose.yml').read_text()
password=re.search(r'MYSQL_ROOT_PASSWORD:\s*([^\s]+)',compose).group(1)
db=pymysql.connect(host='127.0.0.1',port=3308,user='root',password=password,
                   charset='utf8mb4',autocommit=True,cursorclass=pymysql.cursors.DictCursor)
schema='csvfix_sqltest_20261026_'+uuid.uuid4().hex[:8]
assert re.fullmatch(r'csvfix_sqltest_20261026_[0-9a-f]{8}',schema)
tables=['shopsale','shopsalelines','shopsalesitems','stockissuance','stockmoves',
        'ogpcsvref','debtortrans','posdispatchitems','posdispatch','stockmaster','periods']
baseline=json.loads((ROOT/'tests/fixtures/csv_repair_cases.json').read_text())

def run(query,args=None):
    with db.cursor() as c:
        c.execute(query,args)
        result=c.fetchall() if c.description else []
        while c.nextset():
            if c.description:
                c.fetchall()
        return result

def insert(table,row):
    cols=list(row)
    run('INSERT INTO '+table+' ('+','.join('`'+c+'`' for c in cols)+') VALUES ('+','.join(['%s']*len(cols))+')',
        tuple(row[c] for c in cols))

def statements(source):
    delimiter=';'
    buffer=[]
    for line in source.splitlines():
        if line.lstrip().startswith('--') or not line.strip():
            continue
        if line.startswith('DELIMITER '):
            delimiter=line.split()[1]
            continue
        buffer.append(line)
        if line.rstrip().endswith(delimiter):
            text='\n'.join(buffer).rstrip()
            yield text[:-len(delimiter)]
            buffer=[]
    assert not buffer

def digest(selected=None):
    return hashlib.sha256(json.dumps({t:run('SELECT * FROM '+t+' ORDER BY 1,2')
                                     for t in (selected or tables[:9])},sort_keys=True,default=str).encode()).hexdigest()

def backup_digest():
    names=[next(iter(row.values())) for row in run("SHOW TABLES LIKE 'csvfix_20261026_%'")]
    return digest(sorted(n for n in names if not n.endswith('_log')))

def expect_error(query,contains):
    previous=digest()
    try:
        run(query)
    except pymysql.MySQLError as error:
        assert contains.lower() in str(error).lower(),str(error)
    else:
        raise AssertionError('Expected a guarded failure: '+query)
    assert digest()==previous,'A failing repair left changed operational rows'

def seed():
    for t in tables[:9]:
        run('DELETE FROM '+t)
    for t in ['shopsale','shopsalelines','shopsalesitems','stockissuance','stockmoves','ogpcsvref','debtortrans','posdispatchitems','posdispatch']:
        for row in baseline[t]:
            insert(t,row)
    header=copy.deepcopy(baseline['shopsale'][1])
    header.update(id=101390,orderno=10139,orddate='2026-10-21',complete=1,crname='SQL fixture',accounts=1)
    insert('shopsale',header)
    line=dict(id=265000,orderno=10139,description='Existing attached line',notes='',quantity=1,price=15830,uom='each')
    insert('shopsalelines',line)
    insert('shopsalelines',{**line,'id':265001,'description':'AC SPD 40KA 2 Pole, Himel','price':6000})
    insert('shopsalesitems',dict(id=285000,orderno=10139,lineno=265000,stockid='3802FUSE170L4961',quantity=1,rate=15830,discountpercent=0))
    ledger=copy.deepcopy(baseline['debtortrans'][1])
    ledger.update(id=700000,transno=10139,ovamount=21830,alloc=21830,settled=1,trandate='2026-10-21 12:00:00')
    insert('debtortrans',ledger)
    movement=copy.deepcopy(baseline['stockmoves'][0])
    movement.update(stkmoveno=900000,transno=10139,stockid='3802FUSE170L4961',qty=1,trandate='2026-10-21',newqoh=0)
    insert('stockmoves',movement)
    stock=copy.deepcopy(baseline['stockissuance'][0])
    stock.update(stockissuanceindex=800001,salesperson='Jalal Nasir',stockid='9381SURGPRHDY3402275',issued=2,dc=0)
    insert('stockissuance',stock)
    insert('ogpcsvref',dict(id=200001,stockid='9381SURGPRHDY3402275',dispatchid='280000',csv='10139',requestedby='Fixture',salesman='Jalal Nasir',quantity=1))
    dispatch=copy.deepcopy(baseline['posdispatch'][1])
    dispatch.update(dispatchid=280000,despatchdate='2026-10-21',deliveredto='Jalal Nasir')
    insert('posdispatch',dispatch)
    dispatch_item=copy.deepcopy(baseline['posdispatchitems'][1])
    dispatch_item.update(dispatchid=280000,stockid='9381SURGPRHDY3402275',quantity=1)
    insert('posdispatchitems',dispatch_item)

try:
    run('CREATE DATABASE '+schema)
    run('USE '+schema)
    for statement in statements((ROOT/'tests/fixtures/csv_repair_schema.sql').read_text()):
        run(statement)
    for row in baseline['stockmaster']:
        insert('stockmaster',row)
    # Current-month correction entries need an existing accounting period.
    if not run('SELECT periodno FROM periods WHERE lastdate_in_period=LAST_DAY(CURDATE())'):
        run('INSERT INTO periods(periodno,lastdate_in_period) SELECT COALESCE(MAX(periodno),0)+1,LAST_DAY(CURDATE()) FROM periods')
    seed()
    original=digest()
    for statement in statements((ROOT/'corrections/repair_csv_10139_9992_9996_20261026.sql').read_text()):
        run(statement)
    assert digest()==original,'Default preview modified operational data'
    assert not run('SELECT * FROM csvfix_20261026_log'),'Preview wrote a repair log'
    print('PASS: SQL installs; all default previews preserve operational rows')

    expect_error('CALL csvfix_10139_20261026(1,NULL)','Confirm the SPD')
    expect_error("CALL csvfix_10139_20261026(1,'9381SURGPRHDYNEZ402H')",'not one of the reviewed')
    print('PASS: missing confirmation and wrong SPD code are rejected')

    ledger_before=digest(['debtortrans'])
    moves_before=digest(['stockmoves'])
    run('CALL csvfix_9992_20261026(1)')
    assert run('SELECT complete FROM shopsale WHERE orderno=9992')[0]['complete']==1
    row=run('SELECT quantity,rate,discountpercent FROM shopsalesitems WHERE orderno=9992')[0]
    assert row=={'quantity':1.0,'rate':7000.0,'discountpercent':0.0}
    assert digest(['debtortrans'])==ledger_before and digest(['stockmoves'])==moves_before
    repaired=digest();backed_up=backup_digest()
    run('CALL csvfix_9992_20261026(1)');assert digest()==repaired
    assert backup_digest()==backed_up,'A repeated repair changed its first-state backup'
    print('PASS: CSV 9992 restores attachment once without stock/accounting repost')

    run("CREATE TRIGGER fixture_block_stock BEFORE INSERT ON stockmoves FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture stock failure'")
    expect_error('CALL csvfix_9996_20261026(1)','fixture stock failure')
    run('DROP TRIGGER fixture_block_stock')
    print('PASS: late stock failure rolls back component and cart updates')

    run('CALL csvfix_9996_20261026(1)')
    row=run('SELECT quantity,rate,discountpercent FROM shopsalesitems WHERE orderno=9996')[0]
    assert abs(row['quantity']*24-7.31)<1e-8 and row['discountpercent']==0
    assert abs(row['quantity']*row['rate']*24-2400)<1e-6
    moved=run("SELECT COUNT(*) AS n,SUM(qty) AS q FROM stockmoves WHERE type=750 AND transno=9996")[0]
    assert moved['n']==2 and abs(moved['q']-7.31)<1e-8
    cart=run('SELECT issued,dc FROM stockissuance WHERE stockissuanceindex=69960')[0]
    assert abs(cart['issued'])<1e-8 and abs(cart['dc']-7.31)<1e-8
    assert digest(['debtortrans'])==ledger_before
    repaired=digest();backed_up=backup_digest()
    run('CALL csvfix_9996_20261026(1)');assert digest()==repaired
    assert backup_digest()==backed_up,'Repeated pipe repair changed its original snapshot'
    print('PASS: CSV 9996 preserves selling value and appends one inventory correction; repeat is harmless')

    run("UPDATE ogpcsvref SET quantity=0 WHERE csv='10139' AND stockid='9381SURGPRHDY3402275'")
    expect_error("CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275')",'not allocated')
    run("UPDATE ogpcsvref SET quantity=1 WHERE id=200001")
    run("CREATE TRIGGER fixture_block_stock BEFORE INSERT ON stockmoves FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture stock failure'")
    expect_error("CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275')",'fixture stock failure')
    run('DROP TRIGGER fixture_block_stock')
    print('PASS: unsupported SPD allocation and mid-repair failure leave no partial repair')

    run("CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275')")
    assert run('SELECT COUNT(*) AS n FROM shopsalesitems WHERE orderno=10139 AND lineno=265001')[0]['n']==1
    assert run("SELECT COUNT(*) AS n FROM stockmoves WHERE type=750 AND transno=10139 AND stockid='9381SURGPRHDY3402275'")[0]['n']==1
    assert digest(['debtortrans'])==ledger_before
    repaired=digest();backed_up=backup_digest()
    run("CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275')");assert digest()==repaired
    assert backup_digest()==backed_up,'Repeated SPD repair changed its original snapshot'
    print('PASS: CSV 10139 attaches confirmed SPD and deducts stock exactly once')

    # A missing attachment whose stock was already deducted must only restore the link.
    run('DELETE FROM shopsalesitems WHERE orderno=10139 AND lineno=265001')
    stock_before=digest(['stockmoves','stockissuance','ogpcsvref'])
    run("CALL csvfix_10139_20261026(1,'9381SURGPRHDY3402275')")
    assert digest(['stockmoves','stockissuance','ogpcsvref'])==stock_before
    print('PASS: restoring an already-posted SPD attachment makes no inventory change')

    seed()
    run('UPDATE stockissuance SET issued=issued+5,dc=dc+2 WHERE stockissuanceindex=69960')
    run('CALL csvfix_9996_20261026(1)')
    cart=run('SELECT issued,dc FROM stockissuance WHERE stockissuanceindex=69960')[0]
    assert abs(cart['issued']-5)<1e-8 and abs(cart['dc']-9.31)<1e-8
    print('PASS: CSV 9996 correction preserves later cart activity')

    seed()
    run('UPDATE shopsalesitems SET quantity=7.31E0/24,rate=2400E0/7.31E0,discountpercent=0 WHERE orderno=9996')
    run('UPDATE stockmoves SET qty=7.31,newqoh=0 WHERE stkmoveno=812083')
    run('UPDATE stockmoves SET qty=7.31 WHERE stkmoveno=811519')
    run('UPDATE stockissuance SET issued=0,dc=7.31 WHERE stockissuanceindex=69960')
    run("UPDATE ogpcsvref SET quantity=0 WHERE csv='9996'")
    correct=digest()
    run('CALL csvfix_9996_20261026(1)')
    assert digest()==correct
    print('PASS: previously applied local-style CSV 9996 repair is recognized')

    # Exercise the actual PHP guard queries, not a copied approximation.
    source=(ROOT/'shop/pos/api/finalizeShopSale.php').read_text()
    missing_query=re.search(r'\$SQL = "(SELECT shopsalelines.id.*?)";',source,re.S).group(1)
    posted_query=re.search(r'\$SQL = "(SELECT id FROM debtortrans.*?)";',source,re.S).group(1)
    run('CALL csvfix_9992_20261026(1)')
    insert('shopsalelines',dict(id=999999,orderno=9992,description='Unattached fixture',notes='',quantity=1,price=0,uom='each'))
    assert run(missing_query.replace('$orderno','9992'))==[{'id':999999}]
    assert not run(missing_query.replace('$orderno','9996'))
    assert len(run(posted_query.replace('$orderno','9992')))==1
    run('UPDATE debtortrans SET reversed=1 WHERE transno=9992 AND type=750')
    assert not run(posted_query.replace('$orderno','9992'))
    print('PASS: actual finalization queries reject missing lines and existing active postings')

    run('CREATE TABLE ogpcrvref LIKE ogpcsvref')
    for statement in statements((ROOT/'migrations/20261023_preserve_shop_dispatch_quantity.sql').read_text()):
        run(statement)
    run("INSERT INTO ogpcsvref(stockid,csv,quantity) VALUES ('fractional-fixture','fixture',7.31)")
    assert str(run("SELECT quantity FROM ogpcsvref WHERE csv='fixture'")[0]['quantity'])=='7.310000'
    print('PASS: actual allocation migration preserves fractional quantities')
    print('All CSV repair SQL tests passed in '+schema)
finally:
    # Only the unique test schema created by this process can be dropped.
    assert re.fullmatch(r'csvfix_sqltest_20261026_[0-9a-f]{8}',schema)
    run('DROP DATABASE IF EXISTS '+schema)
    db.close()
