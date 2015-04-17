SLO Cloud
========

A Student Learning Objectives (SLO) Reporting Tool.

**This is a peer-reviewed, open-source project for public institutions of higher education, licensed under the GPLv2.**

# About

The SLO Cloud was conceptualized by Aeron Zentner during his time at Lassen College and engineered by Jesse Lawson 
when both worked at Coastline College.

A full background and development writeup of the original project is available at [lawsonry.com/slocloud](http://lawsonry.com/slocloud).

The Research and Development department at Crafton Hills College saw SLO Cloud and liked the idea of simple SLO
reporting. They felt that it would make it easier for their faculty to submit SLOs. So they, along with San Bernardino
Valley College decided to implement it. The San Bernardino Community College District (SBCCD), went to work
implementing it and this release is the result.

SBCCD has a brief write-up and demo [here](http://sbccd.org/slocloud).

## About this Release

This release contains the changes to implement SLO Cloud for SBCCD. As the original SLO Cloud release was just a 
prototype model, this means extensive changes.

We added the following features:

* SLOs stored in SQL Server using Doctrine
* Export all SLOs to a CSV or TSV file
* Two different SLO models supported
    * Simple - very similar to the original
    * Rubric - uses a 4 level rubric to score each SLO statement
* Ability to add more SLO models should the need arise
* Per model mapping of SLOs to Program Learning Outcomes (PLO), Institution Learning Outcomes (ILO) (Core 
  Competencies(CC) for Simple), and General Education Outcomes (GEO) (Rubric only)
* Summary report for ILOs/CCs/GEOs
* Ability to securely login via an LDAP account
* Emails a copy of the SLO to the submitter when secured with LDAP
* Responsive for tablet resolutions and above

We made the following changes:

* Removed the MVPReady theme that is no longer free and went back to vanilla Bootstrap

## Requirements

* Intermediate PHP, JavaScript, HTML, and CSS
* PHP 5.6
* SQL Server 2008 or SQLEXPRESS
* No Web Server necessary for the demo, but is required for actual use

# Instructions

1. Download the zip file and unpack it
2. Follow the instructions in the `BUILD.md` file
3. Enjoy!

Instructions for setting up SLO Cloud on IIS are available in `SETUP.md`

# Demonstration

SBCCD has a brief write-up and demo [here](http://sbccd.org/slocloud).

# Contributing

If you are a programmer, developer, or other technology professional looking to contribute to this project, please 
feel free to fork this repo and submit pull requests at will. All pull requests are reviewed at least weekly. 

## Code Commenting Policy

For those of you who are actively contributing to projects, please comment your code as verbose as possible and add 
an identifier and timestamp to your code comments.

For example, in the comment head of the file you're working on, add something like this:

	Contributors:
	Jesse Lawson (lawsonry), jesse@lawsonry.com


Then, in your code, put something like this:

`// (10 Oct 2014 lawsonry) Here is a comment for the code below`

Thank you!
