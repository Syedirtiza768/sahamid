-- Synthetic adversarial data only. Never load into an operational company.
INSERT INTO debtorsmaster (debtorno,name,currcode) VALUES ('C2','Fixture customer','PKR'),('C3','Foreign customer','USD');
INSERT INTO suppliers (supplierid,suppname,currcode) VALUES ('S3','Foreign supplier','USD');
INSERT INTO stockcategory (categoryid,categorydescription) VALUES ('CAT1','Fixture category');
INSERT INTO manufacturers (manufacturers_id,manufacturers_name) VALUES (1,'Fixture brand');
INSERT INTO stockmaster (stockid,description,brand,categoryid,materialcost,labourcost,overheadcost,units) VALUES
 ('X1','Known cost item',1,'CAT1',10,2,3,'each'),('X2','Missing cost item',1,'CAT1',0,0,0,'kg'),('X3','Reorder item',1,'CAT1',20,0,0,'each');
INSERT INTO invoice (invoiceindex,invoiceno,invoicesdate,debtorno,branchcode,salescaseref,fromstkloc,returned,inprogress) VALUES
 (11,1001,'2024-01-15','C1','B1','SC1','MT',0,0),(12,1002,'2024-01-16','C2','B1','SC2','SR',0,0),
 (13,1003,'2024-01-17','C1','B1','SC1','MT',0,0),
 (14,1004,'2024-01-18','C1','B1','SC1','MT',0,0),(15,1004,'2024-01-18','C1','B1','SC1','MT',0,0),
 (16,1005,'2024-01-19','C3','B1','SC3','MT',0,0),(17,1006,'2024-01-20','C1','B1','SC1','MT',1,0),
 (18,1007,'2024-01-20','C1','B1','SC1','MT',0,1),(19,1008,'2023-12-15','C1','B1','SC1','MT',0,0);
INSERT INTO invoicedetails (invoicedetailsindex,invoiceno,invoicelineno,invoiceoptionno,stkcode,unitprice,discountpercent,quantity) VALUES
 (11,1001,1,1,'X1',100,0.1,2),(12,1002,1,1,'X1',200,0,1),(13,1003,1,1,'X2',100,0,1),
 (14,1004,1,1,'X1',100,0,1),(15,1005,1,1,'X1',100,0,1),(16,1006,1,1,'X1',999,0,1),
 (17,1007,1,1,'X1',999,0,1),(18,1008,1,1,'X1',50,0,1);
INSERT INTO invoiceoptions (invoiceoptionindex,invoiceno,invoicelineno,invoiceoptionno,quantity) VALUES
 (11,1001,1,1,3),(12,1002,1,1,1),(14,1004,1,1,1),(15,1005,1,1,1),(16,1006,1,1,1),(17,1007,1,1,1),(18,1008,1,1,1);
INSERT INTO shopsale (id,orderno,debtorno,branchcode,orddate,payment,salesman,complete) VALUES
 (1,2001,'C1','B1','2024-01-10','csv','Owner A',1),(2,2002,'C2','B1','2024-01-10','crv','Owner B',1),
 (3,2003,'C1','B1','2024-01-10','crv','Owner A',1),(4,2004,'C1','B1','2024-01-10','csv','Owner A',0);
INSERT INTO debtortrans (id,transno,type,debtorno,branchcode,trandate,rate,ovamount,alloc,reversed) VALUES
 (11,2001,750,'C1','B1','2024-01-10',2,120,120,0),
 (12,2002,750,'C2','B1','2024-01-10',1,20,20,0),(13,2002,750,'C2','B1','2024-01-10',1,20,20,0);
INSERT INTO bazar_parchi (id,type,parchino,svid,created_at,inprogress,discarded,returned) VALUES
 (1,601,'M1','S1','2024-01-10',0,0,0),(2,601,'M2','S1','2024-01-10',1,0,0),
 (3,601,'M3','S1','2024-01-10',0,1,0),(4,601,'M4','S1','2024-01-10',0,0,1),
 (5,601,'M5','S1','2024-01-10',0,0,0),(6,601,'M6','S3','2024-01-10',0,0,0);
INSERT INTO bpitems (id,parchino,stockid,name,quantity,quantity_received,price) VALUES
 (1,'M1','X1','Fixture item',10,4,25),(2,'M2','X1','Fixture item',2,0,10),
 (3,'M3','X1','Excluded discarded',4,4,100),(4,'M4','X1','Excluded returned',4,4,100),
 (5,'M5','X2','Unpriced',3,1,0),(6,'M6','X1','Foreign',3,1,10);
