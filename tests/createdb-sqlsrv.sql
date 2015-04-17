USE master;
GO

IF EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'slocloud_unit_tests')
  DROP DATABASE [slocloud_unit_tests]

IF NOT EXISTS (SELECT name FROM master.dbo.sysdatabases WHERE name = N'slocloud_unit_tests')
  CREATE DATABASE [slocloud_unit_tests]

GO