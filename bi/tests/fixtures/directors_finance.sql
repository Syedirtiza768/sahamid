-- Synthetic data only; loaded exclusively into a guarded disposable fixture DB.
INSERT INTO accountsection (sectionid,sectionname) VALUES (1,'Income'),(2,'Cost of sales'),(3,'Expenses');
INSERT INTO accountgroups (groupname,sectioninaccounts,pandl,parentgroupname) VALUES ('Sales',1,1,''),('COGS',2,1,''),('Admin',3,1,'');
INSERT INTO chartmaster (accountcode,accountname,group_) VALUES ('4000','Sales revenue','Sales'),('5000','Cost of sales','COGS'),('6000','Office costs','Admin');
INSERT INTO periods (periodno,lastdate_in_period) VALUES (1,'2023-12-31'),(2,'2024-01-31'),(3,'2024-02-29');
INSERT INTO chartdetails (accountcode,period,budget,actual) VALUES ('4000',2,-1200,-1000),('5000',2,450,400),('6000',2,200,150),('4000',3,-500,-400);
INSERT INTO gltrans (counterindex,type,typeno,trandate,periodno,account,narrative,amount,posted,jobref) VALUES
    (1,10,1,'2024-01-15',2,'4000','posted revenue',-1000,1,'PROJECT-A'),
    (2,10,1,'2024-01-15',2,'5000','posted COGS',400,1,'PROJECT-A'),
    (3,1,2,'2024-01-20',2,'6000','office cost',200,1,''),
    (4,1,3,'2024-01-21',2,'6000','cost credit',-50,1,''),
    (5,10,4,'2024-01-25',2,'4000','unposted not in reported actual',-5000,0,''),
    (6,10,5,'2024-02-15',3,'4000','comparison revenue',-400,1,'PROJECT-A');
INSERT INTO debtorsmaster (debtorno,name,currcode) VALUES ('C1','Fixture customer','PKR');
INSERT INTO invoice (invoiceindex,invoiceno,debtorno,branchcode,due) VALUES
    (1,101,'C1','B1','2020-01-31'),
    (2,102,'C1','B1','2020-01-31'),(3,102,'C1','B1','2020-02-28');
INSERT INTO debtortrans (id,transno,type,debtorno,branchcode,trandate,rate,ovamount,ovgst,ovfreight,ovdiscount,alloc,reversed) VALUES
    (1,101,10,'C1','B1','2020-01-01',2,100,10,5,-5,20,0),
    (2,102,10,'C1','B1','2020-01-02',1,30,0,0,0,0,0),
    (3,103,12,'C1','B1','2020-01-03',1,-10,0,0,0,0,0),
    (4,104,10,'C1','B1','2020-01-04',0,99,0,0,0,0,0),
    (5,105,10,'C1','B1','2020-01-05',1,5000,0,0,0,0,1);
INSERT INTO paymentterms (termsindicator,terms,daysbeforedue,dayinfollowingmonth) VALUES ('15','15th following month',0,15),('31','Month-end following month',0,31);
INSERT INTO suppliers (supplierid,suppname,currcode,paymentterms) VALUES ('S1','Fixture supplier','PKR','15'),('S2','Month-end supplier','PKR','31');
INSERT INTO supptrans (id,transno,type,supplierno,trandate,duedate,rate,ovamount,ovgst,alloc,reversed) VALUES
    (1,201,20,'S1','2024-01-31','0000-00-00',2,100,10,10,0),
    (2,202,22,'S1','2024-01-20','0000-00-00',1,-20,0,0,0),
    (3,203,20,'S2','2024-01-31','0000-00-00',1,10,0,0,0),
    (4,204,20,'S1','2024-01-31','2024-03-10',0,99,0,0,0),
    (5,205,20,'S1','2024-01-31','2024-03-10',1,5000,0,0,1);
INSERT INTO bankaccounts (accountcode,currcode,bankaccountname) VALUES ('1000','PKR','Authorized fixture bank'),('1001','PKR','Private fixture bank');
INSERT INTO bankaccountusers (accountcode,userid) VALUES ('1000','fixture-director'),('1000','fixture-director');
INSERT INTO banktrans (banktransid,type,transno,bankact,transdate,amount,exrate,functionalexrate) VALUES
    (1,12,301,'1000','2024-01-15',100,2,5),
    (2,22,302,'1000','2024-01-20',-30,1,1),
    (3,12,303,'1000','2024-01-21',100,0,1),
    (4,12,304,'1001','2024-01-22',999999,1,1);
