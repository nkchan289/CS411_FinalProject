CREATE TABLE fips_to_counties (
  State VARCHAR(50),
  County_Name VARCHAR(50), 
  FIPS_Code INT PRIMARY KEY
);

CREATE TABLE labor_force_data (
  FIPS_Code INT,
  State VARCHAR(5),
  Area_Name VARCHAR(255),
  Attribute VARCHAR(100),
  Value DOUBLE,
  PRIMARY KEY (FIPS_Code, Attribute),
  FOREIGN KEY (FIPS_Code) REFERENCES fips_to_counties(FIPS_Code)
);

CREATE TABLE population_estimate (
  FIPStxt INT,
  State VARCHAR(2),
  Area_Name VARCHAR(100),
  Attribute VARCHAR(100),
  Value DOUBLE,
  PRIMARY KEY (FIPStxt, Attribute),
  FOREIGN KEY (FIPStxt) REFERENCES fips_to_counties(FIPS_Code)
);

CREATE TABLE gdp_by_county (
  FIPS_Code INT PRIMARY KEY,
  countyName VARCHAR(255),
  percentChange2021 FLOAT,
  percentChange2022 FLOAT,
  percentChange2023 FLOAT,
  FOREIGN KEY (FIPS_Code) REFERENCES fips_to_counties(FIPS_Code)
);

CREATE TABLE educationtest (
  FIPSCode INT,
  State VARCHAR(2),
  Areaname VARCHAR(100),
  Attribute VARCHAR(100),
  Value DOUBLE,
  PRIMARY KEY (FIPSCode, Attribute),
  FOREIGN KEY (FIPSCode) REFERENCES fips_to_counties(FIPS_Code)
);
