debtorsmaster
custbranch

`supplierid`, `suppname`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `supptype`, `lat`, `lng`, `currcode`, `suppliersince`, `paymentterms`, `lastpaid`, `lastpaiddate`, `bankact`, `bankref`, `bankpartics`, `remittance`, `taxgroupid`, `factorcompanyid`, `taxref`, `phn`, `port`, `email`, `fax`, `telephone`, `url`

`debtorno`, `name`, `address1`, `address2`, `address3`, `address4`, `address5`, `address6`, `currcode`, `salestype`, `clientsince`, `holdreason`, `paymentterms`, `discount`, `pymtdiscount`, `lastpaid`, `lastpaiddate`, `creditlimit`, `invaddrbranch`, `discountcode`, `ediinvoices`, `ediorders`, `edireference`, `editransport`, `ediaddress`, `ediserveruser`, `ediserverpwd`, `taxref`, `customerpoline`, `typeid`, `language_id`, `dba`, `dueDays`, `paymentExpected`, `disabletrans`

`branchcode`, `debtorno`, `brname`, `braddress1`, `braddress2`, `braddress3`, `braddress4`, `braddress5`, `braddress6`, `lat`, `lng`, `estdeliverydays`, `area`, `salesman`, `fwddate`, `phoneno`, `faxno`, `contactname`, `email`, `defaultlocation`, `taxgroupid`, `defaultshipvia`, `deliverblind`, `disabletrans`, `brpostaddr1`, `brpostaddr2`, `brpostaddr3`, `brpostaddr4`, `brpostaddr5`, `brpostaddr6`, `specialinstructions`, `custbranchcode`

// queries
INSERT into debtorsmaster(debtorsmaster.debtorno,debtorsmaster.name,debtorsmaster.address1,debtorsmaster.address2,debtorsmaster.address3,debtorsmaster.address4,debtorsmaster.address5,debtorsmaster.address6,debtorsmaster.currcode,debtorsmaster.clientsince) SELECT suppliers.supplierid,suppliers.suppname,suppliers.address1,suppliers.address2,suppliers.address3,suppliers.address4,suppliers.address5,suppliers.address6,suppliers.currcode,suppliers.suppliersince FROM suppliers WHERE suppliers.supptype = 2

INSERT into 
custbranch(custbranch.branchcode,custbranch.debtorno,custbranch.brname,custbranch.braddress1,custbranch.braddress2,custbranch.braddress3,custbranch.braddress4,custbranch.braddress5,custbranch.braddress6)
SELECT suppliers.supplierid, suppliers.supplierid,suppliers.suppname,suppliers.address1,suppliers.address2,suppliers.address3,suppliers.address4,suppliers.address5,suppliers.address6
FROM suppliers
WHERE suppliers.supptype = 2
UPDATE debtorsmaster SET debtorsmaster.typeid = 28 WHERE debtorno like 'MV-%'



UPDATE custbranch SET custbranch.defaultlocation='SR' WHERE custbranch.branchcode like 'MV-%'