-- Minimal, synthetic InnoDB schema for the columns exercised by CSV repairs.
CREATE TABLE shopsale (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, orderno INT NOT NULL,
  orddate DATE, payment VARCHAR(3), salesman VARCHAR(50), complete TINYINT DEFAULT 0,
  discount DOUBLE DEFAULT 0, discountPKR DOUBLE DEFAULT 0,
  crname VARCHAR(50) DEFAULT '', accounts TINYINT DEFAULT 1
) ENGINE=InnoDB;
CREATE TABLE shopsalelines (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, orderno INT NOT NULL,
  description TEXT, notes TEXT, quantity DOUBLE, price DOUBLE, uom VARCHAR(10)
) ENGINE=InnoDB;
CREATE TABLE shopsalesitems (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, orderno INT NOT NULL, lineno INT NOT NULL,
  stockid VARCHAR(40), quantity DOUBLE DEFAULT 0, rate DOUBLE, discountpercent DOUBLE DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE stockissuance (
  salesperson VARCHAR(50), stockid VARCHAR(40), issued DOUBLE, returned DOUBLE DEFAULT 0,
  dc DOUBLE, stockissuanceindex INT NOT NULL AUTO_INCREMENT PRIMARY KEY, adjusted INT DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE stockmoves (
  stkmoveno INT NOT NULL AUTO_INCREMENT PRIMARY KEY, stockid VARCHAR(40), type INT,
  transno INT, loccode VARCHAR(10), trandate DATE, reference VARCHAR(100),
  qty DOUBLE, prd INT, newqoh DOUBLE, hidemovt TINYINT DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE ogpcsvref (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, stockid VARCHAR(40), dispatchid VARCHAR(30),
  csv VARCHAR(30), requestedby VARCHAR(50), salesman VARCHAR(50), quantity INT DEFAULT NULL
) ENGINE=InnoDB;
CREATE TABLE debtortrans (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, transno INT, type INT,
  trandate DATETIME, ovamount DOUBLE, alloc DOUBLE, settled TINYINT, reversed TINYINT DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE posdispatchitems (
  stockid VARCHAR(40), quantity DOUBLE, dispatchid INT, dispatchitemsid INT DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE posdispatch (
  dispatchid INT NOT NULL PRIMARY KEY, loccode VARCHAR(10), despatchdate DATE, deliveredto VARCHAR(50)
) ENGINE=InnoDB;
CREATE TABLE stockmaster (
  stockid VARCHAR(40) NOT NULL PRIMARY KEY, mnfpno VARCHAR(50), description VARCHAR(150),
  units VARCHAR(10), materialcost DOUBLE DEFAULT 0, discontinued TINYINT DEFAULT 0
) ENGINE=InnoDB;
CREATE TABLE periods (periodno INT NOT NULL PRIMARY KEY,lastdate_in_period DATE) ENGINE=InnoDB;