INSERT INTO locstock (loccode,stockid,quantity,reorderlevel) VALUES ('MT','X1',5,10),('SR','X1',-2,0),('MT','X2',3,0),('MT','X3',0,4);
INSERT INTO salescase (salescaseindex,salescaseref,debtorno,branchcode,salesman,commencementdate,enquiryvalue,closed) VALUES
 (1,'SC1','C1','B1','Owner A','2024-01-01',10000,0),(2,'SC2','C2','B1','Owner B','2024-01-01',5000,0),
 (3,'SC3','C3','B1','Owner A','2024-01-01',1000,0),
 (4,'SC4','C1','B1','Owner A','2024-01-01',2000,0),(5,'SC4','C1','B1','Owner A','2024-01-01',2000,0),
 (6,'SC5','C1','B1','Owner A','2024-01-01',999999,1);
INSERT INTO workorders (wo,loccode,startdate,requiredby,closed) VALUES (1,'MT','2024-01-01','2024-02-01',0),
 (2,'MT','2024-01-01','2024-02-01',0),(2,'MT','2024-01-01','2024-02-01',0),(3,'MT','2024-01-01','2024-02-01',1);
INSERT INTO dcs (dcindex,orderno,salescaseref,debtorno,branchcode,orddate,courierslipdate,invoicedate,grbdate) VALUES
 (1,1,'SC1','C1','B1','2024-01-01','0000-00-00','0000-00-00','0000-00-00'),
 (2,2,'SC1','C1','B1','2024-01-01','0000-00-00','0000-00-00','0000-00-00'),
 (3,3,'SC1','C1','B1','2024-01-01','0000-00-00','2024-01-15','0000-00-00'),
 (4,4,'SC1','C1','B1','2024-01-01','0000-00-00','0000-00-00','0000-00-00'),
 (5,4,'SC1','C1','B1','2024-01-01','0000-00-00','0000-00-00','0000-00-00');
INSERT INTO dcgroups (dcnos) VALUES ('2, 99');
INSERT INTO pcexpenses (codeexpense,description,glaccount) VALUES ('FUEL','Fuel','6000');
INSERT INTO pctabs (tabcode,usercode,typetabcode,currency) VALUES ('T1','User A','OPS','PKR'),('T2','User B','OPS','PKR'),('T3','User C','OPS','USD');
INSERT INTO pcashdetails (counterindex,tabcode,date,codeexpense,amount,authorized,posted,receipt,receiptimage,notes) VALUES
 (1,'T1','2024-01-10','FUEL',-100,'0000-00-00',0,'','','Claim <unsafe>'),
 (2,'T1','2024-01-11','FUEL',20,'2024-01-12',1,'R1','','Credit'),
 (3,'T1','2024-01-10','ASSIGNCASH',500,'2024-01-10',1,'R2','','Funding'),
 (4,'T2','2024-01-10','FUEL',-40,'2024-01-10',1,'R3','','Private tab'),
 (5,'T3','2024-01-10','FUEL',-50,'2024-01-10',1,'R4','','Currency mismatch');
INSERT INTO expense_listing_access (user,can_access) VALUES ('delegated-director','T1'),('delegated-director','T1');
INSERT INTO purchorders (orderno,supplierno,orddate,rate,status,intostocklocation) VALUES
 (1,'S1','2024-01-01',2,'Authorised','MT'),(2,'S1','2024-01-01',1,'Pending','MT'),(3,'S1','2024-01-01',0,'Printed','MT');
INSERT INTO purchorderdetails (podetailitem,orderno,itemcode,unitprice,quantityord,quantityrecd,completed) VALUES
 (1,1,'X1',10,10,4,0),(2,2,'X1',999,10,0,0),(3,3,'X1',10,3,0,0);
INSERT INTO procurement_document (id,supplierid,stage,commencement_date,canceled_date,received_date) VALUES
 (1,'S1','Under Negotiation','2024-01-01',NULL,NULL),(2,'S1','Commencement','2024-01-01',NULL,NULL),
 (3,'S1','Cancelled','2024-01-01','2024-01-02',NULL),(4,'S3','PO Placed','2024-01-01',NULL,NULL);
INSERT INTO procurement_document_details (id,pdid,stockid,quantity,price) VALUES (1,1,'X1',3,30),(2,2,'X2',2,0),(3,3,'X1',10,999),(4,4,'X1',3,10);
